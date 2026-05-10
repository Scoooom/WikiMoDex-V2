#!/usr/bin/env python3
"""
render_alt_build_sprite.py
Extracts the first animation frame from a pokevoid embedded-atlas sprite sheet,
then applies grayscale_overlay recolouring.

Usage: python3 render_alt_build_sprite.py <src.png> <palette_json> <out.png>
"""
import sys
import json
import base64
import zlib

try:
    from PIL import Image
    import numpy as np
except ImportError:
    sys.exit(1)

def extract_first_frame(img):
    """Extract the first frame using the embedded atlas JSON."""
    info = img.info
    compressed_b64 = info.get('jsonData', '')

    if not compressed_b64:
        # No atlas — return as-is (already a single sprite)
        return img.convert('RGBA')

    try:
        decompressed = zlib.decompress(base64.b64decode(compressed_b64))
        atlas = json.loads(decompressed)
        frames = atlas['textures'][0]['frames']
    except Exception:
        return img.convert('RGBA')

    if not frames:
        return img.convert('RGBA')

    rgba = img.convert('RGBA')
    f = frames[0]
    fx, fy = f['frame']['x'], f['frame']['y']
    fw, fh = f['frame']['w'], f['frame']['h']
    sx, sy = f['spriteSourceSize']['x'], f['spriteSourceSize']['y']
    sw, sh = f['sourceSize']['w'], f['sourceSize']['h']

    # Crop frame from sheet and place on full-size canvas
    frame_crop = rgba.crop((fx, fy, fx + fw, fy + fh))
    canvas = Image.new('RGBA', (sw, sh), (0, 0, 0, 0))
    canvas.paste(frame_crop, (sx, sy))
    return canvas

def soft_light(bg, fg):
    dark = 2 * bg * fg
    lite = 1 - 2 * (1 - bg) * (1 - fg)
    return np.where(bg <= 0.5, dark, lite)

def hex_to_rgb(hex_str):
    h = hex_str.lstrip('#')
    return tuple(int(h[i:i+2], 16) for i in (0, 2, 4))

def apply_grayscale_overlay(img, palette_hex):
    data = np.array(img, dtype=np.float32) / 255.0
    r, g, b, a = data[...,0], data[...,1], data[...,2], data[...,3]
    lum = (r + g + b) / 3.0

    palette = [tuple(c/255.0 for c in hex_to_rgb(h)) for h in palette_hex]
    n = len(palette)
    idx = np.clip((lum * n).astype(int), 0, n - 1)

    tr = np.zeros_like(lum)
    tg = np.zeros_like(lum)
    tb = np.zeros_like(lum)
    for i, (pr, pg, pb) in enumerate(palette):
        mask = idx == i
        tr[mask] = pr
        tg[mask] = pg
        tb[mask] = pb

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
    frame = extract_first_frame(img)
    result = apply_grayscale_overlay(frame, palette)

    # Scale up to fill 256×256, preserving aspect ratio with nearest-neighbor
    w, h = result.size
    scale = min(256 / w, 256 / h)
    new_w = max(1, int(w * scale))
    new_h = max(1, int(h * scale))
    result = result.resize((new_w, new_h), Image.NEAREST)

    # Center on canvas
    canvas = Image.new('RGBA', (256, 256), (0, 0, 0, 0))
    x = (256 - new_w) // 2
    y = (256 - new_h) // 2
    canvas.paste(result, (x, y))
    canvas.save(out_path, 'PNG')
    print(f"Saved: {out_path} ({result.width}x{result.height} frame)")

if __name__ == '__main__':
    main()
