#!/usr/bin/env python3
"""
extract_item_icons.py
Extracts individual item icon PNGs from pokevoid's items.png and smitems_32.png atlases.
Output: storage/app/item-icons/{filename}.png
"""
import json, sys
from pathlib import Path
from PIL import Image

POKEVOID    = Path('/var/www/void.scooom.com/pokevoid')
OUT_DIR     = Path('/var/www/void.scooom.com/storage/app/item-icons')
OUT_DIR.mkdir(parents=True, exist_ok=True)

ATLASES = [
    (POKEVOID / 'public/images/items.png',          POKEVOID / 'public/images/items.json'),
    (POKEVOID / 'public/images/olditems.png',       None),   # no atlas — skip
    (POKEVOID / 'src/notes/smitems_32.png',         POKEVOID / 'src/notes/smitems_32.json'),
]

def extract_atlas(img_path: Path, atlas_path: Path):
    if not img_path.exists():
        print(f"  SKIP (missing): {img_path}", file=sys.stderr)
        return 0

    sheet = Image.open(img_path).convert('RGBA')

    with open(atlas_path) as f:
        atlas = json.load(f)

    frames = atlas['textures'][0]['frames']
    count = 0
    for frame in frames:
        name = frame['filename']
        f    = frame['frame']
        x, y, w, h = f['x'], f['y'], f['w'], f['h']
        icon = sheet.crop((x, y, x + w, y + h))
        out  = OUT_DIR / f'{name}.png'
        icon.save(out, 'PNG')
        count += 1
    return count

total = 0
for img_path, atlas_path in ATLASES:
    if atlas_path is None or not atlas_path.exists():
        continue
    print(f"Extracting {img_path.name}...")
    n = extract_atlas(img_path, atlas_path)
    print(f"  {n} icons extracted")
    total += n

print(f"\nDone! {total} icons in {OUT_DIR}")
