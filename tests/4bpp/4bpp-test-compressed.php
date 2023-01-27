<?php
require __DIR__ . '/../../src/boot.php';

use Decoders\RLE4_Decoder;
use File_Readers\Bitmap_File_Reader;

$file = __DIR__ . '/../../bitmaps_collection/4/pal4-compressed-rle.bmp';

require __DIR__ . '/../shared.php';
require __DIR__ . '/mike42/gfx-php/Rle4Decoder.php';

$reader = new Bitmap_File_Reader( $file );

$reader->open();
$reader->read_data();

$reader->close();

$file_data = $reader->get_data();

$width = $file_data['width'];
$height = $file_data['height'];
$body = $file_data['body'];
$body_length = $file_data['body_length'];

$decoder = new RLE4_Decoder( $width, $height, $body, $body_length );
$mike_decoder = new \Mike42\GfxPhp\Codec\Bmp\Rle4Decoder();

$content_mike = $mike_decoder->decode( $body, $width, $height );

$buffer = $decoder->decode();

$content = $decoder->stringify();

assert( $content === $content_mike );

// Calculate the padding
$bytes_per_row = ceil($width / 2);
$padding_bytes = $bytes_per_row % 4;
$bytes_to_read = $bytes_per_row + $padding_bytes;

for ($y = 0; $y < $height; $y++) {
	for ( $x = 0; $x < $bytes_to_read; $x++ ) {
		$byte = ord( $content[ intval($y * $bytes_to_read + $x) ] );
		$byte_in_bits = str_pad( decbin( $byte ), 8, '0', STR_PAD_LEFT );
		$byte_in_bits = str_split( $byte_in_bits, 2 );

		$lower_byte = $byte >> 4;
		$higher_byte = $byte & 0x0F;

		if ( $x * 2 < $width ) {
			echo $byte_in_bits[0];
		}

		if ( $x * 2 + 1 < $width ) {
			echo $byte_in_bits[1];
		}
	}

	echo PHP_EOL;
}
