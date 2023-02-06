# Development Research

> -- Started at 2023-01-22 17:00

__Resources__

- __Research__
    - https://medium.com/sysf/bits-to-bitmaps-a-simple-walkthrough-of-bmp-image-format-765dc6857393
    - https://gibberlings3.github.io/iesdp/file_formats/ie_formats/bmp.htm


- __RLE Compression__
    - https://wiki.multimedia.cx/index.php/Microsoft_RLE


- __Useful links__
    - [File meta scanner](https://www.metadata2go.com/)




<br>

> Notes: The functions that introduced in that research may not exist in the final version of the project.

## Images data

- Me: What are the difference in the data between __bmp__, __jpg__ and __png__?
- GPT3: The main difference in the data between __BMP__, __JPG__, and __PNG__ is the way the data is compressed.
    - __BMP__ (Bitmap) is a standard image file format on Windows, it does not use any compression, so the data is stored in the file exactly as it appears in the image.

      This means that __BMP__ files tend to be quite large, and not efficient for storage or transfer.

    - __JPG__ (Joint Photographic Experts Group) uses lossy compression.

      This means that some image data is lost when the file is compressed, but it results in much smaller file sizes. __JPG__ uses a technique called `discrete cosine transform` __(DCT__) which compresses the image by discarding some of the less important visual information.

      This is efficient for photographs, where there is a lot of redundant information and where the human eye is less sensitive to small details.
    - __PNG__ (Portable Network Graphics) uses lossless compression.

      This means that no image data is lost when the file is compressed. __PNG__ uses a technique called `DEFLATE` which compresses the image by removing repetitive patterns of data.

      This is efficient for graphics and images with transparent backgrounds, where it is important to maintain all of the visual information in the image.

In summary, __BMP__ files are the largest and have no loss in image data, __JPG__ uses lossy compression to make smaller file size but loss some of the image data and __PNG__ uses lossless compression to maintain all the image data but larger file size.

## JPG

- Me: If decode __JPG__ to which structure it can be applied?
- GPT3: When you decode a __JPG__ image, you are essentially taking the compressed image data stored in the file and converting it back into a representation that can be used and manipulated by your application.

  The structure of the decoded image data depends on the format that you choose to use in your application.


Typically, the decoded image data is stored in a __2-dimensional__ array of pixels, where each element in the array represents the color of a single pixel in the image.

Each element of the array can be represented by a struct or a class that contains information about the color such as __RGB__ or __HSL__ values.

The most common format for storing decoded image data is the __RGB__ format, where each pixel is represented by __3 bytes__, one for the __red__ color, one for the __green__ color and one for the __blue__ color.

This format is easy to manipulate and understand, it's also the most common format used in image processing libraries.

Another common format is the __RGBA__ format, where each pixel is represented by __4 bytes__, one for __red__, one for __green__, one for __blue__, and one for __alpha (transparency)__.

There are other formats that can be used, such as __grayscale__, __YCbCr__, and __HSL__, but they are less common and it depends on the application.

## RGB

- Me: Can you give me __RGB__ structure in __C__?
- GPT3: Sure, here's an example of the RGB structure in __C__:

```c
struct RGB {
    unsigned char red;
    unsigned char green;
    unsigned char blue;
};
```
You can use this structure in a __2D__ array to represent the pixels in an image, for example:

```c
struct RGB image[100][100];
```

Each element in the array represents a single pixel in the image, and the __red__, __green__, and __blue__ fields of the structure hold the color values for that pixel.

You can also use it to create a __1D__ array of pixels, and use the width and height of the image to access to a specific pixel.
```c
struct RGB image[100 * 100];
```
and to access the pixel at `x = 2, y = 3` you can use ```image[2 + 3 * width]```
>   Please note that this is a basic example, and you may need to adjust the structure and array sizes based on the specific requirements of your application.

At this point I started search in google for C code that generate an BMP images - it gave me some insights.

I came to the conclusion that I will start by using the __bitmap__ format, since I'm creating software that determine the dominant colors of an image, and the __bitmap__ files generally have a higher quality than __JPEGs__.



I created my first image __RGBA__ __32 bit__ image:

![32-bit-image](./../bitmaps_collection/32/rgba_32.bmp)

; )


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

# Bitmap (BMP)
[Supported formats](#currently-supported-formats)

## Header

- Me: me: can you give me __BMP__ structure in __C__?
- GPT3: Sure, here's an example of the structure of a __BMP__ file in __C__:


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

The __BMPFILEHEADER__ structure contains information about the file itself, such as the type of file and the size of the file. The __BMPINFOHEADER__ structure contains information about the image, such as the dimensions of the image, the number of bits per pixel, and the compression method used.

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

- __biXPelsPerMeter__ and __biYPelsPerMeter__ fields contain the horizontal and vertical resolution of the image, in pixels per meter.

- __biClrUsed__ field contains the number of colors in the color palette, or `0` if the image is a _truecolor_ image.

- __biClrImportant__ field contains the number of important colors used in the image, or `0` if all colors are important.

> Please note that the above structure is for a standard __BMP__ file, some variations of the format may have different structures or fields.

---

- Me: Can you give me more information about __bfOffBits__ ?
- GPT3: __bfOffBits__ is a field in the __BMP__ file header __(BITMAPFILEHEADER)__ that specifies the offset, in bytes, from the beginning of the file to the beginning of the bitmap data.


In other words, it tells you at which position in the file the actual image data begins.

--- 

- Me: What is __biPlanes__?
- GPT3: __biPlanes__ is a field in the __BMP__ information header __(BITMAPINFOHEADER)__ that specifies the number of color planes in the image.

In the case of __BMP__ format, the value of this field is always set to `1`, this field is not used in the __BMP__ format and its purpose is mostly historical.


--- 

- Me: What is __biClrUsed__?
- GPT3: __biClrUsed__ is a field in the __BMP___ information header __(BITMAPINFOHEADER)__ that specifies the number of colors in the color palette of the image.

The color palette is a table that contains the colors used in the image, it's used when the image has a limited number of colors, depends on bits per pixel.

---

- Me: What is __biClrImportant__?
- GPT3: __biClrImportant__ is a field in the __BMP__ information header __(BITMAPINFOHEADER)__ that specifies the number of color indices considered important for displaying the image.
  This field is optional and is not always present in the BMP file format.

When the value of this field is `zero`, it means that all colors are considered important for displaying the image.

When the value of this field is `non-zero`, it indicates the number of color indices considered important for displaying the image.

--- 

- Me: What about __biXPelsPerMeter__, __biYPelsPerMeter__?
- GPT3: __biXPelsPerMeter__ and __biYPelsPerMeter__ are fields in the BMP information header __(BITMAPINFOHEADER)__ that specify the horizontal and vertical resolution of the image, respectively.

These fields indicate the number of pixels per meter in the image.
They are used to indicate the physical dimensions of the image, and are mainly used for printing purposes.

--- 

## BitCount (Bits per pixels)


- Me: If __biBitCount__ is `1` it will be only black and white?
- GPT3: Yes, that's correct. If the __biBitCount__ field in the __BMPINFOHEADER__ structure is set to `1`, it means that each pixel in the image is represented by `1` bit, which can only have two possible values, `0` or `1`.

This means that the image can only have two colors, typically black and white, one color for `0` and another for `1`.

This format is called `Monochrome` and it's used for __1-bit__ deep images, it's also called `Indexed` because the colors are stored in a color table.


__Q__: How to determine how many bytes are used in each pixel in bitmaps?

__A__: Using __biBitCount__

- __1 bit__ per pixel: `1` byte per pixel
- __4 bits__ per pixel: `1/2` byte per pixel
- __8 bits__ per pixel: `1` byte per pixel
- __16 bits__ per pixel: `2` bytes per pixel
- __24 bits__ per pixel: `3` bytes per pixel
- __32 bits__ per pixel: `4` bytes per pixel

---

- Me: how to get __biBitCount__ using PHP?
- GPT3: You can use the __fread__ function in PHP to read the binary data of a __BMP__ file and extract the __biBitCount__ field from it.

The __biBitCount__ field is located at offset `28` in the __BMP__ file's header, and it's a `2` bytes value.

Here's an example of how you can use the __fread__ function to get the __biBitCount__ field of a BMP image:
```php
<?php
$image_path = 'path/to/image.bmp';

$file = fopen($image_path, 'rb');

fseek($file, 28); 

$biBitCount = unpack('v', fread($file, 2))[1];

fclose($file);
```

## Padding

I was curious about it and asked GPT...

- Me: What are padding in __BMP__?
- GPT3: In the __BMP__ file format, padding refers to the extra bytes that may be added to the end of each row of image data in order to align the data on a __4-byte__ boundary.

    - This is necessary because the __BMP__ format stores image data in a bottom-up, left-to-right order and requires that each row of pixels be aligned on a __4-byte__ boundary.

    - The number of padding bytes required for a given row of pixels can be calculated by taking the number of bytes required for the row and rounding up to the nearest multiple of `4`.

    - For example, if the image has a width of `100` pixels and each pixel is `3` bytes __(24 bits)__ in size, the number of bytes required for the row would be `300` __(100 pixels * 3 bytes per pixel)__.
      Since `300` is not a multiple of `4`, `2` padding bytes would be added to the end of the row to make the total number of bytes `302`, which is a multiple of `4`.

    - The padding bytes are typically filled with zeroes, but they may contain any value.

    - When reading a BMP file, it's important to take the padding bytes into account in order to correctly interpret the image data.


- Me: Can you give me example?
- GPT3: Sure! Here's an example of how padding bytes are added to a __BMP__ file in C:

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

    - In this example, the image data is stored in an array of unsigned char, with __3 bytes per pixel__ (one byte for __red__, one byte for __green__, and one byte for __blue__).
      The image data is filled with some arbitrary values for demonstration purposes.

    - The padding variable is calculated based on the width of the image, which is `100` pixels, and the number of bytes per pixel, which is `3`.
      The formula used to calculate the padding is `(4 - (WIDTH * 3) % 4) % 4`, which ensures that the total number of bytes per row is a multiple of `4`.

    - When writing the image data to the file, the program writes one row of pixels at a time, followed by the required number of padding bytes.
      The padding bytes are filled with zeroes, but as I mentioned before, they can contain any value.

- Me: Does all BMP files have padding?
- GPT3: Most __BMP__ files include padding bytes to align each row of image data on __a 4-byte__ boundary as I explained before. However, not all __BMP__ files require padding.

## Image size

After testing different bitmaps, few of them was huge I got an issue:

Of PHP limiting me to specific size...

So I created a custom `php.ini` config for the project

but `xdebug` is not working anymore, I need somehow merge default `php.ini` config with my custom (`custom.php.ini`).

I found solution for that:

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

- It is fine when the bitmaps not including many colors

![](img_0.png)

![](img_1.png)

But when the bitmap including a lot of colors, it's not good enough.

![](img_2.png)

The issue can be fixed by merging familiar colors into one color.

---

After doing few tests with different __biBitCount__'s sizes I came to conclusion that I need favor paddings since it effects the colors stats.

So I started to by updating the parsing method of bitmap files get __height__ and __width__ from the file.

```php
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
```
<br>

> Note: You can see more information about padding in [here](#padding).

<br>

Now I have to use `width` and `height` for looping over the pixels with favor of paddings.

Its require changes in `get_bmp_file_statistics` function, since previously I was looping over the pixels in one loop using the total body length.


```php
function get_bmp_file_statistics_V1( $file_info ): array {
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
```

### Results:

```json
[
	{
		"success": true,
		"total": 2457628,
		"statistics": [  ],
		"usage": 4.454843997955322,
		"function_name": "get_bmp_file_statistics_V1"
	},
	{
		"success": true,
		"total": 2457600,
		"statistics": [  ],
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

# Currently supported formats

- BMP
    - [X] 1-bit image, monochrome image
    - [ ] 2-bit image, 4-color image
    - [X] 4-bit image, 16 colors
    - [X] 8-bit image, 256 colors
    - [ ] 16-bit image, 65,536 colors
    - [X] 24-bit image, 16,777,216 colors
    - [X] 32-bit image, 16,777,216 colors

## 1 bit
![](img_3.png)

- Research: [test](../tests/1bpp/)

#### Color palette support for 1-bit images:
![](img_4.png)

## 2 bit
Paletted image with 2 bits/pixel. Usually only 1, 4, and 8 are allowed, but 2 is legal on Windows CE.

![](../bitmaps_collection/2/pal2-gray.bmp)
![](../bitmaps_collection/2/pal2-colored.bmp)

## 4 bit
* Including RLE4 compression.

![](img_6.png)

## 8 bit
* Including RLE8 compression.
* OS/2 1.x are not supported.

![](img_7.png)

---

What done in this project:

- Researching
- Testing
- Debugging
- Coding (not copy/paste)

---

Till [this](https://github.com/iNewLegend/php-image-dominant-colors/commit/d2451f863c080fb9a11919d194ec0cafffdecd69) commit (__initial__), it takes __6__ days average working hours to complete the challenge, not including the first day which started late.

