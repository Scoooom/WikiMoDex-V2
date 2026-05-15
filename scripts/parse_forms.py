#!/usr/bin/env python3
import re
import json
import subprocess
import sys
import os
from dotenv import load_dotenv


dir_path = os.path.dirname(os.path.realpath(__file__)) + "/../"
from dotenv import load_dotenv

load_dotenv(dir_path+ "/.env")

# DB config
DB_NAME = os.getenv("DB_DATABASE")
DB_USER = os.getenv("DB_USERNAME")
DB_PASS = os.getenv("DB_PASSWORD")


# Paths
POKEVOID = dir_path + '/pokevoid/src'
ABILITY_JSON = f'{POKEVOID}/locales/en/ability.json'
ABILITIES_ENUM = f'{POKEVOID}/enums/abilities.ts'
SPECIES_ENUM = f'{POKEVOID}/enums/species.ts'
POKEMON_FORMS = f'{POKEVOID}/data/pokemon-forms.ts'
POKEMON_SPECIES = f'{POKEVOID}/data/pokemon-species.ts'
MODIFIER_TYPE = f'{POKEVOID}/modifier/modifier-type.ts'
MODIFIER_TYPE_JSON = f'{POKEVOID}/locales/en/modifier-type.json'


def run_sql(sql):
    result = subprocess.run(
        ['mariadb', '-u', DB_USER, f'-p{DB_PASS}', DB_NAME],
        input=sql, capture_output=True, text=True
    )
    if result.returncode != 0:
        print(f"SQL ERROR: {result.stderr.strip()}", file=sys.stderr)
    return result

def query_sql(sql):
    result = subprocess.run(
        ['mariadb', '-u', DB_USER, f'-p{DB_PASS}', '--skip-column-names', DB_NAME],
        input=sql, capture_output=True, text=True
    )
    return result.stdout.strip()

def enum_to_camel(name):
    parts = name.lower().split('_')
    return parts[0] + ''.join(p.capitalize() for p in parts[1:])

def load_abilities():
    with open(ABILITIES_ENUM) as f:
        content = f.read()
    enum_names = re.findall(r'^\s*(\w+)', content, re.MULTILINE)
    enum_names = [a for a in enum_names if a not in ['export', 'enum', 'Abilities']]

    with open(ABILITY_JSON) as f:
        ability_data = json.load(f)

    result = {}
    for name in enum_names:
        key = enum_to_camel(name)
        info = ability_data.get(key, {})
        result[name] = {
            'name': info.get('name', name.replace('_', ' ').title()),
            'description': info.get('description', 'No description available.')
        }
    return result

def load_species():
    with open(SPECIES_ENUM) as f:
        content = f.read()
    result = {}
    current_val = 0
    for line in content.split('\n'):
        line = line.strip()
        explicit = re.match(r'^(\w+)\s*=\s*(\d+)', line)
        implicit = re.match(r'^(\w+)\s*[,]?\s*$', line)
        if explicit:
            name = explicit.group(1)
            current_val = int(explicit.group(2))
            if name not in ['export', 'enum', 'Species']:
                result[name] = current_val
            current_val += 1
        elif implicit:
            name = implicit.group(1)
            if name not in ['export', 'enum', 'Species', '{', '}', '']:
                result[name] = current_val
                current_val += 1
    return result

def load_types():
    return {
        'UNKNOWN': -1, 'NONE': -1,
        'NORMAL': 0, 'FIGHTING': 1, 'FLYING': 2, 'POISON': 3,
        'GROUND': 4, 'ROCK': 5, 'BUG': 6, 'GHOST': 7,
        'STEEL': 8, 'FIRE': 9, 'WATER': 10, 'GRASS': 11,
        'ELECTRIC': 12, 'PSYCHIC': 13, 'ICE': 14, 'DRAGON': 15,
        'DARK': 16, 'FAIRY': 17,
    }

def resolve_type(val, types):
    if val is None or val.strip() == 'null':
        return None
    m = re.match(r'Type\.(\w+)', val.strip())
    if m:
        return types.get(m.group(1))
    return None

def resolve_species(val, species):
    ids = re.findall(r'Species\.(\w+)', val)
    return ','.join(str(species.get(s, 0)) for s in ids)

def normalize_call(text):
    return re.sub(r'\s+', ' ', text.replace('\n', ' ')).strip()

def extract_calls(content, func_name):
    results = []
    pattern = re.compile(r'\b' + re.escape(func_name) + r'\s*\(', re.MULTILINE)
    for m in pattern.finditer(content):
        depth = 0
        i = m.end() - 1
        while i < len(content):
            if content[i] == '(':
                depth += 1
            elif content[i] == ')':
                depth -= 1
                if depth == 0:
                    results.append(normalize_call(content[m.start():i+1]))
                    break
            i += 1
    return results

def parse_args(call, func_name):
    start = call.index('(') + 1
    end = call.rindex(')')
    inner = call[start:end]
    args = []
    depth = 0
    current = ''
    for ch in inner:
        if ch in '([{':
            depth += 1
            current += ch
        elif ch in ')]}':
            depth -= 1
            current += ch
        elif ch == ',' and depth == 0:
            args.append(current.strip())
            current = ''
        else:
            current += ch
    if current.strip():
        args.append(current.strip())
    return args

def escape(s):
    return str(s).replace("'", "\\'")

def get_smitty_form_code(name):
    p1 = subprocess.Popen(['grep', 'addSmittyForm(', POKEMON_FORMS], stdout=subprocess.PIPE)
    p2 = subprocess.Popen(['awk', '{$1=$1;print}'], stdin=p1.stdout, stdout=subprocess.PIPE)
    p3 = subprocess.Popen(['grep', '-i', name], stdin=p2.stdout, stdout=subprocess.PIPE)
    p4 = subprocess.Popen(['awk', '-F"', '{print $2}'], stdin=p3.stdout, stdout=subprocess.PIPE)
    p1.stdout.close(); p2.stdout.close(); p3.stdout.close()
    form_name = p4.communicate()[0].decode().strip()
    if not form_name:
        return ''
    p5 = subprocess.Popen(['grep', form_name, MODIFIER_TYPE], stdout=subprocess.PIPE)
    p6 = subprocess.Popen(['awk', '{$1=$1;print}'], stdin=p5.stdout, stdout=subprocess.PIPE)
    p5.stdout.close()
    result = p6.communicate()[0].decode().strip()
    m = re.search(r"'[^']+'\s*:\s*'([^']+)'", result)
    return m.group(1) if m else ''

def get_uni_form_code(name):
    p1 = subprocess.Popen(['grep', 'addUni', POKEMON_FORMS], stdout=subprocess.PIPE)
    p2 = subprocess.Popen(['awk', '{$1=$1;print}'], stdin=p1.stdout, stdout=subprocess.PIPE)
    p3 = subprocess.Popen(['grep', '-i', name], stdin=p2.stdout, stdout=subprocess.PIPE)
    p4 = subprocess.Popen(['awk', '-F"', '{print $2}'], stdin=p3.stdout, stdout=subprocess.PIPE)
    p1.stdout.close(); p2.stdout.close(); p3.stdout.close()
    form_name = p4.communicate()[0].decode().strip()
    if not form_name:
        return ''
    p5 = subprocess.Popen(['grep', form_name, MODIFIER_TYPE], stdout=subprocess.PIPE)
    p6 = subprocess.Popen(['awk', '{$1=$1;print}'], stdin=p5.stdout, stdout=subprocess.PIPE)
    p5.stdout.close()
    result = p6.communicate()[0].decode().strip()
    m = re.search(r"'[^']+'\s*:\s*'([^']+)'", result)
    return m.group(1) if m else ''

def load_form_change_items():
    with open(MODIFIER_TYPE_JSON) as f:
        data = json.load(f)
    return data.get('FormChangeItem', {})

def parse_smitty_form_items(content, item_names):
    m = re.search(r'export const SMITTY_FORM_ITEMS\s*=\s*\{(.*?)\n\}', content, re.DOTALL)
    if not m:
        print("  ERROR: Could not find SMITTY_FORM_ITEMS", file=sys.stderr)
        return {}
    result = {}
    inner = m.group(1)
    form_pattern = re.compile(r"'([\w-]+)'\s*:\s*\[(.*?)\]", re.DOTALL)
    for form_match in form_pattern.finditer(inner):
        form_name = form_match.group(1)
        items_str = form_match.group(2)
        items = re.findall(r'FormChangeItem\.(\w+)', items_str)
        result[form_name] = [
            {'enum': item, 'name': item_names.get(item, item.replace('_', ' ').title())}
            for item in items
        ]
    return result

def upsert_ability(enum_name, name, description):
    existing = query_sql(f"SELECT CONCAT(name,'|||',description) FROM abilities WHERE enum_name='{escape(enum_name)}'")
    expected = f"{name}|||{description}"

    sql = f"""
INSERT INTO abilities (enum_name, name, description, created_at, updated_at)
VALUES ('{escape(enum_name)}', '{escape(name)}', '{escape(description)}', NOW(), NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), updated_at=NOW();
"""
    run_sql(sql)
    result = query_sql(f"SELECT id FROM abilities WHERE enum_name = '{escape(enum_name)}';")
    if existing and existing != expected:
        print(f"  CHANGED: ability {name}")
    elif not existing:
        print(f"  NEW: ability {name}")
    return int(result) if result else None

def upsert_form(form_type, name, og_mon, type1, type2, ab1_id, ab2_id, ha_id, bst, hp, atk, def_, spatk, spdef, spd, form_code=''):
    existing = query_sql(f"SELECT id FROM builtin_forms WHERE name='{escape(name)}'")
    t2_val = str(type2) if type2 is not None else 'NULL'
    og_val = f"'{og_mon}'" if og_mon else 'NULL'
    code_val = f"'{escape(form_code)}'" if form_code else 'NULL'

    sql = f"""
INSERT INTO builtin_forms
    (name, form_type, og_mon, type1, type2, ab1_id, ab2_id, ha_id, bst, hp, atk, def, spatk, spdef, spd, form_code, created_at, updated_at)
VALUES
    ('{escape(name)}', '{form_type}', {og_val}, {type1}, {t2_val},
     {ab1_id}, {ab2_id}, {ha_id},
     {bst}, {hp}, {atk}, {def_}, {spatk}, {spdef}, {spd}, {code_val}, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    form_type=VALUES(form_type), og_mon=VALUES(og_mon), type1=VALUES(type1), type2=VALUES(type2),
    ab1_id=VALUES(ab1_id), ab2_id=VALUES(ab2_id), ha_id=VALUES(ha_id),
    bst=VALUES(bst), hp=VALUES(hp), atk=VALUES(atk), def=VALUES(def),
    spatk=VALUES(spatk), spdef=VALUES(spdef), spd=VALUES(spd),
    form_code=VALUES(form_code), updated_at=NOW();
"""
    result = run_sql(sql)
    if result.returncode == 0:
        if not existing:
            print(f"  NEW: {name} ({form_type})")
    else:
        print(f"  ERROR: {name}", file=sys.stderr)

def upsert_smitty_items(form_name, items):
    changed = 0
    new = 0
    for i, item in enumerate(items):
        existing = query_sql(f"SELECT item_name FROM smitty_items WHERE form_name='{escape(form_name)}' AND enum_name='{escape(item['enum'])}'")
        if existing == item['name']:
            continue
        sql = f"""
INSERT INTO smitty_items (form_name, enum_name, item_name, sort_order, created_at, updated_at)
VALUES ('{escape(form_name)}', '{escape(item['enum'])}', '{escape(item['name'])}', {i}, NOW(), NOW())
ON DUPLICATE KEY UPDATE item_name=VALUES(item_name), sort_order=VALUES(sort_order), updated_at=NOW();
"""
        run_sql(sql)
        if existing:
            changed += 1
        else:
            new += 1
    if new > 0:
        print(f"  NEW: {form_name} ({new} items)")
    if changed > 0:
        print(f"  CHANGED: {form_name} ({changed} items updated)")

def main():
    print("Loading enums...")
    abilities = load_abilities()
    species = load_species()
    types = load_types()

    print(f"\nUpserting {len(abilities)} abilities...")
    ability_ids = {}
    for enum_name, info in abilities.items():
        aid = upsert_ability(enum_name, info['name'], info['description'])
        if aid:
            ability_ids[enum_name] = aid
    print(f"Done. {len(ability_ids)} abilities in DB.")

    def get_ability_id(val):
        m = re.match(r'Abilities\.(\w+)', val.strip())
        if m:
            return ability_ids.get(m.group(1))
        return None

    # Parse core glitches
    print("\nParsing core glitches...")
    with open(POKEMON_SPECIES) as f:
        species_content = f.read()

    core_calls = extract_calls(species_content, 'addFormToSpecies')
    core_count = 0
    for call in core_calls:
        try:
            args = parse_args(call, 'addFormToSpecies')
            if len(args) < 15:
                continue
            if 'GLITCH' not in args[2]:
                continue
            og_mon = resolve_species(args[0], species)
            name = args[1].strip().strip('"\'')
            type1 = resolve_type(args[3], types)
            type2 = resolve_type(args[4], types)
            ab1_id = get_ability_id(args[5])
            ab2_id = get_ability_id(args[6])
            ha_id = get_ability_id(args[7])
            bst = int(args[8].strip())
            hp = int(args[9].strip())
            atk = int(args[10].strip())
            def_ = int(args[11].strip())
            spatk = int(args[12].strip())
            spdef = int(args[13].strip())
            spd = int(args[14].strip())
            if not all([ab1_id, ab2_id, ha_id]):
                print(f"  SKIP {name}: missing ability ID", file=sys.stderr)
                continue
            upsert_form('core', name, og_mon, type1, type2, ab1_id, ab2_id, ha_id, bst, hp, atk, def_, spatk, spdef, spd)
            core_count += 1
        except Exception as e:
            print(f"  SKIP (core): {e} — {call[:80]}", file=sys.stderr)

    print(f"Core glitches: {core_count}")

    # Parse smitty forms
    print("\nParsing smitty forms...")
    with open(POKEMON_FORMS) as f:
        forms_content = f.read()

    smitty_calls = extract_calls(forms_content, 'addSmittyForm')
    smitty_count = 0
    for call in smitty_calls:
        if 'function addSmittyForm' in call:
            continue
        try:
            args = parse_args(call, 'addSmittyForm')
            if len(args) < 15:
                continue
            og_mon = resolve_species(args[0], species)
            name = args[1].strip().strip('"\'')
            type1 = resolve_type(args[3], types)
            type2 = resolve_type(args[4], types)
            ab1_id = get_ability_id(args[5])
            ab2_id = get_ability_id(args[6])
            ha_id = get_ability_id(args[7])
            bst = int(args[8].strip())
            hp = int(args[9].strip())
            atk = int(args[10].strip())
            def_ = int(args[11].strip())
            spatk = int(args[12].strip())
            spdef = int(args[13].strip())
            spd = int(args[14].strip())
            if not all([ab1_id, ab2_id, ha_id]):
                print(f"  SKIP {name}: missing ability ID", file=sys.stderr)
                continue
            code = get_smitty_form_code(name)
            upsert_form('smitty_form', name, og_mon, type1, type2, ab1_id, ab2_id, ha_id, bst, hp, atk, def_, spatk, spdef, spd, code)
            smitty_count += 1
        except Exception as e:
            print(f"  SKIP (smitty): {e} — {call[:80]}", file=sys.stderr)

    print(f"Smitty forms: {smitty_count}")

    # Parse universal smitty forms
    print("\nParsing universal smitty forms...")
    uni_calls = extract_calls(forms_content, 'addUniversalSmittyForm')
    uni_count = 0
    for call in uni_calls:
        if 'function addUniversalSmittyForm' in call or 'export function' in call:
            continue
        try:
            inner = call[call.index('({')+1:call.rindex('})')]

            def extract(key, text):
                m = re.search(key + r'\s*:\s*([^,}]+)', text)
                return m.group(1).strip() if m else None

            name = extract('formName', inner)
            if name:
                name = name.strip('"\'')
            type1 = resolve_type(extract('primaryType', inner), types)
            type2 = resolve_type(extract('secondaryType', inner), types)
            ab1_id = get_ability_id(extract('ability1', inner))
            ab2_id = get_ability_id(extract('ability2', inner))
            ha_id = get_ability_id(extract('abilityHidden', inner))
            bst = int(extract('totalStats', inner))
            hp = int(extract('hp', inner))
            atk = int(extract('attack', inner))
            def_ = int(extract('defense', inner))
            spatk = int(extract('spAttack', inner))
            spdef = int(extract('spDefense', inner))
            spd = int(extract('speed', inner))
            if not all([ab1_id, ab2_id, ha_id]):
                print(f"  SKIP {name}: missing ability ID", file=sys.stderr)
                continue
            code = get_uni_form_code(name)
            upsert_form('smitty', name, None, type1, type2, ab1_id, ab2_id, ha_id, bst, hp, atk, def_, spatk, spdef, spd, code)
            uni_count += 1
        except Exception as e:
            print(f"  SKIP (uni): {e} — {call[:80]}", file=sys.stderr)

    print(f"Universal smitty forms: {uni_count}")

    # Parse smitty items
    print("\nParsing smitty items...")
    item_names = load_form_change_items()
    smitty_form_items = parse_smitty_form_items(forms_content, item_names)
    for form_name, items in smitty_form_items.items():
        upsert_smitty_items(form_name, items)
    print(f"Smitty item forms: {len(smitty_form_items)}")

    print("\nDone!")

if __name__ == '__main__':
    main()
