# Development research of _Image Dominant Colors_ - Started at 2023-01-22 17:00.
### Starting research by asking questions to GPT3:

> Notes: The functions that introduced in that research may not exist in the final version of the project.


- TODO:
    - __JPG__ Support.
    - Sensitivity control (merge familiar colors).

__Resources__:
- Here are images that can be used as tests:
    - https://github.com/sagefarrenholz/Blimp/tree/master/tests/test_images
    - https://people.math.sc.edu/Burkardt/data/bmp/bmp.html
    - https://eeweb.engineering.nyu.edu/~yao/EL5123/SampleData.html
    - http://entropymine.com/jason/bmpsuite/bmpsuite/html/bmpsuite.html

- Research:
  - https://medium.com/sysf/bits-to-bitmaps-a-simple-walkthrough-of-bmp-image-format-765dc6857393
  - https://gibberlings3.github.io/iesdp/file_formats/ie_formats/bmp.htm
  - https://www.cis.rit.edu/people/faculty/pelz/courses/SIMG203/res.pdf

- RLE Compression
  - https://github.com/JenniferSorensen/decompress_bmp_RLE8
  - https://wiki.multimedia.cx/index.php/Microsoft_RLE
  - https://github.dev/mike42/gfx-php/blob/ed9ded2a9298e4084a9c557ab74a89b71e43dbdb/src/Mike42/GfxPhp/Codec/Bmp/Rle8Decoder.php#L35

- Source of truth:
 - https://onlinejpgtools.com/find-dominant-jpg-colors - Used to test the project.

---

- Me: What is the difference between __bmp__, __jpg__ and __png__?
- GPT: __BMP__, __JPG__, and __PNG__ are all different image file formats. 
  - __BMP__ (Bitmap) is a standard image file format on Windows, it supports lossless compression but files tend to be quite large.
  - __JPG__ (Joint Photographic Experts Group) is a commonly used image format for photographs, it uses lossy compression which means that some image data is lost when the file is compressed, but it results in much smaller file sizes.
  - __PNG__ (Portable Network Graphics) is a lossless image format that is often used for graphics and images with transparent backgrounds. __PNG__ files are generally larger in size than __JPG__ files but offer better quality.

---

- Me: What are the difference in the data?
- GPT: The main difference in the data between __BMP__, __JPG__, and __PNG__ is the way the data is compressed. 
  - __BMP__ (Bitmap) is a standard image file format on Windows, it does not use any compression, so the data is stored in the file exactly as it appears in the image.
  
    This means that __BMP__ files tend to be quite large, and not efficient for storage or transfer.
  
  - __JPG__ (Joint Photographic Experts Group) uses lossy compression. 
  
    This means that some image data is lost when the file is compressed, but it results in much smaller file sizes. __JPG__ uses a technique called `discrete cosine transform` __(DCT__) which compresses the image by discarding some of the less important visual information.
  
    This is efficient for photographs, where there is a lot of redundant information and where the human eye is less sensitive to small details. 
  - __PNG__ (Portable Network Graphics) uses lossless compression.
  
    This means that no image data is lost when the file is compressed. __PNG__ uses a technique called `DEFLATE` which compresses the image by removing repetitive patterns of data.
  
    This is efficient for graphics and images with transparent backgrounds, where it is important to maintain all of the visual information in the image.

In summary, __BMP__ files are the largest and have no loss in image data, __JPG__ uses lossy compression to make smaller file size but loss some of the image data and __PNG__ uses lossless compression to maintain all the image data but larger file size.

---

- Me: What is the structure of __BMP__ image data?
- GPT: __BMP__ (Bitmap) is a standard image file format on Windows, it has a specific structure for storing image data. The structure of a __BMP__ file includes a header, the pixel array, and an optional color palette.

  - The header of a __BMP__ file contains a number of fields that describe the properties of the image, such as the file size, the dimensions of the image, the number of bits per pixel, and the compression method used (if any).

  - The pixel array is the actual image data, it contains the values for each pixel in the image. The pixel array is often stored in a contiguous block of memory, with each pixel represented by one or more bytes. The order of the pixels in the array is typically from left to right and from top to bottom.

  - The color palette is an optional part of a __BMP__ file, it contains a list of colors that are used in the image. The color palette is only used when the image uses a limited number of colors, such as with indexed color images.

  - The file format is simple and easy to understand, but not efficient for storage or transfer. The size of the file is directly proportional to the number of pixels in the image and cannot be compressed. However, it is still widely used in Windows ecosystem, such as creating and editing images in paint, saving screenshots, etc.

---

- Me: me: can you give me __BMP__ structure in __C__?
- GPT: Sure, here's an example of the structure of a __BMP__ file in __C__:

  - 
    ```c
      #pragma pack(1)
    
      typedef struct {
          unsigned short bfType;
          unsigned int bfSize;
          unsigned short bfReserved1;
          unsigned short bfReserved2;
          unsigned int bfOffBits;
      } BMPFILEHEADER;
    
      typedef struct {
          unsigned int biSize;
          int biWidth;
          int biHeight;
          unsigned short biPlanes;
          unsigned short biBitCount;
          unsigned int biCompression;
          unsigned int biSizeImage;
          int biXPelsPerMeter;
          int biYPelsPerMeter;
          unsigned int biClrUsed;
          unsigned int biClrImportant;
      } BMPINFOHEADER;
      ```
    
- The __BMPFILEHEADER__ structure contains information about the file itself, such as the type of file and the size of the file. The __BMPINFOHEADER__ structure contains information about the image, such as the dimensions of the image, the number of bits per pixel, and the compression method used.

  - __bfType__ field contains the signature of the file, which must be set to the hexadecimal value of `0x4D42`.

  - __bfSize__ field contains the size of the __BMP__ file in bytes.

  - __bfReserved1__ and __bfReserved2__ fields are reserved, they should be set to `0`.

  - __bfOffBits__ field contains the offset, in bytes, from the beginning of the file to the bitmap data.

  - __biSize__ field of __BMPINFOHEADER__ contains the size of the __BMPINFOHEADER__ structure, in bytes.

  - __biWidth__ and __biHeight__ fields contain the width and height of the image, in pixels.

  - __biPlanes__ field contains the number of color planes in the image. It should be set to `1`.

  - __biBitCount__ field contains the number of bits per pixel. It can be set to `1`, `4,` `8`, `16`, `24`, or `32`.

  - __biCompression__ field contains the compression method used. It can be set to `0` __(BI_RGB)__ for uncompressed data or `1` __(BI_RLE8)__ for __8-bit__ RLE compression.

  - __biSizeImage__ field contains the size of the image data, in bytes.

  - __biXPelsPerMeter__ and biYPelsPerMeter fields contain the horizontal and vertical resolution of the image, in pixels per meter.

  - __biClrUsed__ field contains the number of colors in the color palette, or `0` if the image is a _truecolor_ image.

  - __biClrImportant__ field contains the number of important colors used in the image, or `0` if all colors are important.
    > Please note that the above structure is for a standard __BMP__ file, some variations of the format may have different structures or fields.
    
---

- Me: If __biBitCount__ is `1` it will be only black and white?
- GPT: Yes, that's correct. If the __biBitCount__ field in the __BMPINFOHEADER__ structure is set to `1`, it means that each pixel in the image is represented by `1` bit, which can only have two possible values, `0` or `1`.

This means that the image can only have two colors, typically black and white, one color for `0` and another for `1`. 

This format is called `Monochrome` and it's used for 1-bit deep images, it's also called `Indexed` because the colors are stored in a color table.

It's worth noting that, even though the image will only have two colors, the color table can still have entries for more colors, but they will not be used. 

Also, the color table should have two entries for the two colors used in the image.

---

- Me: Can you give me the structure of __JPG__ in __C__?
- GPT: __JPG__ (Joint Photographic Experts Group) is a standard image format that uses lossy compression, which means that some image data is lost when the file is compressed, but it results in much smaller file sizes.

    It's worth noting that __JPG__ is not a single file format, but rather a family of file formats that share the same basic structure and compression method. 


   - The structure of a __JPG__ file is based on blocks of data called `segments`.
  
     Each segment has a specific purpose, such as describing the image's dimensions, specifying the compression method used, or storing the actual image data.

   - The structure of a __JPG__ file is not a fixed one, it's not like the __BMP__ format that has a specific structure, it's based on a series of markers that indicate the start of each segment, followed by the data for that segment.
   
      Because of this, it's not possible to provide a single `structure of JPG file in __C__` that describes the format.


__JPG__ files are typically read and written using libraries such as __libjpeg__, rather than working with the raw file data directly. These libraries provide an API for reading and writing __JPG__ files, and handle the details of the file format for you.

It's worth noting that __JPG__ is a proprietary format, thus the standard is not freely available, and it's not recommended to implement your own __JPG__ encoder/decoder.

---

- Me: If decode __JPG__ to which structure it can be applied?
- GPT: When you decode a __JPG__ image, you are essentially taking the compressed image data stored in the file and converting it back into a representation that can be used and manipulated by your application.

    The structure of the decoded image data depends on the format that you choose to use in your application.


Typically, the decoded image data is stored in a 2-dimensional array of pixels, where each element in the array represents the color of a single pixel in the image.

Each element of the array can be represented by a struct or a class that contains information about the color such as __RGB__ or __HSL__ values.

The most common format for storing decoded image data is the __RGB__ format, where each pixel is represented by __3 bytes__, one for the __red__ color, one for the __green__ color and one for the __blue__ color. 

This format is easy to manipulate and understand, it's also the most common format used in image processing libraries.

Another common format is the __RGBA__ format, where each pixel is represented by __4 bytes__, one for __red__, one for __green__, one for __blue__, and one for __alpha (transparency)__.

There are other formats that can be used, such as __grayscale__, __YCbCr__, and __HSL__, but they are less common and it depends on the application.

---

- Me: Can you give me __RGB__ structure in __C__?
- GPT: Sure, here's an example of the RGB structure in __C__:
    ```c
    struct RGB {
        unsigned char red;
        unsigned char green;
        unsigned char blue;
    };
    ```
    In this structure, each color channel (__red__, __green__ and __blue__) is represented by an __8-bit unsigned char__, allowing for a range of values from `0` to `255`.

    You can use this structure in a __2D__ array to represent the pixels in an image, for example:
    ```c
    const int width = 100;
    const int height = 100;
    struct RGB image[width][height];
    ```
    
    Each element in the array represents a single pixel in the image, and the __red__, __green__, and __blue__ fields of the structure hold the color values for that pixel.
    
    You can also create a class instead of a struct, or add more functionality to the struct, like methods to set and get the values of the color channels, or even methods to convert the image format to another format.
    
    You can also use it to create a __1D__ array of pixels, and use the width and height of the image to access to a specific pixel.
    ```c
    const int width = 100;
    const int height = 100;
    struct RGB image[width*height];
    ```
    and to access the pixel at x = 2, y = 3 you can use ```image[2 + 3*width]```
    > Please note that this is a basic example, and you may need to adjust the structure and array sizes based on the specific requirements of your application.

---

- Me: What the range of the bits used in RGB?
- GPT: The range of values for each color channel in an RGB format depends on the number of bits used to represent each channel.

  - When __8__ bits (1 byte) are used for each channel, the range of values for each channel is `0` to `255`, where `0` represents the minimum (no color) and `255` represents the maximum (full intensity). This is the most common configuration for the __RGB__ format.

  - When __16__ bits (2 bytes) are used for each channel, the range of values for each channel is `0` to `65535`, where `0` represents the minimum (no color) and `65535` represents the maximum (full intensity). This configuration provides more color depth but also increases the size of the data.

  - When __32__ bits (4 bytes) are used for each channel, the range of values for each channel is `0` to `4294967295`, where `0` represents the minimum (no color) and `4294967295` represents the maximum (full intensity). This configuration provides even more color depth but also increases the size of the data even more.

    
It's worth noting that some file formats like `OpenEXR` uses __32__ bits per channel, this format is typically used in high dynamic range image and video processing, where the image has a much higher range of brightness and color than what can be represented by 8 or 16 bits per channel.

---

- Me: How to determine how many bytes are used?
- GPT: To determine how many bytes are used to represent each color channel in an __RGB__ image, you need to know the bit depth of the image.

    The bit depth refers to the number of bits used to represent each color channel.
    
    It's also known as color depth or __bit-per-pixel (bpp)__. For example, an image with a bit depth of __8 bits__ per channel uses __8 bits__ to represent each of the __red__, __green__, and __blue__ color channels, resulting in __24 bits__ per pixel.

    To determine the number of bytes used to represent each color channel in an __RGB__ image, you can divide the bit depth by `8` (since there are __8 bits__ in a byte).

For example, if the bit depth of an image __is 8 bits__ per channel, you can calculate the number of bytes used per channel as follows:
  - `8 bits / 8 bits per byte = 1 byte per channel`

Another example, if the bit depth of an image is 16 bits per channel, you can calculate the number of bytes used per channel as follows: 
  - `16 bits / 8 bits per byte = 2 bytes per channel`

---

At this point I started search in google for C code that generate an BMP images.

Found this: https://engineering.purdue.edu/ece264/16au/hw/HW13

Not what I was looking for, but it gave me some insights.

---

Now I'm not sure about the format I will use, __JPG__ or __bmp__, I'll try to search more about it, how to decode the __jpg__ and read it the 2D array.

I came to the conclusion that I will use the __bitmap__ format, since I'm creating software that determine the dominant colors of an image, and the __bitmap__ files generally have a higher quality than __JPEGs__.

In a __BMP__ image, each pixel has its own specific color.

---

I created my first image __RGBA__ __32 bit__ image:

![32-bit-image](./../bitmaps_collection/32/rgba_32.bmp)

--- 

- Me: Can you give me more information about __bfOffBits__ ?
- GPT: __bfOffBits__ is a field in the __BMP__ file header __(BITMAPFILEHEADER)__ that specifies the offset, in bytes, from the beginning of the file to the beginning of the bitmap data.


In other words, it tells you at which position in the file the actual image data begins. 

The image data is stored in the file after the headers that contain information about the image, such as the size of the image, the resolution, and the color depth.

The __bfOffBits__ field tells you where the image data starts so that you know where to begin reading the pixel data.

In the example I provided, the __bfOffBits__ field is set to the size of the file header plus the size of the information header, which is __sizeof(BMPFILEHEADER)__ + __sizeof(BMPINFOHEADER)__.

This is because the image data follows immediately after the headers in the file.

It's worth noting that the __bfOffBits__ field is not the only offset used in __BMP__ format, there are other offsets used by the file format to store the palette or the color masks.

This information can be found in the __DIB__ header __(BITMAPINFOHEADER)__

It's also worth noting that __BMP__ format is not the only image format that uses this concept of `offset` to locate the image data within the file.

--- 

- Me: What is __biPlanes__?
- GPT: __biPlanes__ is a field in the __BMP__ information header __(BITMAPINFOHEADER)__ that specifies the number of color planes in the image.

In the case of __BMP__ format, the value of this field is always set to `1`, this field is not used in the __BMP__ format and its purpose is mostly historical.

In the past, it was used in some other image formats.

It's worth noting that the __BMP__ format has several versions, and depending on the version the __DIB__ header __(BITMAPINFOHEADER)__ may have different fields, some versions use __biPlanes__ field and others don't, some versions use the field to indicate the number of planes, other versions use this field to indicate the number of bits per pixel.

It's also worth noting that the __BMP__ format is not the only image format that uses this concept of color planes.

Other image formats like __TIFF__ and __PCX__ uses color planes to store the image data.

--- 

- Me: What is __biClrUsed__?
- GPT: __biClrUsed__ is a field in the __BMP___ information header __(BITMAPINFOHEADER)__ that specifies the number of colors in the color palette of the image.

The color palette is a table that contains the colors used in the image, it's used when the image has a limited number of colors, such as an image with `1`, `4`, `8` bits-per-pixel.

In the example I provided, the field __biClrUsed__ is set to `0`, which indicates that all colors are used.

But in cases where the image has a limited number of colors, this field should be set to the number of colors in the palette.

It's worth noting that this field is not used when the image has __24-bit per pixel__ or more, since in this case the image uses true color and doesn't have a color palette.

It's also worth noting that the __BMP__ format has several versions, and depending on the version the __DIB__ header __(BITMAPINFOHEADER)__ may have different fields, some versions use __biClrUsed__ and others don't.

---

- Me: What is biClrImportant?
- GPT: __biClrImportant__ is a field in the __BMP__ information header __(BITMAPINFOHEADER)__ that specifies the number of color indices considered important for displaying the image.
   This field is optional and is not always present in the BMP file format.

  - When the value of this field is `zero`, it means that all colors are considered important for displaying the image.
  - When the value of this field is `non-zero`, it indicates the number of color indices considered important for displaying the image. 

--- 

- Me: What about __biXPelsPerMeter__, __biYPelsPerMeter__?
- GPT: __biXPelsPerMeter__ and __biYPelsPerMeter__ are fields in the BMP information header __(BITMAPINFOHEADER)__ that specify the horizontal and vertical resolution of the image, respectively.

These fields indicate the number of pixels per meter in the image.
They are used to indicate the physical dimensions of the image, and are mainly used for printing purposes.

--- 

Ok now I want to take a look at `rgba_32.bmp` file I created using hex editor...

I want to see the Red, Green, Blue in the file.

I opened the file in hex editor and I found the 4 bytes that are repeating them self:
- `FF 33 04 FF`
- `00 F9 00 FF`
- `00 26 FF FF`

But I didn't understand how they relate to the color, I'm missing something?

After thinking and thinking I came to the conclusion that I don't understand hex values in the file as it should be...

I wished to see the hex values for each color in `rgb.bmp` file using Color Picker
```
- Red:      FF 29 18       dec: 255 41 24
- Green:    00 F7 3C       dec: 0 247 60 
- Blue:     00 3B F9       dec: 0 59 249
```

After reading more I found that __RGBA__ colors are stored differently, so i need __RGBA__ converter to test it:

Using this https://rgbacolorpicker.com/hex-to-rgba converter I got the right colors.

- `FF3304FF` - `red`
- `00F900FF` - `green`
- `0026FFFF` - `blue`

---

Yesterday I saw some video of __BMP__ creation that were using something called _PaddingAmount_.

I was curious and asked GPT...

- Me: What are padding in __BMP__?
- GPT: In the __BMP__ file format, padding refers to the extra bytes that may be added to the end of each row of image data in order to align the data on a __4-byte__ boundary.

- This is necessary because the __BMP__ format stores image data in a bottom-up, left-to-right order and requires that each row of pixels be aligned on a __4-byte__ boundary.

The number of padding bytes required for a given row of pixels can be calculated by taking the number of bytes required for the row and rounding up to the nearest multiple of `4`.

For example, if the image has a width of `100` pixels and each pixel is `3` bytes __(24 bits)__ in size, the number of bytes required for the row would be `300` __(100 pixels * 3 bytes per pixel)__.

Since `300` is not a multiple of `4`, `2` padding bytes would be added to the end of the row to make the total number of bytes `302`, which is a multiple of `4`.

The padding bytes are typically filled with zeroes, but they may contain any value. 

When reading a BMP file, it's important to take the padding bytes into account in order to correctly interpret the image data.

---

- Me: Can you give me example?
- GPT: Sure! Here's an example of how padding bytes are added to a __BMP__ file in C:

```c
#include <stdio.h>
#include <stdlib.h>

#define WIDTH 100
#define HEIGHT 100

int main() {
    // Allocate memory for image data
    unsigned char *image = malloc(WIDTH * HEIGHT * 3); // 3 bytes per pixel (RGB)

    // Fill image data with some values
    for (int y = 0; y < HEIGHT; y++) {
        for (int x = 0; x < WIDTH; x++) {
            image[y * WIDTH * 3 + x * 3 + 0] = x % 256; // red
            image[y * WIDTH * 3 + x * 3 + 1] = y % 256; // green
            image[y * WIDTH * 3 + x * 3 + 2] = 0;       // blue
        }
    }

    // Calculate padding
    int padding = (4 - (WIDTH * 3) % 4) % 4;

    // Open file for writing
    FILE *file = fopen("image.bmp", "wb");

    // Write BMP headers
    // ...

    // Write image data
    for (int y = 0; y < HEIGHT; y++) {
        // Write row of pixels
        fwrite(image + y * WIDTH * 3, 1, WIDTH * 3, file);

        // Write padding
        for (int I = 0; I < padding; i++) {
            fputc(0, file);
        }
    }

    // Close file
    fclose(file);

    // Free memory
    free(image);

    return 0;
```

In this example, the image data is stored in an array of unsigned char, with __3 bytes per pixel__ (one byte for __red__, one byte for __green__, and one byte for __blue__).
The image data is filled with some arbitrary values for demonstration purposes.

The padding variable is calculated based on the width of the image, which is `100` pixels, and the number of bytes per pixel, which is `3`.
The formula used to calculate the padding is `(4 - (WIDTH * 3) % 4) % 4`, which ensures that the total number of bytes per row is a multiple of `4`.

When writing the image data to the file, the program writes one row of pixels at a time, followed by the required number of padding bytes.
The padding bytes are filled with zeroes, but as I mentioned before, they can contain any value.

It's worth noting that in this example I didn't include the __BMP__ header information since it was not the main focus of the question, if you would like to know more about it, please let me know.

---

- Me: Does all BMP files have padding?
- GPT: Most __BMP__ files include padding bytes to align each row of image data on __a 4-byte__ boundary as I explained before. However, not all __BMP__ files require padding.

--- 

- Created `run.bash` file to run development php server
- Created public folder to store all public files:
    - Created `backend.php` where will be the logic to analyze the `BMP` file.
    - Created `index.html` where will be the form to upload the `BMP` file.

---

I want to proceed with the `backend.php` file, I thinking on the beginning of the code requirements:
- Check if post & file is set.
- Check if file is `BMP`.
- Read the header using `fread` function, then use `fseek` to the end of the file to determine the pixels sizes.

---

- Added `fetch` from frontend to backend and some simple error mechanism.
- Now I'll check for image header to ensuring im covering only supported formats.
    - It will be determined by the header of the file.

--- 

- I want get `biBitCount` from the header.
    - Searching for header analyzer in the internet...
        - found this https://www.metadata2go.com/ - I will use to check my php functions.
---

- Me: how to get __biBitCount__ using PHP?
  GPT: You can use the __fread__ function in PHP to read the binary data of a __BMP__ file and extract the __biBitCount__ field from it.

The __biBitCountv__ field is located at offset `28` in the __BMP__ file's header, and it's a `2` bytes (WORD) value.

Here's an example of how you can use the __fread__ function to get the __biBitCount__ field of a BMP image:
```php
<?php
$image_path = 'path/to/image.bmp';

$file = fopen($image_path, 'rb');

fseek($file, 28); 

$biBitCount = unpack('v', fread($file, 2))[1];

fclose($file);
```

I got the right value for __biBitCount__ in my `backend.php` file.

---

At this point I already know occurrences count for my `rgba.bmp` using this method:

```php
function get_bmp_file_statistics( $fileInfo ) {
	$result = [
		'success' => false,
		'message' => 'Failed to get BMP file statistics',
	];

	$pixelLength = RGB_LENGTH;

	switch ( $fileInfo['biBitCount'] ) {
		case '32':
			$pixelLength = RGBA_LENGTH;
			break;

		default:
			return [
				'success' => false,
				'message' => 'Unsupported bit count',
			];
	}

	$stack = [];
	$body_as_array = str_split( $fileInfo['body'] );

	for ( $i = 0; $i < $fileInfo['bodyLength']; $i += $pixelLength ) {
		// Get the RGB/A Value.
		$color = strtoupper( bin2hex( implode( "", array_slice( $body_as_array, $i, $pixelLength ) ) ) );

		if ( ! array_key_exists( $color, $stack ) ) {
			$stack[ $color ] = 1;

			continue;
		}

		$stack[ $color ]++;
	}

	if ( ! empty( $stack ) ) {
		$result = [
			'success' => true,
			'message' => 'Successfully got BMP file statistics',
			'stack' => $stack,
		];
	}

	return $result;
}
```

---

Ofc it's not optimized and not complete functions but the base is there.

Now I'm going now test different bitmaps to get some conclusion.

Faced an issue with max file size, etc... So I used custom `php.ini` config for the project

but `xdebug` is not working anymore, I need somehow merge default `php.ini` config with my custom (`custom.php.ini`).

I found solution to merge them:

```shell
#!/bin/bash

PHPSysConfig=`php -i | grep /php.ini | awk '{print $5}'`;

# Copy the `php.ini` from current php cli.
cp -f $PHPSysConfig /tmp/php.ini

# Append `custom.php`.ini into `php.ini`
cat custom.php.ini >> /tmp/php.ini

# Run server with the appended `php.ini`
php -c /tmp/php.ini -S localhost:8000 -t public
```

---

I found a huge bitmap and now im testing it... after testing it few times used `uasort` method to find top occurrence of colors.
I finally got the result.

```php
	$stack = [];
	$total = 0;
	$body_binary = $file_data['body'];

	for ( $i = 0; $i < $file_data['bodyLength']; $i += $pixel_length ) {
		$color = bin2hex( substr( $body_binary, $i, $pixel_length ) );
		$color = $mapping_callback( $color );

		++$total;

		if ( ! array_key_exists( $color, $stack ) ) {
			$stack[ $color ] = 1;

			continue;
		}

		$stack[ $color ]++;
	}

	// Free some memory.
	unset( $file_info );
	unset( $body_binary );

	// Sort by value.
	uasort( $stack, function ( $a, $b ) {
		return $b <=> $a;
	} );

	// Extract `MAX_COLORS_OCCURRENCE` from stack.
	$stack = array_slice( $stack, 0, MAX_COLORS_OCCURRENCE, true );
```

Now I'm thinking need total, and percentage for each color, so im back to the function.

Ok here is the complete function:

```php
function get_bmp_file_statistics( $file_info ): array {
	$result = [
		'success' => false,
		'message' => 'Failed to get BMP file statistics',
	];

	switch ( $file_info['biBitCount'] ) {
		case '24':
			$pixel_length = RGB_LENGTH;
			$mapping_callback = function ( $data ) {
				$r = substr( $data, 4, 2 );
				$g = substr( $data, 2, 2 );
				$b = substr( $data, 0, 2 );

				return $r . $g . $b;
			};
			break;

		case '32':
			$pixel_length = RGBA_LENGTH;
			$mapping_callback = function ( $data ) {
				$r = substr( $data, 4, 2 );
				$g = substr( $data, 2, 2 );
				$b = substr( $data, 0, 2 );
				$a = substr( $data, 6, 2 );

				return $r . $g . $b . $a;
			};
			break;

		default:
			return [
				'success' => false,
				'message' => 'Unsupported bit count',
			];
	}

	$stack = [];
	$total = 0;
	$body_binary = $file_info['body'];

	for ( $i = 0; $i < $file_info['bodyLength']; $i += $pixel_length ) {
		$color = bin2hex( substr( $body_binary, $i, $pixel_length ) );
		$color = $mapping_callback( $color );

		++$total;

		if ( ! array_key_exists( $color, $stack ) ) {
			$stack[ $color ] = 1;

			continue;
		}

		$stack[ $color ]++;
	}

	// Free some memory.
	unset( $file_info );
	unset( $body_binary );

	// Sort by value.
	uasort( $stack, function ( $a, $b ) {
		return $b <=> $a;
	} );

	// Extract `MAX_COLORS_OCCURRENCE` from stack.
	$stack = array_slice( $stack, 0, MAX_COLORS_OCCURRENCE, true );

	// Calculate percentage.
	$percentage = array_map( function ( $value ) use ( $total ) {
		return round( ( $value / $total ) * 100, 2 );
	}, $stack );

	if ( ! empty( $stack ) ) {
		$result = [
			'success' => true,
			'message' => 'Successfully got BMP file statistics',
			'stack' => $stack,
			'total' => $total,
			'percentage' => $percentage,
		];
	}

	return $result;
}
```

---

Adjusting the UI, I think it's good enough for now.

### It is fine when the bitmaps not including much colors

![alt text](img_0.png)

![alt text](img_1.png)

- ### But when the bitmap including a lot of colors, it's not good enough.
    - #### Can be handled with familiar colors merge.

![alt text](img_2.png)

---

After doing few tests with different __biBitCount__'s sizes I came to conclusion that I need favor paddings, 
Since it effects the colors.

So I started to update the `get_supported_file_info` method to get __height__ and __width__ from the file.
> Note: This is first time `get_supported_file_info` introduced to this markdown file.

```php
function get_supported_file_info( $file_handler ) {
	static $supported_types = [
		'424d' => 'bmp',
	];

	static $supported_data = [
		'bmp' => [
			'extension' => 'bmp',
			'mime' => 'image/bmp',
			// --- Custom --- //
			'header' => '',
			'header_length' => 54,
			'body_length' => 0,
			// --- Part of BMP_FILE_HEADER ---
			'bFileSize' => 0,
			// --- Part of BMP_INFO_HEADER ---
			'biWidth' => 0,
			'biHeight' => 0,
			'biBitCount' => 0,
		],
	];

	$result = false;

	$data = fread( $file_handler, 2 ); // TODO: Add `jpg` support.

	if ( $data ) {
		$hex = bin2hex( $data );

		switch ( $supported_types[ $hex ] ) {
			case 'bmp':
				$result = $supported_data['bmp'];

				// Start reading from the beginning.
				fseek( $file_handler, SEEK_SET );

				$result['header'] = fread( $file_handler, $supported_data['bmp']['header_length'] );

				// Get `bfSize` from the header.
				$result['bfSize'] = unpack( 'I', substr( $result['header'], 2, 4 ) )[1]; // I = unsigned integer.

				$result['biWidth'] = unpack( "i", substr( $result['header'], 18, 4 ) )[1]; // i − signed integer.
				$result['biHeight'] = unpack( "i", substr( $result['header'], 22, 4 ) )[1]; // i − signed integer.

				$result['biBitCount'] = unpack( "S", substr( $result['header'], 28, 2 ) )[1]; // S − unsigned short.

				// Determine pixels length.
				$result['body_length'] = $result['bfSize'] - $result['header_length'];

				$body = fread( $file_handler, $result['body_length'] );

				$result['body'] = $body;

				break;
		}
	}

	fclose( $file_handler );

	return $result;
}
```

---

Now I have to use `width` and `height` for looping over the pixels with favor of paddings.

Its require changes in `get_bmp_file_statistics` function.

To understand how to deal with padding I watched this video: https://youtu.be/NcEE5xmpgQ0?t=648

```php
function get_bmp_file_statistics_V2( $file_info ): array {
	$result = [
		'success' => false,
		'message' => 'Failed to get BMP file statistics',
	];

	$time_start = microtime( true );

	$stack = [];
	$total = 0;
	$body_binary = $file_info['body'];
	$height = abs( $file_info['biHeight'] );
	$width = $file_info['biWidth'];

	$padding_amount = get_bmp_padding_amount( $file_info );
	$bytes_amount = get_bmp_bytes_amount( $file_info );

	// Iterate through the pixel data
	for ( $y = 0; $y < $height; $y++ ) {
		for ( $x = 0; $x < $width; $x++ ) {
			$pixel_data = substr( $body_binary, ( $x + $y * $width ) * $bytes_amount, $bytes_amount );
			$color = bin2hex( $pixel_data );

			$color = '_' . $color;

			++$total;

			$x += $padding_amount;

			if ( empty( $stack[ $color ] ) ) {
				$stack[ $color ] = 1;

				continue;
			}

			$stack[ $color ]++;
		}
	}

	// Free some memory.
	unset( $file_info );
	unset( $body_binary );

	// Sort by value.
	asort( $stack );
	$stack = array_reverse( $stack );

	// Extract `MAX_COLORS_OCCURRENCE` from stack.
	$stack = array_slice( $stack, 0, MAX_COLORS_OCCURRENCE, true );

	// Calculate percentage.
	$percentage = array_map( function ( $value ) use ( $total ) {
		return ( $value / $total ) * 100;
	}, $stack );

	$statistics = [];

	// `stack` and `percentage` should be ordered for the client.
	foreach ( $stack as $value => $key ) {
		$statistics[] = [
			'color' => trim( $value, '_' ),
			'occurrence' => $key,
			'percentage' => $percentage[ $value ],
		];
	}

	if ( ! empty( $stack ) ) {
		$result = [
			'success' => true,
			'total' => $total,
			'statistics' => $statistics,
			'usage' => microtime( true ) - $time_start,
			'function_name' => __FUNCTION__,
		];
	}

	return $result;
}

function get_bmp_file_statistics_V1( $file_info ): array {
	$result = [
		'success' => false,
		'message' => 'Failed to get BMP file statistics',
	];

	switch ( $file_info['biBitCount'] ) {
		case '1':
			$pixel_length = 1;
			$mapping_callback = function ( $data ) {
				// TODO: Fix
				if ( $data === '00' ) {
					return '000000';
				}

				return 'FFFFFF';
			};
			break;

		case '24':
			$pixel_length = RGB_LENGTH;
			$mapping_callback = function ( $data ) {
				$r = substr( $data, 4, 2 );
				$g = substr( $data, 2, 2 );
				$b = substr( $data, 0, 2 );

				return $r . $g . $b;
			};
			break;

		case '32':
			$pixel_length = RGBA_LENGTH;
			$mapping_callback = function ( $data ) {
				$r = substr( $data, 4, 2 );
				$g = substr( $data, 2, 2 );
				$b = substr( $data, 0, 2 );
				$a = substr( $data, 6, 2 );

				return $r . $g . $b . $a;
			};
			break;

		default:
			return [
				'success' => false,
				'message' => 'Unsupported bit count',
			];
	}

	$time_start = microtime( true );

	$stack = [];
	$total = 0;
	$body_binary = $file_info['body'];

	for ( $i = 0; $i < $file_info['body_length']; $i += $pixel_length ) {
		$color = bin2hex( substr( $body_binary, $i, $pixel_length ) );
		// '_' is used to avoid 'exculpation' for numerical keys, eg, '000000' will become '0', etc...
		$color = '_' . $mapping_callback( $color );

		++$total;

		if ( empty( $stack[ $color ] ) ) {
			$stack[ $color ] = 1;

			continue;
		}

		$stack[ $color ]++;
	}

	// Free some memory.
	unset( $file_info );
	unset( $body_binary );

	// Sort by value.
	asort( $stack );
	$stack = array_reverse( $stack );

	// Extract `MAX_COLORS_OCCURRENCE` from stack.
	$stack = array_slice( $stack, 0, MAX_COLORS_OCCURRENCE, true );

	// Calculate percentage.
	$percentage = array_map( function ( $value ) use ( $total ) {
		return ( $value / $total ) * 100;
	}, $stack );

	$statistics = [];

	// `stack` and `percentage` should be ordered for the client.
	foreach ( $stack as $value => $key ) {
		$statistics[] = [
			'color' => trim( $value, '_' ),
			'occurrence' => $key,
			'percentage' => $percentage[ $value ],
		];
	}

	if ( ! empty( $stack ) ) {
		$result = [
			'success' => true,
			'total' => $total,
			'statistics' => $statistics,
			'usage' => microtime( true ) - $time_start,
			'function_name' => __FUNCTION__,
		];
	}

	return $result;
}
```

### Results:

```json
[
	{
		"success": true,
		"total": 2457628,
		"statistics": [
			{
				"color": "080800",
				"occurrence": 1225,
				"percentage": 0.049844809710826866
			},
			{
				"color": "5e475b",
				"occurrence": 1162,
				"percentage": 0.04728136235427005
			},
			{
				"color": "5d465a",
				"occurrence": 1147,
				"percentage": 0.04667101774556605
			},
			{
				"color": "574156",
				"occurrence": 1133,
				"percentage": 0.04610136277744231
			},
			{
				"color": "070700",
				"occurrence": 1125,
				"percentage": 0.045775845652800184
			}
		],
		"usage": 4.454843997955322,
		"function_name": "get_bmp_file_statistics_V1"
	},
	{
		"success": true,
		"total": 2457600,
		"statistics": [
			{
				"color": "000808",
				"occurrence": 1225,
				"percentage": 0.049845377604166664
			},
			{
				"color": "5b475e",
				"occurrence": 1162,
				"percentage": 0.04728190104166667
			},
			{
				"color": "5a465d",
				"occurrence": 1147,
				"percentage": 0.046671549479166664
			},
			{
				"color": "564157",
				"occurrence": 1133,
				"percentage": 0.046101888020833336
			},
			{
				"color": "000707",
				"occurrence": 1125,
				"percentage": 0.0457763671875
			}
		],
		"usage": 1.7410809993743896,
		"function_name": "get_bmp_file_statistics_V2"
	}
]


```
I noticed that `V2` suffer from the same issue as `V1`, __RGB__, __RGBA__  are not ordered correctly.

so I extracted the code which reorder the __RGB__ values, and created this function:

```php
function get_bmp_rgb_fixed_order( $file_info, $color_hex ): string {
	switch ( $file_info['biBitCount'] ) {
		case '24':
			$r = substr( $color_hex, 4, 2 );
			$g = substr( $color_hex, 2, 2 );
			$b = substr( $color_hex, 0, 2 );

			return $r . $g . $b;
	}
	
	// ...
}
```

I notice that the execution time become drastically higher about x2 times `(3.8472611904144287)`,
so I taught maybe I can use something else instead of `substr`.

so id did few tests:

#### bitwise operators
```php
$dec = hexdec( $color_hex );

// Extract red.
$r = $dec & 0xFF;

// Extract green.
$g = ( $dec >> 8 ) & 0xFF;

// Extract blue.
$b = ( $dec >> 16 ) & 0xFF;

// Recombine `rgb` in `bgr` order.
$bgr = ( $r << 16 ) | ( $g << 8 ) | $b;

// Add leading zeros & convert to hex.
$result = sprintf("%06X", $bgr );

break;
```
execution time `4.247522830963135`

#### sscanf + sprintf
```php
<?php

list( $r, $g, $b ) = sscanf( $color_hex, '%02X%02X%02X' );

$result = sprintf( '%02X%02X%02X', $b, $g, $r ); // Swap red and blue.

break;
```
execution time `4.968564033508301`

#### unpack + sprintf
```php
list( , $r, $g, $b ) = unpack( 'C3', $color_hex );

// Add leading zeros & convert to hex.
$result = sprintf('%02X%02X%02X', $b, $g, $r); // Swap R & B.

break;
```
execution time `3.7468149662017822`

#### unpack + sprintf has been chosen.

---

#### Next-step is covering all bit counts. 

- 1-bit image, monochrome image
- 4-bit image, 16 colors
- 8-bit image, 256 colors
- 16-bit image, 65,536 colors

### I Covered 1-bit images

![alt text](img_3.png)

- Tested with different files.
- Checked the output using https://onlinejpgtools.com/find-dominant-jpg-colors
- Research: [test](../tests/1bpp/1bpp-test.php)

### Added color palette support for 1-bit images:
![alt text](img_4.png)

### Known issue:
Paletted image with 2 bits/pixel. Usually only 1, 4, and 8 are allowed, but 2 is legal on Windows CE.

They works in `markdown` editor, but __PHPStrom__ cannot open them, currently i will skip that.

![alt text](../bitmaps_collection/2/pal2-gray.bmp)
![alt text](../bitmaps_collection/2/pal2-colored.bmp)

### Added support for 4-bit images:
* Including RLE4 compression.

![alt text](img_6.png)

### Added support for 8-bit images:
* Including RLE8 compression.

![alt text](img_7.png)

### Added New UI features:
- Max timeout.
- Max colors. 

---

Till this commit, it takes 6 days average working hours to complete the task, not including the first day which started late.

This is includes research, testing, and coding. not copy/paste.
