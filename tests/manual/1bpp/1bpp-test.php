<?php
require __DIR__ . '/../../../src/boot.php';

use File_Readers\Bitmap_File_Reader;

ob_start();

 $file = __DIR__ . '/../../../bitmaps_collection/1/pal1.bmp';
require __DIR__ . '/../shared.php';

$reader = new Bitmap_File_Reader( $file );

$reader->open();
$reader->read_data();

$file_data = $reader->get_data();

$reader->close();

$width = $file_data['width'];

$total_bits = 0;

for ( $i = 0; $i < $file_data['body_length']; ++$i ) {
	$byte = ord( $file_data['body'][ $i ] );

	$byte = str_pad( decbin( $byte ), 8, '0', STR_PAD_LEFT );

	$byte_required = substr( $byte, 0, $width );

	// Entering binary resolution.
	$binary = str_split($byte_required, 1);

	for( $b = 0 ; $b < count($binary) ; ++$b ) {
		echo $binary[ $b ];

		$total_bits++;
	}

	if ( $total_bits >= $width ) {
		$diff = strlen($byte) - $width;

		if ( $diff > 0 ) {
			$i += $diff;
		}

		$total_bits = 0;

		echo PHP_EOL;
	}
}

// Swallowing the output.
$content = ob_get_clean();

reverse_and_print_multi_content_string( $content );
