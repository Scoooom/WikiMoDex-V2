#!/usr/bin/env python3
"""
parse_alt_builds.py
Parses pokemon-alt-buid.ts and upserts named Alt Builds into the alt_builds table.
Only processes rank >= 1 builds (ignores signature placeholders).
"""
import re
import json
import subprocess
import sys

import os
from dotenv import load_dotenv


dir_path = os.path.dirname(os.path.realpath(__file__)) + "/../"

load_dotenv(dir_path+ "/.env")

# DB config
DB_NAME = os.getenv("DB_DATABASE")
DB_USER = os.getenv("DB_USERNAME")
DB_PASS = os.getenv("DB_PASSWORD")


# Paths
POKEVOID = dir_path + '/pokevoid/src'
ALT_BUILD_TS   = f'{POKEVOID}/data/pokemon-alt-buid.ts'
SPECIES_TS     = f'{POKEVOID}/enums/species.ts'
POKEMON_TS     = f'{POKEVOID}/data/pokemon-species.ts'

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
    return str(s).replace("\\", "\\\\").replace("'", "\\'").replace("\n", "\\n").replace("\r", "")

# ── Species dex number lookup ──────────────────────────────────────────────
def build_species_map(content):
    lines = content.split('\n')
    current = 0
    species_map = {}
    for line in lines:
        line = line.strip().rstrip(',')
        m = re.match(r'^(\w+)\s*=\s*(\d+)$', line)
        if m:
            current = int(m.group(2))
            species_map[m.group(1)] = current
            continue
        m = re.match(r'^(\w+)$', line)
        if m and m.group(1) not in ('export', 'enum', 'Species', '{', '}', ''):
            current += 1
            species_map[m.group(1)] = current
    return species_map

def build_base_stats_map(content):
    """Parse PokemonSpecies constructor calls to extract base stats.
    Format: new PokemonSpecies(Species.X, ..., BST, HP, ATK, DEF, SPATK, SPDEF, SPD, ...)
    The BST and stats are positional — BST at index 13, then HP,ATK,DEF,SPATK,SPDEF,SPD at 14-19.
    """
    stats_map = {}
    # Match: new PokemonSpecies(Species.NAME, ...numbers...)
    pattern = re.compile(
        r'new PokemonSpecies\(Species\.(\w+),\s*'  # species name
        r'(\d+),\s*'   # generation
        r'(?:true|false),\s*' * 3 +               # legendary/sublegendary/mythical
        r'"[^"]*",\s*' +                           # name
        r'Type\.\w+,\s*(?:Type\.\w+|null),\s*' +  # type1, type2
        r'[\d.]+,\s*[\d.]+,\s*' +                 # height, weight
        r'Abilities\.\w+,\s*Abilities\.\w+,\s*Abilities\.\w+,\s*' +  # abilities
        r'(\d+),\s*'  +                            # BST
        r'(\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*'  # HP ATK DEF SPATK SPDEF SPD
    )
    for m in pattern.finditer(content):
        species = m.group(1)
        hp, atk, def_, spatk, spdef, spd = int(m.group(4)), int(m.group(5)), int(m.group(6)), int(m.group(7)), int(m.group(8)), int(m.group(9))
        stats_map[species] = [hp, atk, def_, spatk, spdef, spd]
    return stats_map

# ── Name humanisers ────────────────────────────────────────────────────────
def enum_to_name(val):
    return val.replace('_', ' ').title()

def strip_species_prefix(build_id, species_name):
    species_words = species_name.upper().replace(' ', '_').replace('-', '_')
    if build_id.startswith(species_words + '_'):
        remainder = build_id[len(species_words)+1:]
        return enum_to_name(remainder)
    first_word = build_id.split('_')[0]
    if species_name.upper().startswith(first_word):
        remainder = '_'.join(build_id.split('_')[1:])
        return enum_to_name(remainder)
    return enum_to_name(build_id)

# ── Type mapping ───────────────────────────────────────────────────────────
TYPE_MAP = {
    'NORMAL': 'Normal', 'FIGHTING': 'Fighting', 'FLYING': 'Flying',
    'POISON': 'Poison', 'GROUND': 'Ground', 'ROCK': 'Rock',
    'BUG': 'Bug', 'GHOST': 'Ghost', 'STEEL': 'Steel', 'FIRE': 'Fire',
    'WATER': 'Water', 'GRASS': 'Grass', 'ELECTRIC': 'Electric',
    'PSYCHIC': 'Psychic', 'ICE': 'Ice', 'DRAGON': 'Dragon',
    'DARK': 'Dark', 'FAIRY': 'Fairy', 'UNKNOWN': None,
}

# ── Champion mapping ───────────────────────────────────────────────────────
CHAMPION_BUILD_MAP = {
    'ONIX_CRYSTAL_LEVIATHAN': 'brock',
    'GEODUDE_PHANTOM_FIST': 'brock',
    'VULPIX_FLAMING_FOREST_SPIRIT': 'brock',
    'ZUBAT_VAMPIRIC_FIEND': 'brock',
    'BONSLY_TEAR_DROP': 'brock',
    'MUDKIP_STONE_SKINNED_SALAMANDER': 'brock',
    'PINECO_IRON_PLATED_GRENADE': 'brock',
    'CROAGUNK_JESTER_OF_PESTILENCE': 'brock',
    'HAPPINY_PINK_FORTRESS': 'brock',
    'LOTAD_SHADOW_LILY': 'brock',
    'COMFEY_AQUA_BLOOM': 'brock',
    'STARYU_CHRONOS_GEAR': 'misty',
    'MAGIKARP_SPLASH_TYRANT': 'misty',
    'PSYDUCK_KAPPA_QUACK': 'misty',
    'POLIWAG_LIGHTNING_DRUM': 'misty',
    'AZURILL_BOUNCE_CHAMPION': 'misty',
    'GOLDEEN_PIERCING_STINGER': 'misty',
    'HORSEA_FIREBALL_SEAHORSE': 'misty',
    'TOGEPI_EGGSHELL_FORTRESS': 'misty',
    'CORSOLA_TOXIC_CORAL': 'misty',
    'LUVDISC_HEARTBREAKER': 'misty',
    'CLAUNCHER_BUSTER_DRAGON_BLASTER': 'misty',
    'RIOLU_SHADOW_WARRIOR': 'apollo_diana',
    'SOLROCK_VOID_CONSTELLATION': 'apollo_diana',
    'LUNATONE_DREAM_WEAVER': 'apollo_diana',
    'LARVESTA_TOXIC_SPINNER': 'apollo_diana',
    'SWABLU_FROST_NIMBUS': 'apollo_diana',
    'CASTFORM_DUST_DEVIL': 'apollo_diana',
    'LITWICK_WYRMFLAME': 'apollo_diana',
    'EEVEE_UNTAMED_SPIRIT': 'apollo_diana',
    'TEDDIURSA_SWEET_TOOTH': 'apollo_diana',
    'CLEFFA_METEORIC_CORE': 'apollo_diana',
    'SUNKERN_PLASMA_SPROUT': 'apollo_diana',
}

def parse_builds(content, species_map):
    builds = re.findall(
        r'\[PokemonAltBuildId\.(\w+)\]:\s*\{(.*?)\},\s*(?=\n\s*\[|\n\})',
        content, re.DOTALL
    )

    results = []
    for build_id, body in builds:
        rank_match = re.search(r'rank:\s*(\d+)', body)
        rank = int(rank_match.group(1)) if rank_match else 0
        if rank < 1:
            continue

        species_match = re.search(r'species:\s*Species\.(\w+)', body)
        species_key = species_match.group(1) if species_match else ''
        species = enum_to_name(species_key)
        dex_number = species_map.get(species_key)

        name = strip_species_prefix(build_id, species)

        type_match = re.search(r'typeChanges:\s*\[Type\.(\w+)(?:,\s*Type\.(\w+))?\]', body)
        type1 = TYPE_MAP.get(type_match.group(1)) if type_match else None
        type2 = TYPE_MAP.get(type_match.group(2)) if type_match and type_match.group(2) else None

        stat_match = re.search(r'statFocus:\s*\[(.*?)\]', body)
        if stat_match:
            stats = re.findall(r'Stat\.(\w+)', stat_match.group(1))
            stat_labels = {'HP': 'HP', 'ATK': 'ATK', 'DEF': 'DEF',
                          'SPATK': 'SP.ATK', 'SPDEF': 'SP.DEF', 'SPD': 'SPD'}
            stat_focus = ' / '.join(stat_labels.get(s, s) for s in stats)
        else:
            stat_focus = ''

        ab_match = re.search(r'abilityChanges:\s*\[(.*?)\]', body, re.DOTALL)
        abilities = []
        if ab_match:
            ab_raw = re.findall(r'Abilities\.(\w+)', ab_match.group(1))
            abilities = [enum_to_name(a) for a in ab_raw if a != 'undefined']

        passive_match = re.search(r'passiveAbilityChange:\s*Abilities\.(\w+)', body)
        passive = enum_to_name(passive_match.group(1)) if passive_match else None

        move_matches = re.findall(r'(\d+):\s*Moves\.(\w+)', body)
        all_moves = [(int(lvl), enum_to_name(mv)) for lvl, mv in move_matches]
        if len(all_moves) > 8:
            key = all_moves[:3] + all_moves[len(all_moves)//2-1:len(all_moves)//2+1] + all_moves[-3:]
        else:
            key = all_moves
        key_moves = [f"Lv{lvl}: {mv}" for lvl, mv in key]

        prevents_evo = 'preventEvolution: true' in body

        prereq_match = re.search(r'prerequisiteBuilds:\s*\[PokemonAltBuildId\.(\w+)\]', body)
        prereq = prereq_match.group(1).lower() if prereq_match else None

        champion = CHAMPION_BUILD_MAP.get(build_id)

        # Extract palette data
        palette_match = re.search(r'spriteColorPalette:\s*\{(.*?)\n    \}', body, re.DOTALL)
        target_palette = []
        dark_palette = []
        if palette_match:
            palette_body = palette_match.group(1)
            parts = palette_body.split('darkPalette')
            target_palette = re.findall(r'"(#[0-9a-fA-F]{6})"', parts[0])
            if len(parts) > 1:
                dark_palette = re.findall(r'"(#[0-9a-fA-F]{6})"', parts[1])

        results.append({
            'build_id':          build_id.lower(),
            'name':              name,
            'species':           species,
            'dex_number':        dex_number,
            'champion':          champion,
            'rank':              rank,
            'type1':             type1,
            'type2':             type2,
            'stat_focus':        stat_focus,
            'ability1':          abilities[0] if len(abilities) > 0 else None,
            'ability2':          abilities[1] if len(abilities) > 1 else None,
            'ability3':          abilities[2] if len(abilities) > 2 else None,
            'passive_ability':   passive,
            'key_moves':         key_moves,
            'prevents_evolution': prevents_evo,
            'prerequisite_build': prereq,
            'target_palette':    target_palette,
            'dark_palette':      dark_palette,
        })

    return results

def main():
    print("Parsing species map...")
    with open(SPECIES_TS) as f:
        species_map = build_species_map(f.read())

    print("Parsing alt builds...")
    with open(ALT_BUILD_TS) as f:
        content = f.read()

    builds = parse_builds(content, species_map)
    print(f"Found {len(builds)} named alt builds (rank >= 1)")

    run_sql("""
        CREATE TABLE IF NOT EXISTS alt_builds (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            build_id VARCHAR(191) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            species VARCHAR(100),
            dex_number SMALLINT UNSIGNED,
            champion VARCHAR(50),
            `rank` INT DEFAULT 1,
            type1 VARCHAR(50),
            type2 VARCHAR(50),
            stat_focus VARCHAR(50),
            ability1 VARCHAR(100),
            ability2 VARCHAR(100),
            ability3 VARCHAR(100),
            passive_ability VARCHAR(100),
            key_moves TEXT,
            prevents_evolution TINYINT(1) DEFAULT 0,
            prerequisite_build VARCHAR(191),
            target_palette TEXT,
            dark_palette TEXT,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    """)

    # Add columns if they don't exist (for existing installs)
    run_sql("ALTER TABLE alt_builds ADD COLUMN IF NOT EXISTS dex_number SMALLINT UNSIGNED AFTER species;")
    run_sql("ALTER TABLE alt_builds ADD COLUMN IF NOT EXISTS target_palette TEXT AFTER prevents_evolution;")
    run_sql("ALTER TABLE alt_builds ADD COLUMN IF NOT EXISTS dark_palette TEXT AFTER target_palette;")

    upserted = 0
    for b in builds:
        moves_json = escape(json.dumps(b['key_moves']))
        target_json = escape(json.dumps(b['target_palette']))
        dark_json = escape(json.dumps(b['dark_palette']))
        dex = b['dex_number'] if b['dex_number'] else 'NULL'

        sql = f"""
            INSERT INTO alt_builds (
                build_id, name, species, dex_number, champion, `rank`,
                type1, type2, stat_focus,
                ability1, ability2, ability3, passive_ability,
                key_moves, prevents_evolution, prerequisite_build,
                target_palette, dark_palette,
                created_at, updated_at
            ) VALUES (
                '{escape(b["build_id"])}', '{escape(b["name"])}',
                '{escape(b["species"])}', {dex}, '{escape(b["champion"])}', {b["rank"]},
                '{escape(b["type1"])}', '{escape(b["type2"])}', '{escape(b["stat_focus"])}',
                '{escape(b["ability1"])}', '{escape(b["ability2"])}', '{escape(b["ability3"])}',
                '{escape(b["passive_ability"])}',
                '{moves_json}', {1 if b["prevents_evolution"] else 0},
                '{escape(b["prerequisite_build"])}',
                '{target_json}', '{dark_json}',
                NOW(), NOW()
            )
            ON DUPLICATE KEY UPDATE
                name=VALUES(name), species=VALUES(species),
                dex_number=VALUES(dex_number),
                champion=VALUES(champion), `rank`=VALUES(`rank`),
                type1=VALUES(type1), type2=VALUES(type2),
                stat_focus=VALUES(stat_focus),
                ability1=VALUES(ability1), ability2=VALUES(ability2), ability3=VALUES(ability3),
                passive_ability=VALUES(passive_ability),
                key_moves=VALUES(key_moves),
                prevents_evolution=VALUES(prevents_evolution),
                prerequisite_build=VALUES(prerequisite_build),
                target_palette=VALUES(target_palette),
                dark_palette=VALUES(dark_palette),
                updated_at=NOW();
        """
        result = run_sql(sql)
        if result.returncode == 0:
            types = f" [{b['type1']}/{b['type2']}]" if b['type1'] else ''
            palette_info = f" palette={b['target_palette'][0]}" if b['target_palette'] else ''
            print(f"  OK: {b['species']} — {b['name']}{types}{palette_info}")
            upserted += 1
        else:
            print(f"  ERROR: {b['build_id']}")

    print(f"\nDone! Upserted {upserted} alt builds.")

if __name__ == '__main__':
    main()
