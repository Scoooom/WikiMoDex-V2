#!/usr/bin/env python3
"""
parse_items.py
Parses PokeVoid's modifier-type.ts and modifier-type.json to extract all
items with their names, descriptions, tiers, and pool membership.
Upserts into the game_items table.
"""
import re
import json
import subprocess
import sys

# ── Paths ──────────────────────────────────────────────────────────────────
POKEVOID          = '/var/www/void.scooom.com/pokevoid/src'
MODIFIER_TYPE_TS  = f'{POKEVOID}/modifier/modifier-type.ts'
MODIFIER_TYPE_JSON= f'{POKEVOID}/locales/en/modifier-type.json'
MODIFIER_TIER_TS  = f'{POKEVOID}/modifier/modifier-tier.ts'
PERMA_MODIFIERS_TS= f'{POKEVOID}/modifier/perma-modifiers.ts'

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

# ── Load i18n names + descriptions ────────────────────────────────────────
def load_i18n():
    with open(MODIFIER_TYPE_JSON) as f:
        data = json.load(f)
    types = data.get('ModifierType', {})
    perma = data.get('modifierType', {}).get('permaModifiers', {})

    result = {}

    # Main ModifierType entries
    for k, v in types.items():
        if not isinstance(v, dict):
            continue
        name = v.get('name', '')
        desc = v.get('description', '')
        # Strip template placeholders for display
        name = re.sub(r'\{\{[^}]+\}\}', '…', name).strip('+ ').strip()
        desc = re.sub(r'\{\{[^}]+\}\}', '…', desc)
        result[k] = {'name': name, 'description': desc}

    # Flat keys at top level (e.g. RARE_CANDY, MOVE_UPGRADE, SKILL_POINTS)
    for k, v in data.items():
        if isinstance(v, dict) and 'name' in v:
            name = re.sub(r'\{\{[^}]+\}\}', '…', v.get('name', '')).strip()
            desc = re.sub(r'\{\{[^}]+\}\}', '…', v.get('description', ''))
            result[k] = {'name': name, 'description': desc}

    # Perma modifier entries
    for k, v in perma.items():
        if isinstance(v, dict):
            name = re.sub(r'\{\{[^}]+\}\}', '…', v.get('name', '')).strip()
            desc = re.sub(r'\{\{[^}]+\}\}', '…', v.get('description', ''))
            result[k] = {'name': name, 'description': desc, '_omega': True}

    return result

# ── Parse modifier pools from .ts ─────────────────────────────────────────
def parse_pools(content):
    """
    Returns dict: { 'KEY': {'tiers': set(), 'pools': set(), 'conditional': bool} }
    Scans the player/trainer/wild modifier pool blocks.
    """
    items = {}

    # Find all pool blocks: [ModifierTier.XXXX]: [ ... ].map(...)
    tier_blocks = re.findall(
        r'\[ModifierTier\.(\w+)\]:\s*\[(.*?)\]\.map\(',
        content, re.DOTALL
    )

    # Detect which pool section we're in by finding pool type headers
    # Split content into sections by looking for export const / modifierPool identifiers
    pool_sections = re.split(
        r'(export\s+const\s+\w*[Mm]odifier[Pp]ool\w*|playerModifierPool|trainerModifierPool|enemyModifierPool|wildModifierPool)',
        content
    )

    # Simpler approach: scan all WeightedModifierType occurrences with context
    # We'll use line-by-line analysis to track current tier and pool
    current_tier = None
    current_pool = 'player'
    pool_indicators = {
        'playerModifierPool': 'player',
        'trainerModifierPool': 'trainer',
        'enemyModifierPool': 'enemy',
        'wildModifierPool': 'wild',
        'shopModifierPool': 'shop',
    }

    for line in content.splitlines():
        # Detect pool context
        for indicator, pool_name in pool_indicators.items():
            if indicator in line:
                current_pool = pool_name

        # Detect tier context
        tier_match = re.search(r'\[ModifierTier\.(\w+)\]', line)
        if tier_match:
            current_tier = tier_match.group(1)

        # Detect items
        item_match = re.search(r'modifierTypes\.(\w+)', line)
        if item_match and current_tier:
            key = item_match.group(1)

            # Skip generator functions and helpers
            if key in ('ANYTM_MEH', 'ANYTM_COMMON', 'ANYTM_GREAT', 'ANYTM_ULTRA',
                       'ANYTM_MASTER', 'ANYTM_LUXURY', 'TM_COMMON', 'TM_GREAT', 'TM_ULTRA'):
                # These are TM generators — keep but mark tier
                pass

            if key not in items:
                items[key] = {'tiers': set(), 'pools': set(), 'conditional': False}

            items[key]['tiers'].add(current_tier)
            items[key]['pools'].add(current_pool)

            # Conditional = has a lambda weight function (not just a plain number)
            # Detect by checking if the line has a closure/arrow function before the weight
            if re.search(r'party\s*=>', line) or re.search(r'\(party:', line):
                items[key]['conditional'] = True

    return items

# ── Parse PermaType enum ───────────────────────────────────────────────────
def parse_perma_types(content):
    match = re.search(r'export enum PermaType \{(.*?)\}', content, re.DOTALL)
    if not match:
        return []
    return [name.strip().rstrip(',') for name in match.group(1).splitlines()
            if name.strip() and not name.strip().startswith('//')]

# ── Human-readable fallback name from key ─────────────────────────────────
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

    # Also extract all modifierTypes keys from the modifierTypes object
    # to catch items that might not appear in pools directly
    all_type_keys = re.findall(r'^\s{4}(\w+):\s*\(\)', ts_content, re.MULTILINE)

    print(f"Found {len(pool_data)} items in pools, {len(all_type_keys)} total modifier keys, {len(perma_keys)} omega items")

    # Ensure table exists (migration should handle this, but just in case)
    run_sql("""
        CREATE TABLE IF NOT EXISTS game_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `key` VARCHAR(191) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            tier VARCHAR(50) NOT NULL DEFAULT 'COMMON',
            pool VARCHAR(50) NOT NULL DEFAULT 'player',
            conditional TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    """)

    upserted = 0
    skipped  = 0

    # Process pool items
    for key, data in pool_data.items():
        # Pick the highest tier the item appears in
        tier_order = ['MEH', 'COMMON', 'GREAT', 'ULTRA', 'ROGUE', 'MASTER', 'LUXURY']
        tiers = data['tiers']
        tier = max(tiers, key=lambda t: tier_order.index(t) if t in tier_order else -1)
        pool = ','.join(sorted(data['pools']))
        conditional = 1 if data['conditional'] else 0

        # Get i18n data — try several key formats
        info = (i18n.get(key) or
                i18n.get(key + 'ModifierType') or
                i18n.get(key.replace('_', '').title() + 'ModifierType') or
                {})

        name = info.get('name') or key_to_name(key)
        desc = info.get('description', '')

        # Skip empty/internal keys
        if not name or key.endswith('_QUEST') or key.startswith('ENEMY_'):
            skipped += 1
            continue

        sql = f"""
            INSERT INTO game_items (`key`, name, description, tier, pool, conditional, created_at, updated_at)
            VALUES ('{escape(key)}', '{escape(name)}', '{escape(desc)}', '{tier}', '{escape(pool)}', {conditional}, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                name=VALUES(name), description=VALUES(description),
                tier=VALUES(tier), pool=VALUES(pool),
                conditional=VALUES(conditional), updated_at=NOW();
        """
        result = run_sql(sql)
        if result.returncode == 0:
            print(f"  OK: {key} [{tier}] — {name}")
            upserted += 1
        else:
            print(f"  ERROR: {key}")
            skipped += 1

    # Process omega/perma items
    for key in perma_keys:
        info = i18n.get(key, {})
        name = info.get('name') or key_to_name(key)
        desc = info.get('description', '')

        if not name:
            skipped += 1
            continue

        sql = f"""
            INSERT INTO game_items (`key`, name, description, tier, pool, conditional, created_at, updated_at)
            VALUES ('{escape(key)}', '{escape(name)}', '{escape(desc)}', 'OMEGA', 'omega', 0, NOW(), NOW())
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
