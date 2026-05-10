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

POKEVOID       = '/var/www/void.scooom.com/pokevoid/src'
ALT_BUILD_TS   = f'{POKEVOID}/data/pokemon-alt-buid.ts'

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
    if s is None: return ''
    return str(s).replace("\\", "\\\\").replace("'", "\\'").replace("\n", "\\n").replace("\r", "")

# ── Name humanisers ────────────────────────────────────────────────────────
def enum_to_name(val):
    """Convert ENUM_CONSTANT to Title Case."""
    return val.replace('_', ' ').title()

def build_id_to_name(build_id):
    """ONIX_CRYSTAL_LEVIATHAN -> Crystal Leviathan (strip species prefix)."""
    # Remove first word (species name) from the build id
    parts = build_id.split('_')
    # Find where the species ends — usually 1-2 words
    # Strategy: the build enum starts with species name, rest is build name
    # We'll strip known species prefixes by matching against the species field
    return None  # Will be set from species field below

def strip_species_prefix(build_id, species_name):
    """Remove species name prefix from build ID to get the build name."""
    species_words = species_name.upper().replace(' ', '_').replace('-', '_')
    if build_id.startswith(species_words + '_'):
        remainder = build_id[len(species_words)+1:]
        return enum_to_name(remainder)
    # Try just first word
    first_word = build_id.split('_')[0]
    if species_name.upper().startswith(first_word):
        remainder = '_'.join(build_id.split('_')[1:])
        return enum_to_name(remainder)
    return enum_to_name(build_id)

# ── Type mapping (matches PokemonService) ─────────────────────────────────
TYPE_MAP = {
    'NORMAL': 'Normal', 'FIGHTING': 'Fighting', 'FLYING': 'Flying',
    'POISON': 'Poison', 'GROUND': 'Ground', 'ROCK': 'Rock',
    'BUG': 'Bug', 'GHOST': 'Ghost', 'STEEL': 'Steel', 'FIRE': 'Fire',
    'WATER': 'Water', 'GRASS': 'Grass', 'ELECTRIC': 'Electric',
    'PSYCHIC': 'Psychic', 'ICE': 'Ice', 'DRAGON': 'Dragon',
    'DARK': 'Dark', 'FAIRY': 'Fairy', 'UNKNOWN': None,
}

# ── Champion mapping ───────────────────────────────────────────────────────
# Map build IDs to champion based on prerequisite or naming convention
CHAMPION_BUILD_MAP = {
    # Brock
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
    # Misty
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
    # Apollo/Diana
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

def parse_builds(content):
    builds = re.findall(
        r'\[PokemonAltBuildId\.(\w+)\]:\s*\{(.*?)\},\s*(?=\n\s*\[|\n\})',
        content, re.DOTALL
    )

    results = []
    for build_id, body in builds:
        # Skip rank 0 (signature placeholders)
        rank_match = re.search(r'rank:\s*(\d+)', body)
        rank = int(rank_match.group(1)) if rank_match else 0
        if rank < 1:
            continue

        # Species
        species_match = re.search(r'species:\s*Species\.(\w+)', body)
        species = enum_to_name(species_match.group(1)) if species_match else ''

        # Build name (strip species prefix)
        name = strip_species_prefix(build_id, species)

        # Types
        type_match = re.search(r'typeChanges:\s*\[Type\.(\w+)(?:,\s*Type\.(\w+))?\]', body)
        type1 = TYPE_MAP.get(type_match.group(1)) if type_match else None
        type2 = TYPE_MAP.get(type_match.group(2)) if type_match and type_match.group(2) else None

        # Stat focus
        stat_match = re.search(r'statFocus:\s*\[(.*?)\]', body)
        if stat_match:
            stats = re.findall(r'Stat\.(\w+)', stat_match.group(1))
            stat_labels = {'HP': 'HP', 'ATK': 'ATK', 'DEF': 'DEF',
                          'SPATK': 'SP.ATK', 'SPDEF': 'SP.DEF', 'SPD': 'SPD'}
            stat_focus = ' / '.join(stat_labels.get(s, s) for s in stats)
        else:
            stat_focus = ''

        # Abilities
        ab_match = re.search(r'abilityChanges:\s*\[(.*?)\]', body, re.DOTALL)
        abilities = []
        if ab_match:
            ab_raw = re.findall(r'Abilities\.(\w+)', ab_match.group(1))
            abilities = [enum_to_name(a) for a in ab_raw if a != 'undefined']

        # Passive ability
        passive_match = re.search(r'passiveAbilityChange:\s*Abilities\.(\w+)', body)
        passive = enum_to_name(passive_match.group(1)) if passive_match else None

        # Key moves (just a sample — take every ~5th level or notable ones)
        move_matches = re.findall(r'(\d+):\s*Moves\.(\w+)', body)
        # Pick a representative subset: first 3, some middle, last 3
        all_moves = [(int(lvl), enum_to_name(mv)) for lvl, mv in move_matches]
        if len(all_moves) > 8:
            key = all_moves[:3] + all_moves[len(all_moves)//2-1:len(all_moves)//2+1] + all_moves[-3:]
        else:
            key = all_moves
        key_moves = [f"Lv{lvl}: {mv}" for lvl, mv in key]

        # Prevents evolution
        prevents_evo = 'preventEvolution: true' in body

        # Prerequisite build
        prereq_match = re.search(r'prerequisiteBuilds:\s*\[PokemonAltBuildId\.(\w+)\]', body)
        prereq = prereq_match.group(1).lower() if prereq_match else None

        # Champion
        champion = CHAMPION_BUILD_MAP.get(build_id)

        results.append({
            'build_id':          build_id.lower(),
            'name':              name,
            'species':           species,
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
        })

    return results

def main():
    print("Parsing alt builds...")
    with open(ALT_BUILD_TS) as f:
        content = f.read()

    builds = parse_builds(content)
    print(f"Found {len(builds)} named alt builds (rank >= 1)")

    run_sql("""
        CREATE TABLE IF NOT EXISTS alt_builds (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            build_id VARCHAR(191) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            species VARCHAR(100),
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
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    """)

    upserted = 0
    for b in builds:
        moves_json = escape(json.dumps(b['key_moves']))
        sql = f"""
            INSERT INTO alt_builds (
                build_id, name, species, champion, `rank`,
                type1, type2, stat_focus,
                ability1, ability2, ability3, passive_ability,
                key_moves, prevents_evolution, prerequisite_build,
                created_at, updated_at
            ) VALUES (
                '{escape(b["build_id"])}', '{escape(b["name"])}',
                '{escape(b["species"])}', '{escape(b["champion"])}', {b["rank"]},
                '{escape(b["type1"])}', '{escape(b["type2"])}', '{escape(b["stat_focus"])}',
                '{escape(b["ability1"])}', '{escape(b["ability2"])}', '{escape(b["ability3"])}',
                '{escape(b["passive_ability"])}',
                '{moves_json}', {1 if b["prevents_evolution"] else 0},
                '{escape(b["prerequisite_build"])}',
                NOW(), NOW()
            )
            ON DUPLICATE KEY UPDATE
                name=VALUES(name), species=VALUES(species),
                champion=VALUES(champion), `rank`=VALUES(`rank`),
                type1=VALUES(type1), type2=VALUES(type2),
                stat_focus=VALUES(stat_focus),
                ability1=VALUES(ability1), ability2=VALUES(ability2), ability3=VALUES(ability3),
                passive_ability=VALUES(passive_ability),
                key_moves=VALUES(key_moves),
                prevents_evolution=VALUES(prevents_evolution),
                prerequisite_build=VALUES(prerequisite_build),
                updated_at=NOW();
        """
        result = run_sql(sql)
        if result.returncode == 0:
            types = f" [{b['type1']}/{b['type2']}]" if b['type1'] else ''
            print(f"  OK: {b['species']} — {b['name']}{types}")
            upserted += 1
        else:
            print(f"  ERROR: {b['build_id']}")

    print(f"\nDone! Upserted {upserted} alt builds.")

if __name__ == '__main__':
    main()
