#!/usr/bin/env python3
"""Extract embedded atlas JSON from a pokevoid PNG sprite sheet."""
import sys, json, base64, zlib
from PIL import Image

img = Image.open(sys.argv[1])
b64 = img.info.get('jsonData', '')
if not b64:
    sys.exit(1)
data = zlib.decompress(base64.b64decode(b64))
print(data.decode('utf-8'))
