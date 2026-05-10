#!/usr/bin/env python3
"""
render_alt_build_sprite.py
Applies grayscale_overlay recolouring to a Pokémon sprite.
Usage: python3 render_alt_build_sprite.py <src.png> <palette_json> <out.png>

Implements the game's GLSL grayscale_overlay blend mode (mode 3):
  gray = (r+g+b)/3
  result = softLight(gray, targetColor)
  where softLight = mix(1-2*(1-bg)*(1-fg), 2*bg*fg, step(bg, 0.5))
"""
import sys
import json

try:
    from PIL import Image
    import numpy as np
except ImportError:
    sys.exit(1)

def soft_light(bg, fg):
    """Vectorised soft-light blend matching the GLSL shader."""
    dark = 2 * bg * fg
    lite = 1 - 2 * (1 - bg) * (1 - fg)
    return np.where(bg <= 0.5, dark, lite)

def hex_to_rgb(hex_str):
    h = hex_str.lstrip('#')
    return tuple(int(h[i:i+2], 16) for i in (0, 2, 4))

def apply_grayscale_overlay(img, palette_hex):
    """Apply grayscale_overlay recolour using the target palette."""
    img = img.convert('RGBA')
    data = np.array(img, dtype=np.float32) / 255.0

    r, g, b, a = data[...,0], data[...,1], data[...,2], data[...,3]

    # Per-pixel luminance
    lum = (r + g + b) / 3.0

    # Build palette as float array
    palette = [tuple(c/255.0 for c in hex_to_rgb(h)) for h in palette_hex]
    n = len(palette)

    # Map luminance → palette index
    idx = np.clip((lum * n).astype(int), 0, n - 1)

    # Build target colour arrays
    tr = np.zeros_like(lum)
    tg = np.zeros_like(lum)
    tb = np.zeros_like(lum)
    for i, (pr, pg, pb) in enumerate(palette):
        mask = idx == i
        tr[mask] = pr
        tg[mask] = pg
        tb[mask] = pb

    # Apply soft-light blend: grayscale bg × target colour fg
    out_r = soft_light(lum, tr)
    out_g = soft_light(lum, tg)
    out_b = soft_light(lum, tb)

    result = np.stack([out_r, out_g, out_b, a], axis=-1)
    result = np.clip(result * 255, 0, 255).astype(np.uint8)
    return Image.fromarray(result, 'RGBA')

def main():
    if len(sys.argv) < 4:
        print("Usage: render_alt_build_sprite.py <src> <palette_json> <out>")
        sys.exit(1)

    src_path     = sys.argv[1]
    palette_json = sys.argv[2]
    out_path     = sys.argv[3]

    palette = json.loads(palette_json)
    if not palette:
        sys.exit(1)

    img = Image.open(src_path)
    result = apply_grayscale_overlay(img, palette)

    # Output at 256×256 for Discord
    result = result.resize((256, 256), Image.NEAREST)
    result.save(out_path, 'PNG')
    print(f"Saved: {out_path}")

if __name__ == '__main__':
    main()
