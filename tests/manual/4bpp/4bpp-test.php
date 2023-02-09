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

$total_bits = 0;

for ( $i = 0; $i < $file_data['body_length']; $i++ ) {
	$byte_in_bits = ord( $file_data['body'][ $i ] );
	$byte_in_bits = str_pad( decbin( $byte_in_bits ), 8, '0', STR_PAD_LEFT );

	// Entering binary resolution.
	$binary = str_split( $byte_in_bits, 2 );
	$binary = $binary[0] . $binary[1];
	$total_bits += 2;

	echo $binary;

	// TODO: Try to add padding in the end of a row.

	if ( $total_bits >= $width ) {
		$total_bits = 0;
		echo PHP_EOL;
	}
}

// Swallowing the output.
$content = ob_get_clean();

reverse_and_print_multi_content_string( $content );
