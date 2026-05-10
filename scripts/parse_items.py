#!/usr/bin/env python3
"""
parse_items.py
Parses PokeVoid's modifier-type.ts and modifier-type.json to extract all
items with their names, descriptions, tiers, pool membership, and spawn conditions.
Upserts into the game_items table.
"""
import re
import json
import subprocess
import sys

# ── Paths ──────────────────────────────────────────────────────────────────
POKEVOID           = '/var/www/void.scooom.com/pokevoid/src'
MODIFIER_TYPE_TS   = f'{POKEVOID}/modifier/modifier-type.ts'
MODIFIER_TYPE_JSON = f'{POKEVOID}/locales/en/modifier-type.json'
PERMA_MODIFIERS_TS = f'{POKEVOID}/modifier/perma-modifiers.ts'

# ── DB ─────────────────────────────────────────────────────────────────────
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
    if not s:
        return ''
    return s.replace("\\", "\\\\").replace("'", "\\'").replace("\n", "\\n").replace("\r", "")

# ── Condition patterns → human-readable text ───────────────────────────────
# Each tuple: (regex to match against the lambda body, human-readable label)
CONDITION_PATTERNS = [
    (r'shouldHideHealingModifier',          'Hidden in Nuzlight/no-heal modes'),
    (r'isFainted\(\)',                       'Only when a Pokémon has fainted'),
    (r'p\.isFainted\(\).*length.*Math\.ceil.*party\.length\s*/\s*2',
                                             'Only when half or more of party is fainted'),
    (r'p\.isFainted\(\)',                    'Only when a Pokémon has fainted'),
    (r'p\.status',                           'Only when a Pokémon has a status condition'),
    (r'getInverseHp|getHpRatio',             'Only when a Pokémon is injured'),
    (r'hasMaximumBalls|pokeballCounts',      'Only if below max ball count'),
    (r'getLearnableLevelMoves',              'Only when a Pokémon has forgotten moves'),
    (r'isSplicedOnly',                       'Not available in Spliced Endless mode'),
    (r'isDaily',                             'Not available in Daily Run mode'),
    (r'isFreshStartChallenge',               'Not available in Fresh Start challenge'),
    (r'isEndless|isSplicedOnly',             'Not available in Endless modes'),
    (r'Unlockables\.MINI_BLACK_HOLE',        'Requires The Void unlock'),
    (r'Unlockables\.THE_VOID_OVERTAKEN',     'Requires The Void Overtaken unlock'),
    (r'Unlockables\.EVIOLITE',               'Requires Eviolite unlock'),
    (r'NIGHTMARE_MODE',                      'Nightmare Mode only'),
    (r'glitchPieceWeight|GlitchPieceMod',    'Requires Glitch Pieces'),
    (r'glitchUnlockWeight',                  'Requires Glitch unlock'),
    (r'glitchChaosGauntlet',                 'Higher weight in Chaos/Gauntlet modes'),
    (r'isChaosMode',                         'Higher weight in Chaos modes'),
    (r'fusionSpecies.*length.*>\s*1|isSplicedOnly.*false',
                                             'Only with unfused Pokémon in party'),
    (r'pokemonEvolutions',                   'Only for Pokémon that can still evolve'),
    (r'checkedSpecies.*FARFETCHD',           'Only with Farfetch\'d or Sirfetch\'d'),
    (r'checkedAbilities.*QUICK_FEET|GUTS.*TOXIC_BOOST',
                                             'Only with compatible ability/move'),
    (r'checkedAbilities.*FLAME|FLARE_BOOST', 'Only with compatible ability/move'),
    (r'waveIndex.*<.*90|waveIndex.*<.*100',  'Only before wave 90'),
    (r'skipInLastClassicWave',               'Not available in the final Classic wave'),
    (r'rerollCount',                         'Chance decreases with each reroll'),
    (r'totalTypeBalls.*>=.*30',              'Only if fewer than 30 type balls held'),
    (r'championData.*type1',                 'Requires active Champion with a type'),
    (r'randSeedInt',                         'Random chance each battle'),
    (r'party\.length.*>\s*1',               'Requires 2+ Pokémon in party'),
    (r'glitchSacrificeWeight',               'Requires Glitch Pieces; party size > 1'),
    (r'glitchPiecePermaMid',                 'Requires mid-tier Glitch Pieces'),
    (r'skillTreeModifierContext',            'Skill Tree context only'),
]

def extract_condition(lambda_body):
    """Given the lambda/weight function body, return a human-readable condition string."""
    if not lambda_body:
        return None
    # Return 0 check = truly conditional
    if 'return 0' not in lambda_body and '? 0 :' not in lambda_body and ': 0,' not in lambda_body:
        # Has a lambda but never returns 0 — just a weight adjustment, not truly conditional
        for pattern, label in CONDITION_PATTERNS:
            if re.search(pattern, lambda_body):
                return label
        return None

    for pattern, label in CONDITION_PATTERNS:
        if re.search(pattern, lambda_body):
            return label

    return 'Conditional'

# ── Load i18n names + descriptions ────────────────────────────────────────
def load_i18n():
    with open(MODIFIER_TYPE_JSON) as f:
        data = json.load(f)
    types = data.get('ModifierType', {})
    perma = data.get('modifierType', {}).get('permaModifiers', {})

    result = {}

    for k, v in types.items():
        if not isinstance(v, dict):
            continue
        name = re.sub(r'\{\{[^}]+\}\}', '…', v.get('name', '')).strip('+ ').strip()
        desc = re.sub(r'\{\{[^}]+\}\}', '…', v.get('description', ''))
        result[k] = {'name': name, 'description': desc}

    for k, v in data.items():
        if isinstance(v, dict) and 'name' in v:
            name = re.sub(r'\{\{[^}]+\}\}', '…', v.get('name', '')).strip()
            desc = re.sub(r'\{\{[^}]+\}\}', '…', v.get('description', ''))
            result[k] = {'name': name, 'description': desc}

    for k, v in perma.items():
        if isinstance(v, dict):
            name = re.sub(r'\{\{[^}]+\}\}', '…', v.get('name', '')).strip()
            desc = re.sub(r'\{\{[^}]+\}\}', '…', v.get('description', ''))
            result[k] = {'name': name, 'description': desc, '_omega': True}

    return result

# ── Parse modifier pools from .ts ─────────────────────────────────────────
def parse_pools(content):
    """
    Returns dict: {
        'KEY': {
            'tiers': set(),
            'pools': set(),
            'conditional': bool,
            'condition_text': str|None
        }
    }
    """
    items = {}

    pool_indicators = {
        'playerModifierPool': 'player',
        'trainerModifierPool': 'trainer',
        'enemyModifierPool': 'enemy',
        'wildModifierPool': 'wild',
        'shopModifierPool': 'shop',
    }

    current_tier = None
    current_pool = 'player'

    # We need to extract WeightedModifierType(...) calls with their full lambda bodies
    # Process line by line for tier/pool context, then use a separate regex for lambdas
    lines = content.splitlines()

    for i, line in enumerate(lines):
        for indicator, pool_name in pool_indicators.items():
            if indicator in line:
                current_pool = pool_name

        tier_match = re.search(r'\[ModifierTier\.(\w+)\]', line)
        if tier_match:
            current_tier = tier_match.group(1)

        item_match = re.search(r'modifierTypes\.(\w+)', line)
        if item_match and current_tier:
            key = item_match.group(1)

            if key not in items:
                items[key] = {
                    'tiers': set(),
                    'pools': set(),
                    'conditional': False,
                    'condition_text': None,
                }

            items[key]['tiers'].add(current_tier)
            items[key]['pools'].add(current_pool)

            # Extract lambda body for this WeightedModifierType line
            # Grab up to 8 lines of context to capture multi-line lambdas
            context = '\n'.join(lines[i:i+8])
            lambda_match = re.search(
                r'modifierTypes\.' + re.escape(key) + r'\s*,\s*'
                r'(\(party[^)]*\)\s*(?::\s*\w+)?\s*=>?\s*\{?[^,\]]{0,500})',
                context, re.DOTALL
            )

            lambda_body = lambda_match.group(1) if lambda_match else ''

            # Also check inline arrow: , (party) => expr, weight
            if not lambda_body:
                inline = re.search(
                    r'modifierTypes\.' + re.escape(key) + r'\s*,\s*'
                    r'(\([^)]*party[^)]*\)\s*=>\s*[^,\n]+)',
                    context
                )
                lambda_body = inline.group(1) if inline else ''

            if lambda_body:
                items[key]['conditional'] = True
                cond = extract_condition(lambda_body)
                # Keep the most specific condition we've seen
                if cond and not items[key]['condition_text']:
                    items[key]['condition_text'] = cond

    return items

# ── Parse PermaType enum ───────────────────────────────────────────────────
def parse_perma_types(content):
    match = re.search(r'export enum PermaType \{(.*?)\}', content, re.DOTALL)
    if not match:
        return []
    return [name.strip().rstrip(',') for name in match.group(1).splitlines()
            if name.strip() and not name.strip().startswith('//')]

def key_to_name(key):
    return key.replace('_', ' ').title()

# ── Main ───────────────────────────────────────────────────────────────────
def main():
    print("Loading i18n data...")
    i18n = load_i18n()

    print("Parsing modifier-type.ts pools...")
    with open(MODIFIER_TYPE_TS) as f:
        ts_content = f.read()

    pool_data = parse_pools(ts_content)

    print("Parsing perma modifier types...")
    with open(PERMA_MODIFIERS_TS) as f:
        perma_content = f.read()
    perma_keys = parse_perma_types(perma_content)

    print(f"Found {len(pool_data)} items in pools, {len(perma_keys)} omega items")

    run_sql("""
        CREATE TABLE IF NOT EXISTS game_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `key` VARCHAR(191) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            tier VARCHAR(50) NOT NULL DEFAULT 'COMMON',
            pool VARCHAR(50) NOT NULL DEFAULT 'player',
            conditional TINYINT(1) NOT NULL DEFAULT 0,
            spawn_condition VARCHAR(500) NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    """)

    # Add column if it doesn't exist yet (for existing installs)
    run_sql("""
        ALTER TABLE game_items
        ADD COLUMN IF NOT EXISTS spawn_condition VARCHAR(500) NULL AFTER conditional;
    """)

    upserted = 0
    skipped  = 0

    for key, data in pool_data.items():
        tier_order = ['MEH', 'COMMON', 'GREAT', 'ULTRA', 'ROGUE', 'MASTER', 'LUXURY']
        tiers = data['tiers']
        tier = max(tiers, key=lambda t: tier_order.index(t) if t in tier_order else -1)
        pool = ','.join(sorted(data['pools']))
        conditional = 1 if data['conditional'] else 0
        condition_text = data.get('condition_text') or ''

        info = (i18n.get(key) or
                i18n.get(key + 'ModifierType') or
                i18n.get(key.replace('_', '').title() + 'ModifierType') or
                {})

        name = info.get('name') or key_to_name(key)
        desc = info.get('description', '')

        if not name or key.endswith('_QUEST') or key.startswith('ENEMY_'):
            skipped += 1
            continue

        sql = f"""
            INSERT INTO game_items (`key`, name, description, tier, pool, conditional, spawn_condition, created_at, updated_at)
            VALUES ('{escape(key)}', '{escape(name)}', '{escape(desc)}', '{tier}', '{escape(pool)}',
                    {conditional}, '{escape(condition_text)}', NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                name=VALUES(name), description=VALUES(description),
                tier=VALUES(tier), pool=VALUES(pool),
                conditional=VALUES(conditional),
                spawn_condition=VALUES(spawn_condition),
                updated_at=NOW();
        """
        result = run_sql(sql)
        if result.returncode == 0:
            cond_str = f' [{condition_text}]' if condition_text else ''
            print(f"  OK: {key} [{tier}]{cond_str} — {name}")
            upserted += 1
        else:
            print(f"  ERROR: {key}")
            skipped += 1

    for key in perma_keys:
        info = i18n.get(key, {})
        name = info.get('name') or key_to_name(key)
        desc = info.get('description', '')

        if not name:
            skipped += 1
            continue

        sql = f"""
            INSERT INTO game_items (`key`, name, description, tier, pool, conditional, spawn_condition, created_at, updated_at)
            VALUES ('{escape(key)}', '{escape(name)}', '{escape(desc)}', 'OMEGA', 'omega', 0, NULL, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                name=VALUES(name), description=VALUES(description),
                tier='OMEGA', pool='omega', updated_at=NOW();
        """
        result = run_sql(sql)
        if result.returncode == 0:
            print(f"  OK: {key} [OMEGA] — {name}")
            upserted += 1
        else:
            print(f"  ERROR: {key}")
            skipped += 1

    print(f"\nDone! Upserted: {upserted}, Skipped: {skipped}")

if __name__ == '__main__':
    main()
