<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Core\BMP;

use Decoders\RLE4_Decoder;
use Decoders\RLE8_Decoder;
use Enums\E_Bits_Per_Pixel;
use Enums\E_Compressions;
use Exception;
use File_Readers\Bitmap_File_Reader;

const MAX_COLORS_OCCURRENCE = 20;

/**
 * Function get_bmp_colors().
 *
 * Return array of all colors for given pixels data
 *
 * @param Bitmap_File_Reader $file_reader
 *
 * @return array
 */
function get_bmp_colors( Bitmap_File_Reader $file_reader): array {
	/**
	 * @var E_Bits_Per_Pixel $bits_per_pixel
	 */
	$bits_per_pixel = $file_reader['bits_per_pixel'];

	switch ( $bits_per_pixel->get_value() ) {
		// Check research in `<project-root>/tests/1bpp/1bpp-test.php` for more info.
		case E_Bits_Per_Pixel::E_BPP_1:
			$result = get_colors_binary_1bpp( $file_reader );
			break;

		case E_Bits_Per_Pixel::E_BPP_4:
			if ( E_Compressions::E_COMPRESSIONS_BI_RLE4 === (string) $file_reader['compression'] ){
				$decoder = new RLE4_Decoder(
					$file_reader['width'],
					$file_reader['height'],
					$file_reader['body'],
					$file_reader['body_length']
				);

				$result = get_colors_simple( $file_reader, $decoder->decode()->get_data() );

				break;
			}

			$result = get_colors_binary_4bpp( $file_reader );

			break;

		case E_Bits_Per_Pixel::E_BPP_8:
			if ( E_Compressions::E_COMPRESSIONS_BI_RLE8 === (string) $file_reader['compression'] ){
				$decoder = new RLE8_Decoder(
					$file_reader['width'],
					$file_reader['height'],
					$file_reader['body'],
					$file_reader['body_length']
				);

				$result = get_colors_simple( $file_reader, $decoder->decode()->get_data() );

				break;
			}

			$result = get_colors_byte_8bpp( $file_reader );

			break;

		case E_Bits_Per_Pixel::E_BPP_24:
		case E_Bits_Per_Pixel::E_BPP_32:
			$result = get_colors_rgb_higher_then_24bpp( $file_reader );
			break;

		default:
			throw new Exception( "Unsupported bits per pixel value: '$bits_per_pixel'." );
	}

	return $result;
}

/**
 * Function get_bmp_statistics().
 *
 * Return array of statistics for given bitmap file.
 *
 * @param Bitmap_File_Reader $file_reader
 * @param $max_colors
 *
 * @return array
 */
function get_bmp_statistics( Bitmap_File_Reader $file_reader, $max_colors ): array {
	$time_start = microtime( true );

	$result = [
		'success' => false,
		'message' => 'Failed to get BMP file statistics',
	];

	$stack = [];
	$total = 0;

	$file_reader->get_data();

	$colors = get_bmp_colors( $file_reader );

	foreach ( $colors as $color ) {
		// '_' is used to avoid 'exculpation' for numerical keys, eg, '000000' will become '0', etc...
		$color = '_' . $color;

		if ( ! isset( $stack[ $color ] ) ) {
			$stack[ $color ] = 0;
		}

		$stack[ $color ]++;
		$total++;
	}

	// Free some memory.
	unset( $file_reader );

	// Sort by value.
	asort( $stack );
	$stack = array_reverse( $stack );

	if ( $max_colors > MAX_COLORS_OCCURRENCE ) {
		$max_colors = MAX_COLORS_OCCURRENCE;
	}

	// Extract `MAX_COLORS_OCCURRENCE` from stack.
	$stack = array_slice( $stack, 0, $max_colors, true );

	// Calculate percentage.
	$percentage = array_map( function ( $value ) use ( $total ) {
		return ( $value / $total ) * 100;
	}, $stack );

	$statistics = [];

	// Since this is an API, the values will be pure.
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
		];
	}

	return $result;
}
