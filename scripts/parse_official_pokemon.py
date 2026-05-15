#!/usr/bin/env python3
"""
parse_official_pokemon.py
Parses pokevoid's pokemon-species.ts to extract all official Pokémon
(base forms + mega/gmax/regional variants) and upserts into core_pokemon table.
"""
import re
import json
import subprocess
import sys

POKEVOID         = '/var/www/void.scooom.com/pokevoid/src'
POKEMON_SPECIES  = f'{POKEVOID}/data/pokemon-species.ts'
ABILITIES_ENUM   = f'{POKEVOID}/enums/abilities.ts'
ABILITY_JSON     = f'{POKEVOID}/locales/en/ability.json'
POKEMON_JSON     = f'{POKEVOID}/locales/en/pokemon.json'
SPECIES_ENUM     = f'{POKEVOID}/enums/species.ts'

DB_NAME = 'pokevoid'
DB_USER = 'void'
DB_PASS = '827uh6aV8VI7F50D30BF'

def run_sql(sql):
    result = subprocess.run(
        ['mariadb', '-u', DB_USER, f'-p{DB_PASS}', DB_NAME],
        input=sql, capture_output=True, text=True
    )
    if result.returncode != 0:
        print(f"SQL ERROR: {result.stderr.strip()}", file=sys.stderr)
    return result

def escape(s):
    if s is None:
        return ''
    return str(s).replace("\\", "\\\\").replace("'", "\\'").replace("\n", "\\n")

def load_species_enum():
    with open(SPECIES_ENUM) as f:
        content = f.read()
    result = {}
    current_val = 0
    for line in content.split('\n'):
        line = line.strip().rstrip(',')
        explicit = re.match(r'^(\w+)\s*=\s*(\d+)', line)
        implicit = re.match(r'^([A-Z][A-Z0-9_]+)\s*$', line)
        if explicit:
            name = explicit.group(1)
            current_val = int(explicit.group(2))
            if name not in ('export', 'enum', 'Species'):
                result[name] = current_val
            current_val += 1
        elif implicit:
            name = implicit.group(1)
            if name not in ('export', 'enum', 'Species'):
                result[name] = current_val
                current_val += 1
    return result

def load_abilities_enum():
    with open(ABILITIES_ENUM) as f:
        content = f.read()
    result = {}
    current_val = 0
    for line in content.split('\n'):
        line = line.strip().rstrip(',')
        explicit = re.match(r'^(\w+)\s*=\s*(\d+)', line)
        implicit = re.match(r'^([A-Z][A-Z0-9_]+)\s*$', line)
        if explicit:
            name = explicit.group(1)
            current_val = int(explicit.group(2))
            if name not in ('export', 'enum', 'Abilities'):
                result[name] = current_val
            current_val += 1
        elif implicit:
            name = implicit.group(1)
            if name not in ('export', 'enum', 'Abilities'):
                result[name] = current_val
                current_val += 1
    return result

def load_ability_names():
    """Load enum_key -> display name from ability.json"""
    with open(ABILITY_JSON) as f:
        data = json.load(f)
    # Keys are camelCase, we need UPPER_SNAKE -> camelCase conversion
    def to_camel(name):
        parts = name.lower().split('_')
        return parts[0] + ''.join(p.capitalize() for p in parts[1:])
    result = {}
    for enum_name in load_abilities_enum():
        key = to_camel(enum_name)
        info = data.get(key, {})
        result[enum_name] = info.get('name', enum_name.replace('_', ' ').title())
    return result

def load_pokemon_display_names():
    """Load species_key (lower) -> display name"""
    with open(POKEMON_JSON) as f:
        return json.load(f)

def resolve_ability_name(val, ability_names):
    m = re.match(r'Abilities\.(\w+)', val.strip())
    if m:
        return ability_names.get(m.group(1), m.group(1).replace('_', ' ').title())
    return None

def resolve_type(val):
    TYPE_MAP = {
        'NORMAL': 0, 'FIGHTING': 1, 'FLYING': 2, 'POISON': 3,
        'GROUND': 4, 'ROCK': 5, 'BUG': 6, 'GHOST': 7,
        'STEEL': 8, 'FIRE': 9, 'WATER': 10, 'GRASS': 11,
        'ELECTRIC': 12, 'PSYCHIC': 13, 'ICE': 14, 'DRAGON': 15,
        'DARK': 16, 'FAIRY': 17,
    }
    if not val or val.strip() == 'null':
        return None
    m = re.match(r'Type\.(\w+)', val.strip())
    if m:
        return TYPE_MAP.get(m.group(1))
    return None

def normalize(text):
    return re.sub(r'\s+', ' ', text.replace('\n', ' ')).strip()

def extract_balanced(content, start_idx):
    """Extract balanced parentheses starting at start_idx (which should be '(')"""
    depth = 0
    i = start_idx
    while i < len(content):
        if content[i] == '(':
            depth += 1
        elif content[i] == ')':
            depth -= 1
            if depth == 0:
                return content[start_idx:i+1]
        i += 1
    return None

def split_args(inner):
    """Split on commas, respecting nested parens/brackets"""
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

def species_key_to_display(key, pokemon_names):
    """Convert SPECIES_KEY to display name"""
    lower = key.lower()
    # Handle regional prefixes
    for prefix in ('alola_', 'hisui_', 'paldea_', 'galar_'):
        if lower.startswith(prefix):
            region = prefix.rstrip('_').title()
            base = lower[len(prefix):]
            base_name = pokemon_names.get(base, base.title())
            return f"{base_name} ({region} Form)"
    # Handle special cases
    if lower == 'bloodmoon_ursaluna':
        return "Ursaluna (Bloodmoon)"
    return pokemon_names.get(lower, key.replace('_', ' ').title())

def form_display_name(species_display, form_name, form_key):
    """Build a full display name for a non-base form"""
    if not form_name or form_name.lower() in ('normal', 'base', ''):
        return species_display
    return f"{species_display} ({form_name})"

def upsert(dex_number, species_key, name, form_name, form_key, form_index, type1, type2,
           ab1, ab2, abh, bst, hp, atk, def_, spatk, spdef, spd):
    t2 = str(type2) if type2 is not None else 'NULL'
    ab1_s = f"'{escape(ab1)}'" if ab1 else 'NULL'
    ab2_s = f"'{escape(ab2)}'" if ab2 else 'NULL'
    abh_s = f"'{escape(abh)}'" if abh else 'NULL'

    sql = f"""
INSERT INTO core_pokemon
    (dex_number, species_key, name, form_name, form_key, form_index,
     type1, type2, ability1, ability2, ability_hidden,
     bst, hp, atk, def, spatk, spdef, spd, created_at, updated_at)
VALUES
    ({dex_number}, '{escape(species_key)}', '{escape(name)}',
     '{escape(form_name)}', '{escape(form_key)}', {form_index},
     {type1}, {t2}, {ab1_s}, {ab2_s}, {abh_s},
     {bst}, {hp}, {atk}, {def_}, {spatk}, {spdef}, {spd}, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    species_key=VALUES(species_key), name=VALUES(name), form_name=VALUES(form_name),
    form_index=VALUES(form_index),
    type1=VALUES(type1), type2=VALUES(type2),
    ability1=VALUES(ability1), ability2=VALUES(ability2), ability_hidden=VALUES(ability_hidden),
    bst=VALUES(bst), hp=VALUES(hp), atk=VALUES(atk), def=VALUES(def),
    spatk=VALUES(spatk), spdef=VALUES(spdef), spd=VALUES(spd), updated_at=NOW();
"""
    run_sql(sql)

def main():
    print("Loading enums and locales...")
    species_enum   = load_species_enum()
    ability_names  = load_ability_names()
    pokemon_names  = load_pokemon_display_names()

    print(f"  {len(species_enum)} species, {len(ability_names)} abilities, {len(pokemon_names)} display names")

    with open(POKEMON_SPECIES) as f:
        content = f.read()

    # Find all PokemonSpecies( calls
    pattern = re.compile(r'\bnew PokemonSpecies\s*\(')
    inserted = 0
    skipped = 0

    for m in pattern.finditer(content):
        block = extract_balanced(content, m.end() - 1)
        if not block:
            continue
        inner = block[1:-1]  # strip outer parens
        args = split_args(inner)
        if len(args) < 16:
            skipped += 1
            continue

        try:
            # PokemonSpecies(Species.X, gen, legendary, sublegendary, mythical,
            #   category, type1, type2, height, weight,
            #   ab1, ab2, ha, bst, hp, atk, def, spatk, spdef, spd,
            #   catchRate, baseFriendship, baseExp, growthRate, malePercent, genderDiffs,
            #   [canChangeForm], [forms...])
            species_m = re.match(r'Species\.(\w+)', args[0].strip())
            if not species_m:
                skipped += 1
                continue

            species_key = species_m.group(1)
            # Skip non-game-mon entries (custom glitch forms handled elsewhere)
            dex_number = species_enum.get(species_key)
            if dex_number is None:
                skipped += 1
                continue

            type1 = resolve_type(args[6])
            type2 = resolve_type(args[7])
            if type1 is None:
                skipped += 1
                continue

            ab1 = resolve_ability_name(args[10], ability_names)
            ab2 = resolve_ability_name(args[11], ability_names)
            abh = resolve_ability_name(args[12], ability_names)

            bst   = int(args[13].strip())
            hp    = int(args[14].strip())
            atk   = int(args[15].strip())
            def_  = int(args[16].strip())
            spatk = int(args[17].strip())
            spdef = int(args[18].strip())
            spd   = int(args[19].strip())

            display_name = species_key_to_display(species_key, pokemon_names)

            # Find PokemonForm children inside this block
            form_pattern = re.compile(r'\bnew PokemonForm\s*\(')
            forms_found = 0
            for fm in form_pattern.finditer(block):
                form_block = extract_balanced(block, fm.end() - 1)
                if not form_block:
                    continue
                fargs = split_args(form_block[1:-1])
                if len(fargs) < 10:
                    continue
                try:
                    form_name_raw = fargs[0].strip().strip('"\'')
                    form_key_raw  = fargs[1].strip().strip('"\'')
                    # resolve SpeciesFormKey.X
                    sk_m = re.match(r'SpeciesFormKey\.(\w+)', fargs[1].strip())
                    if sk_m:
                        form_key_raw = sk_m.group(1).lower().replace('_', '-')

                    ftype1 = resolve_type(fargs[2])
                    ftype2 = resolve_type(fargs[3])
                    if ftype1 is None:
                        continue
                    fab1 = resolve_ability_name(fargs[6], ability_names)
                    fab2 = resolve_ability_name(fargs[7], ability_names)
                    fabh = resolve_ability_name(fargs[8], ability_names)
                    fbst   = int(fargs[9].strip())
                    fhp    = int(fargs[10].strip())
                    fatk   = int(fargs[11].strip())
                    fdef   = int(fargs[12].strip())
                    fspatk = int(fargs[13].strip())
                    fspdef = int(fargs[14].strip())
                    fspd   = int(fargs[15].strip())

                    full_name = form_display_name(display_name, form_name_raw, form_key_raw)
                    upsert(dex_number, species_key, full_name, form_name_raw, form_key_raw, forms_found,
                           ftype1, ftype2, fab1, fab2, fabh,
                           fbst, fhp, fatk, fdef, fspatk, fspdef, fspd)
                    forms_found += 1
                except Exception as e:
                    continue

            if forms_found == 0:
                # No explicit forms — insert base
                upsert(dex_number, species_key, display_name, '', '', 0,
                       type1, type2, ab1, ab2, abh,
                       bst, hp, atk, def_, spatk, spdef, spd)

            inserted += 1
            if inserted % 100 == 0:
                print(f"  {inserted} species processed...")

        except Exception as e:
            skipped += 1
            continue

    print(f"\nDone! {inserted} species processed, {skipped} skipped.")

if __name__ == '__main__':
    main()
