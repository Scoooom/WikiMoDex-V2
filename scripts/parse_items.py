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

import os
from dotenv import load_dotenv


dir_path = os.path.dirname(os.path.realpath(__file__)) + "/../"

load_dotenv(dir_path+ "/.env")

# DB config
DB_NAME = os.getenv("DB_DATABASE")
DB_USER = os.getenv("DB_USERNAME")
DB_PASS = os.getenv("DB_PASSWORD")



# ── Paths ──────────────────────────────────────────────────────────────────
POKEVOID = dir_path + '/pokevoid/src'
MODIFIER_TYPE_TS   = f'{POKEVOID}/modifier/modifier-type.ts'
MODIFIER_TYPE_JSON = f'{POKEVOID}/locales/en/modifier-type.json'
PERMA_MODIFIERS_TS = f'{POKEVOID}/modifier/perma-modifiers.ts'


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
        # Also index nested dicts (e.g. PermaModifierType contains PERMA_* keys)
        for inner_k, inner_v in v.items():
            if isinstance(inner_v, dict) and ('name' in inner_v or 'description' in inner_v):
                inner_name = re.sub(r'\{\{[^}]+\}\}', '…', inner_v.get('name', '')).strip()
                inner_desc = re.sub(r'\{\{[^}]+\}\}', '…', inner_v.get('description', ''))
                result[inner_k] = {'name': inner_name, 'description': inner_desc}

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

# ── Fallback descriptions for items with no JSON description ──────────────
FALLBACK_DESCRIPTIONS = {
    'ATTACK_TYPE_BOOSTER':  'Increases the power of a Pokémon\'s moves of a specific type by 20%.',
    'DNA_SPLICERS':         'Fuse two Pokémon together, combining their Ability, base stats, types, and move pools.',
    'GRIP_CLAW':            'Extends the duration of binding and trapping moves.',
    'LUCKY_EGG':            'Increases the holder\'s EXP gain per battle.',
    'MULTI_LENS':           'Attacks hit one additional time at reduced power per stack.',
    'MINI_BLACK_HOLE':      'Every turn, the holder acquires one held item from the opposing Pokémon.',
    'PP_UP':                'Permanently increases the PP of one move by up to 3 for every 5 maximum PP.',
    'PP_MAX':               'Maximises the PP of one Pokémon\'s move to its highest possible value.',
    'NUGGET':               'Sell for a small amount of money.',
    'BIG_NUGGET':           'Grants a large amount of money when picked up.',
    'RELIC_GOLD':           'Grants a very large amount of money when picked up.',
    'COIN_CASE':            'After every 10th battle, receive 10% of your current money as interest.',
    'LURE':                 'Increases the encounter rate of wild Pokémon for a set number of steps.',
    'SUPER_LURE':           'Greatly increases the encounter rate of wild Pokémon.',
    'MAX_LURE':             'Maximises the encounter rate of wild Pokémon.',
    'SOOTHE_BELL':          'Increases the rate at which a Pokémon\'s friendship grows.',
    'EXP_CHARM':            'Moderately increases EXP gain for all party members.',
    'SUPER_EXP_CHARM':      'Greatly increases EXP gain for all party members.',
    'GOLDEN_EGG':           'Significantly increases EXP gain for all party members.',
    'GOLDEN_EXP_CHARM':     'Maximises EXP gain for all party members.',
    'LOCK_CAPSULE':         'Allows you to lock the rarity tier of items when rerolling.',
    'ETHER':                'Restores 10 PP of one move for one Pokémon.',
    'MAX_ETHER':            'Fully restores the PP of one move for one Pokémon.',
    'ELIXIR':               'Restores 10 PP of all moves for one Pokémon.',
    'MAX_ELIXIR':           'Fully restores the PP of all moves for one Pokémon.',
    'POTION':               'Restores 20 HP for one Pokémon.',
    'SUPER_POTION':         'Restores 50 HP or 25% HP for one Pokémon, whichever is higher.',
    'HYPER_POTION':         'Restores 200 HP or 50% HP for one Pokémon, whichever is higher.',
    'MAX_POTION':           'Fully restores HP for one Pokémon.',
    'FULL_RESTORE':         'Fully restores HP and cures all status conditions for one Pokémon.',
    'FULL_HEAL':            'Cures all status conditions for one Pokémon.',
    'REVIVE':               'Revives one fainted Pokémon and restores 50% of its HP.',
    'MAX_REVIVE':           'Revives one fainted Pokémon and fully restores its HP.',
    'SACRED_ASH':           'Revives all fainted Pokémon and fully restores their HP.',
    'ABILITY_CHARM':        'Dramatically increases the chance of a wild Pokémon having its Hidden Ability.',
    'GOLDEN_POKEBALL':      'Adds 1 extra item option at the end of every battle.',
    'RARE_CANDY':           'Increases a Pokémon\'s level by 1.',
    'RARER_CANDY':          'Increases all party members\' level by 1.',
    'VOUCHER':              'Redeem at the Egg Gacha for a standard egg.',
    'PLAYER_BASE_STAT_BOOSTER': 'Increases the holder\'s base stat by 1%.',
    'SELECTABLE_PMONEY_1':  'Choose a small ΩGOLD reward.',
    'SELECTABLE_PMONEY_2':  'Choose a medium ΩGOLD reward.',
    'SELECTABLE_PMONEY_4OR5': 'Choose a large ΩGOLD reward.',
    'TEMP_STAT_BOOSTER':    'Raises one stat for all party members by 1 stage for 5 battles.',
    'MOVE_UPGRADE':         'Randomly upgrades one of your party\'s moves — power, priority, effect, chance, multi-hit, and more.',
    'SKILL_POINTS':         'Gain Skill Points to unlock nodes in your Champion\'s Skill Tree.',
    'SKILL_TOKENS':         'Gain Skill Tree Tokens to level up your Champion\'s Skill Tree.',
    'MOVE_SACRIFICE':       'Release a Pokémon to have another inherit its moves.',
    'ABILITY_SACRIFICE':    'Release a Pokémon to have another adopt its ability.',
    'PASSIVE_ABILITY_SACRIFICE': 'Release a Pokémon to assign its primary ability as a passive ability for another.',
    'TYPE_SACRIFICE':       'Release a Pokémon to have another inherit its types.',
    'STAT_SACRIFICE':       'Release a Pokémon to boost another Pokémon\'s chosen stat by 15%.',
    'PLAYER_BASE_STAT_BOOSTER': 'Increases the holder\'s base stat by 1%.',
    'ADD_POKEMON':          'Adds a random Pokémon to your party.',
    'DRAFT_POKEMON':        'Adds a drafted Pokémon to your party.',
    'COLLECTED_TYPE':       'Use 4 Essences instead of releasing a Pokémon for Release Items, or exchange with the Collector.',
    'LOW_TIER_MOVE_UPGRADE':'Applies a lower-tier upgrade to one of your party\'s moves.',
    'EVOLUTION_ITEM':       'Causes certain Pokémon to evolve.',
    'RARE_EVOLUTION_ITEM':  'Causes certain Pokémon to evolve via rare evolution methods.',
    'FORM_CHANGE_ITEM':     'Causes certain Pokémon to change form.',
    'SMITTY_FORM_CHANGE_ITEM': 'A forbidden combination of 4 Smitty Items that transforms a Pokémon into a Smitty Form.',
    'STAT_SWITCHER':        'Swap two of a Pokémon\'s base stats (e.g. ATK ↔ DEF). Costs 1 Glitch Piece.',
    'TYPE_SWITCHER':        'Changes a Pokémon\'s primary and secondary types. Costs 1 Glitch Piece.',
    'PRIMARY_TYPE_SWITCHER':'Changes a Pokémon\'s primary type. Costs 1 Glitch Piece.',
    'SECONDARY_TYPE_SWITCHER':'Changes a Pokémon\'s secondary type. Costs 1 Glitch Piece.',
    'ANYTM_MEH':            'Teach a low-tier move to any Pokémon, ignoring compatibility.',
    'ANYTM_COMMON':         'Teach a Common-tier move to any Pokémon, ignoring compatibility.',
    'ANYTM_GREAT':          'Teach a Great-tier move to any Pokémon, ignoring compatibility.',
    'ANYTM_ULTRA':          'Teach an Ultra-tier move to any Pokémon, ignoring compatibility.',
    'ANYTM_MASTER':         'Teach a Master-tier move to any Pokémon, ignoring compatibility.',
    'ANYTM_LUXURY':         'Teach a Luxury-tier move to any Pokémon, ignoring compatibility.',
    'ANY_ABILITY':          'Give any ability to any Pokémon. Costs 1 Glitch Piece.',
    'ANY_SMITTY_ABILITY':   'Give a Smitty-exclusive ability to any Pokémon. Costs 1 Glitch Piece.',
    'ANY_PASSIVE_ABILITY':  'Set any ability as a passive on any Pokémon. Costs 1 Glitch Piece.',
    'ANY_SMITTY_PASSIVE_ABILITY': 'Set a Smitty-exclusive ability as a passive on any Pokémon. Costs 1 Glitch Piece.',
    'ULTRA_BALL':       'A Poké Ball with a higher catch rate than a Great Ball. Only appears if you have fewer than 10.',
    'ROGUE_BALL':       'A high-performance Poké Ball with an even better catch rate than an Ultra Ball.',
    'MASTER_BALL':      'The ultimate Poké Ball — catches any wild Pokémon without fail. Only appears if you have fewer than 5.',
    'VOID_BALL':        'A mysterious Poké Ball attuned to the Void. Has unique catch properties.',
    'VOUCHER':          'Redeem at the Egg Gacha machine for a standard egg.',
    'VOUCHER_PLUS':     'Redeem at the Egg Gacha machine for an enhanced egg with better hatch odds.',
    'VOUCHER_PREMIUM':  'Redeem at the Egg Gacha machine for a premium egg — the highest tier available.',
    'EXP_SHARE':        'Non-active party members receive 20% of a single participant\'s EXP gain.',
    'EXP_BALANCE':      'Weighs EXP distribution toward lower-level party members to help them catch up.',
    'BASE_STAT_BOOSTER':    'Boosts a random base stat of one Pokémon.',
    'BERRY':                'A random berry that provides its effect when the holder\'s HP drops low.',
    'MINT':                 'Changes a Pokémon\'s nature, affecting which stats are boosted and reduced.',
    'SELECTABLE_PMONEY_3':  'Choose a medium-large ΩGOLD reward.',
    'SELECTABLE_PMONEY_4':  'Choose a large ΩGOLD reward.',
    'SPECIES_STAT_BOOSTER': 'Boosts a species-specific stat for compatible Pokémon (e.g. Thick Club for Marowak, Leek for Farfetch\'d).',
    'TERA_SHARD':           'Terastallizes the holder into a specific type for up to 25 battles (35 with Tera Orb equipped).',
}

def key_to_name(key):
    return key.replace('_', ' ').title()

def get_item_info(key, i18n, key_to_class_map):
    """Get name and description with multiple fallback strategies."""
    info = i18n.get(key, {})
    name = info.get('name', '')
    desc = info.get('description', '')

    # Strip template vars
    name = re.sub(r'\{\{[^}]+\}\}', '…', name).strip()
    desc = re.sub(r'\{\{[^}]+\}\}', '…', desc)

    cls = key_to_class_map.get(key, '')

    # Strategy 2: class name lookup
    if not desc and cls:
        cls_info = i18n.get(cls, {})
        desc = re.sub(r'\{\{[^}]+\}\}', '…', cls_info.get('description', ''))

    # Strategy 3: strip 'Generator' suffix from class name
    if not desc and cls and cls.endswith('Generator'):
        base_cls = cls[:-len('Generator')]
        base_info = i18n.get(base_cls, {})
        desc = re.sub(r'\{\{[^}]+\}\}', '…', base_info.get('description', ''))

    # Strategy 4: hardcoded fallback
    if not desc:
        desc = FALLBACK_DESCRIPTIONS.get(key, '')

    # Fix names that are misleading internal labels (e.g. 'Cheap') — use key as name
    BAD_NAMES = {'Cheap', 'ModifierType', ''}
    if not name or name in BAD_NAMES:
        name = key_to_name(key)

    return name, desc

# ── Main ───────────────────────────────────────────────────────────────────
def main():
    print("Loading i18n data...")
    i18n = load_i18n()

    print("Parsing modifier-type.ts pools...")
    with open(MODIFIER_TYPE_TS) as f:
        ts_content = f.read()

    # Build key -> class map from modifierTypes object
    key_to_class_map = dict(re.findall(
        r'^\s{4}(\w+):\s*\(\)\s*=>\s*new\s+(\w+)', ts_content, re.MULTILINE
    ))

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

        name, desc = get_item_info(key, i18n, key_to_class_map)

        # Overwrite garbled descriptions containing unreplaced template placeholders
        if not desc or ('?' in desc and 'Receive' in desc):
            desc = FALLBACK_DESCRIPTIONS.get(key, desc)

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
        name, desc = get_item_info(key, i18n, key_to_class_map)

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
