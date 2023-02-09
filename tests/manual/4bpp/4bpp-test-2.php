<?php
require __DIR__ . '/../../../src/boot.php';

use File_Readers\Bitmap_File_Reader;

ob_start();

$file = __DIR__ . '/../../../bitmaps_collection/4/pal4-colored.bmp';

require __DIR__ . '/../shared.php';
require __DIR__ . '/../../../src/file-readers/bitmap-file-reader.php';

$reader = new Bitmap_File_Reader( $file );

$reader->open();
$reader->read_data();

$file_data = $reader->get_data();

$reader->close();


$width = $file_data['width'];
$height = $file_data['height'];

$total_bits = 0;
$bit_count = $file_data['bits_per_pixel'];

// Calculate the padding
$bytes_per_row = ceil($width / 2);
$padding_bytes = $bytes_per_row % 4;
$bytes_to_read = $bytes_per_row + $padding_bytes;

for ($y = 0; $y < $height; $y++) {
	for ( $x = 0; $x < $bytes_to_read; $x++ ) {
		$byte = ord( $file_data['body'][ intval($y * $bytes_to_read + $x) ] );
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

// Swallowing the output.
$content = ob_get_clean();

reverse_and_print_multi_content_string( $content );
