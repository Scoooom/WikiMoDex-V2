#!/usr/bin/env python3
"""
parse_moves.py
Parses pokevoid's moves enum + move.json locale and upserts into core_moves table.
"""
import re
import json
import subprocess
import sys

POKEVOID   = '/var/www/void.scooom.com/pokevoid/src'
MOVES_ENUM = f'{POKEVOID}/enums/moves.ts'
MOVE_JSON  = f'{POKEVOID}/locales/en/move.json'

DB_NAME = 'pokevoid'
DB_USER = 'void'
DB_PASS = '827uh6aV8VI7F50D30BF'

SMITTY_KEYS = {'SMITTY_NUGGETS', 'NUGGET_OF_SMITTY'}

def run_sql(sql):
    result = subprocess.run(
        ['mariadb', '-u', DB_USER, f'-p{DB_PASS}', DB_NAME],
        input=sql, capture_output=True, text=True
    )
    if result.returncode != 0:
        print(f"SQL ERROR: {result.stderr.strip()}", file=sys.stderr)
    return result

def escape(s):
    return str(s).replace("\\", "\\\\").replace("'", "\\'")

def to_camel(name):
    parts = name.lower().split('_')
    return parts[0] + ''.join(p.capitalize() for p in parts[1:])

def main():
    print("Loading moves enum...")
    with open(MOVES_ENUM) as f:
        content = f.read()

    names = re.findall(r'^\s+([A-Z][A-Z0-9_]+)', content, re.MULTILINE)
    names = [n for n in names if n != 'NONE']
    print(f"  {len(names)} moves found in enum")

    print("Loading move locale...")
    with open(MOVE_JSON) as f:
        locale = json.load(f)

    inserted = 0
    skipped = 0
    for move_key in names:
        camel = to_camel(move_key)
        info = locale.get(camel)
        if not info:
            skipped += 1
            continue

        name = info.get('name', move_key.replace('_', ' ').title())
        is_smitty = 1 if move_key in SMITTY_KEYS else 0

        sql = f"""
INSERT INTO core_moves (move_key, name, is_smitty, created_at, updated_at)
VALUES ('{escape(move_key)}', '{escape(name)}', {is_smitty}, NOW(), NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name), is_smitty=VALUES(is_smitty), updated_at=NOW();
"""
        run_sql(sql)
        inserted += 1

    print(f"Done! {inserted} moves upserted, {skipped} skipped (no locale entry).")

if __name__ == '__main__':
    main()
