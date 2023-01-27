## Bitmaps test suite.

- Copyrights: http://entropymine.com/jason/bmpsuite/bmpsuite/html/bmpsuite.html
- Test can be against: https://onlinejpgtools.com/find-dominant-jpg-colors

---

### [1-ppb]:

1 bit/pixel paletted image, in which black is the first color in the palette.

![](../bitmaps_collection/1/pal1.bmp)

1 bit/pixel paletted image, in which white is the first color in the palette.

![](../bitmaps_collection/1/pal1-color-palette.bmp)

---

### [2-ppb]:

A paletted image with 2 bits/pixel. Usually only 1, 4, and 8 are allowed, but 2 is legal on Windows CE.

![](../bitmaps_collection/2/pal2-colored.bmp)

Same as pal2.bmp, but with a color palette instead of grayscale palette.

![](../bitmaps_collection/2/pal2-gray.bmp)

### [4-ppb]:

Paletted image with 12 palette colors, and 4 bits/pixel.

![](../bitmaps_collection/4/pal4-colored.bmp)

Paletted image with 12 grayscale palette colors, and 4 bits/pixel.

![](../bitmaps_collection/4/pal4-gray.bmp)

4-bit image that uses RLE compression.

![](../bitmaps_collection/4/pal4-compressed-rle.bmp)

An RLE-compressed image that uses “delta” codes to skip over some pixels, leaving them undefined. Some viewers make undefined pixels transparent, others make them black, and others assign them palette color 0 (purple, in this case).

![](../bitmaps_collection/4/pal4-rletrns.bmp)

An RLE-compressed image that uses “delta” codes, and early EOL & EOBMP markers, to skip over some pixels. It’s okay if the viewer’s image doesn’t exactly match any of the reference images.

![](../bitmaps_collection/4/pal4-rlecut.bmp)

---

### [8-ppb]:

Our standard paletted image, with 252 palette colors, and 8 bits/pixel.

![](../bitmaps_collection/8/pal8-paletted.bmp)

Every field that can be set to 0 is set to 0: pixels/meter=0; colors used=0 (meaning the default 256); size-of-image=0.

![](../bitmaps_collection/8/pal8-0.bmp)

An 8-bit image with a palette of 252 grayscale colors.

![](../bitmaps_collection/8/pal8-gray.bmp)

