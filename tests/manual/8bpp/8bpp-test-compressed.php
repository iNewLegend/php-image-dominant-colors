<?php
require __DIR__ . '/../../../src/boot.php';

use Decoders\RLE8_Decoder;
use File_Readers\Bitmap_File_Reader;
use Mike42\GfxPhp\Codec\Bmp\Rle8Decoder;

$file = __DIR__ . '/../../../bitmaps_collection/8/pal8-rle.bmp';

require __DIR__ . '/../shared.php';
require __DIR__ . '/mike42/gfx-php/Rle8Decoder.php';

$reader = new Bitmap_File_Reader( $file );

$reader->open();
$reader->read_data();

$reader->close();

$file_data = $reader->get_data();

$width = $file_data['width'];
$height = $file_data['height'];
$body = $file_data['body'];
$body_length = $file_data['body_length'];

$decoder = new RLE8_Decoder( $width, $height, $body, $body_length );
$mike_decoder = new Rle8Decoder();

$content_mike = $mike_decoder->decode( $body, $width, $height );

$buffer = $decoder->decode();

$content = $decoder->stringify();

assert( $content === $content_mike );

$data = $buffer->get_data();

for ($y = 0; $y < $height; $y++) {
	for ( $x = 0; $x < $width; $x++ ) {
		echo sprintf( '%02X', $data[$y][$x] % 2 );
	}

	echo PHP_EOL;
}
