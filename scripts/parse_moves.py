#!/usr/bin/env python3
"""
parse_moves.py
Parses pokevoid's moves enum + move.ts + move.json locale and upserts into core_moves.
Captures: name, type, category, power, accuracy, pp, is_smitty, is_dynamic_type.
"""
import re
import json
import subprocess
import sys

POKEVOID   = '/var/www/void.scooom.com/pokevoid/src'
MOVES_ENUM = f'{POKEVOID}/enums/moves.ts'
MOVE_TS    = f'{POKEVOID}/data/move.ts'
MOVE_JSON  = f'{POKEVOID}/locales/en/move.json'

DB_NAME = 'pokevoid'
DB_USER = 'void'
DB_PASS = '827uh6aV8VI7F50D30BF'

SMITTY_KEYS = {'SMITTY_NUGGETS', 'NUGGET_OF_SMITTY'}

# Moves whose type changes dynamically at runtime
DYNAMIC_TYPE_KEYS = {
    'REVELATION_DANCE', 'WEATHER_BALL', 'HIDDEN_POWER',
    'TERRAIN_PULSE', 'SMITTY_NUGGETS', 'NUGGET_OF_SMITTY',
    'MULTI_ATTACK', 'NATURAL_GIFT',
}

TYPE_NAMES = {
    -1: 'Unknown', 0: 'Normal', 1: 'Fighting', 2: 'Flying', 3: 'Poison',
    4: 'Ground', 5: 'Rock', 6: 'Bug', 7: 'Ghost', 8: 'Steel',
    9: 'Fire', 10: 'Water', 11: 'Grass', 12: 'Electric', 13: 'Psychic',
    14: 'Ice', 15: 'Dragon', 16: 'Dark', 17: 'Fairy',
    18: 'Stellar', 19: 'All', 20: 'SMITTY', 21: 'Glitch',
}

def run_sql(sql):
    result = subprocess.run(
        ['mariadb', '-u', DB_USER, f'-p{DB_PASS}', DB_NAME],
        input=sql, capture_output=True, text=True
    )
    if result.returncode != 0:
        print(f"SQL ERROR: {result.stderr.strip()}", file=sys.stderr)
    return result

def escape(s):
    if s is None: return ''
    return str(s).replace("\\", "\\\\").replace("'", "\\'")

def to_camel(name):
    parts = name.lower().split('_')
    return parts[0] + ''.join(p.capitalize() for p in parts[1:])

def load_moves_enum():
    with open(MOVES_ENUM) as f:
        content = f.read()
    names = re.findall(r'^\s+([A-Z][A-Z0-9_]+)', content, re.MULTILINE)
    return [n for n in names if n != 'NONE']

def parse_type_enum():
    """Parse Type enum from type.ts"""
    type_file = f'{POKEVOID}/data/type.ts'
    try:
        with open(type_file) as f:
            content = f.read()
        m = re.search(r'export enum Type \{(.*?)\}', content, re.DOTALL)
        if not m: return {}
        result = {}
        val = -1
        for line in m.group(1).split('\n'):
            line = line.strip().rstrip(',')
            explicit = re.match(r'(\w+)\s*=\s*(-?\d+)', line)
            implicit = re.match(r'^([A-Z][A-Z_]*)$', line)
            if explicit:
                val = int(explicit.group(2))
                result[explicit.group(1)] = val
                val += 1
            elif implicit:
                result[implicit.group(1)] = val
                val += 1
        return result
    except:
        return {}

def parse_move_data():
    """Parse AttackMove/StatusMove/SelfStatusMove calls from move.ts"""
    with open(MOVE_TS) as f:
        content = f.read()

    type_enum = parse_type_enum()

    # Build type int -> name
    type_int_to_name = {v: k.title().replace('_', '') for k, v in type_enum.items()}
    # Override with friendly names
    friendly = {
        0:'Normal',1:'Fighting',2:'Flying',3:'Poison',4:'Ground',5:'Rock',
        6:'Bug',7:'Ghost',8:'Steel',9:'Fire',10:'Water',11:'Grass',
        12:'Electric',13:'Psychic',14:'Ice',15:'Dragon',16:'Dark',17:'Fairy',
        18:'Stellar',19:'All',20:'SMITTY',21:'Glitch',-1:'Unknown',
    }
    type_int_to_name.update(friendly)

    # Parse moves enum to get name->int mapping for Moves.X references
    with open(MOVES_ENUM) as f:
        enum_content = f.read()
    moves_enum = {}
    val = 0
    for line in enum_content.split('\n'):
        line = line.strip().rstrip(',')
        m = re.match(r'([A-Z][A-Z0-9_]+)\s*=\s*(\d+)', line)
        m2 = re.match(r'^([A-Z][A-Z0-9_]+)$', line)
        if m:
            val = int(m.group(2))
            moves_enum[m.group(1)] = val
            val += 1
        elif m2:
            moves_enum[m2.group(1)] = val
            val += 1

    results = {}

    # Pattern: new AttackMove(Moves.NAME, Type.X, MoveCategory.X, power, accuracy, pp, ...)
    # or: new StatusMove(Moves.NAME, Type.X, accuracy, pp, ...)
    # or: new SelfStatusMove(Moves.NAME, Type.X, accuracy, pp, ...)

    attack_pat = re.compile(
        r'new AttackMove\(\s*Moves\.(\w+)\s*,\s*Type\.(\w+)\s*,\s*MoveCategory\.(\w+)\s*,\s*(-?\d+)\s*,\s*(-?\d+)\s*,\s*(-?\d+)'
    )
    status_pat = re.compile(
        r'new (?:Status|SelfStatus)Move\(\s*Moves\.(\w+)\s*,\s*Type\.(\w+)\s*,(?:\s*MoveCategory\.\w+\s*,)?\s*(-?\d+)\s*,\s*(-?\d+)'
    )

    for m in attack_pat.finditer(content):
        key = m.group(1)
        type_name_raw = m.group(2)
        category_raw  = m.group(3)
        power    = int(m.group(4))
        accuracy = int(m.group(5))
        pp       = int(m.group(6))
        type_int = type_enum.get(type_name_raw, -1)
        results[key] = {
            'type': type_int,
            'type_name': friendly.get(type_int, type_name_raw.title()),
            'category': category_raw.lower(),
            'power': power if power > 0 else None,
            'accuracy': accuracy if accuracy > 0 else None,
            'pp': pp,
        }

    for m in status_pat.finditer(content):
        key = m.group(1)
        if key in results: continue  # already got it from attack pattern
        type_name_raw = m.group(2)
        accuracy = int(m.group(3))
        pp       = int(m.group(4))
        type_int = type_enum.get(type_name_raw, -1)
        results[key] = {
            'type': type_int,
            'type_name': friendly.get(type_int, type_name_raw.title()),
            'category': 'status',
            'power': None,
            'accuracy': accuracy if accuracy > 0 else None,
            'pp': pp,
        }

    return results

def main():
    print("Loading moves enum...")
    move_keys = load_moves_enum()
    print(f"  {len(move_keys)} moves in enum")

    print("Loading move locale...")
    with open(MOVE_JSON) as f:
        locale = json.load(f)

    print("Parsing move data from move.ts...")
    move_data = parse_move_data()
    print(f"  {len(move_data)} moves parsed from move.ts")

    inserted = skipped = 0
    for move_key in move_keys:
        camel = to_camel(move_key)
        info  = locale.get(camel)
        if not info:
            skipped += 1
            continue

        name     = info.get('name', move_key.replace('_', ' ').title())
        is_smitty  = 1 if move_key in SMITTY_KEYS else 0
        is_dynamic = 1 if move_key in DYNAMIC_TYPE_KEYS else 0
        data = move_data.get(move_key, {})

        type_int  = data.get('type')
        type_name = data.get('type_name')
        category  = data.get('category')
        power     = data.get('power')
        accuracy  = data.get('accuracy')
        pp        = data.get('pp')

        type_sql     = str(type_int)   if type_int  is not None else 'NULL'
        type_name_sql = f"'{escape(type_name)}'" if type_name else 'NULL'
        category_sql = f"'{escape(category)}'" if category else 'NULL'
        power_sql    = str(power)      if power     is not None else 'NULL'
        accuracy_sql = str(accuracy)   if accuracy  is not None else 'NULL'
        pp_sql       = str(pp)         if pp        is not None else 'NULL'

        sql = f"""
INSERT INTO core_moves (move_key, name, is_smitty, type, type_name, category, power, accuracy, pp, is_dynamic_type, created_at, updated_at)
VALUES ('{escape(move_key)}', '{escape(name)}', {is_smitty}, {type_sql}, {type_name_sql}, {category_sql}, {power_sql}, {accuracy_sql}, {pp_sql}, {is_dynamic}, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name=VALUES(name), is_smitty=VALUES(is_smitty),
    type=VALUES(type), type_name=VALUES(type_name), category=VALUES(category),
    power=VALUES(power), accuracy=VALUES(accuracy), pp=VALUES(pp),
    is_dynamic_type=VALUES(is_dynamic_type), updated_at=NOW();
"""
        run_sql(sql)
        inserted += 1

    print(f"Done! {inserted} moves upserted, {skipped} skipped.")

if __name__ == '__main__':
    main()
