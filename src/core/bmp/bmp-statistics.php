<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Core\BMP;

use File_Readers\Bitmap_File_Reader;

const MAX_COLORS_OCCURRENCE = 20;

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
	$total_colors_count = 0;

	$file_reader->get_data();

	$colors = get_bmp_colors( $file_reader );

	$unique_colors_count = count( array_unique( $colors ) );

	// Free memory.
	unset( $file_reader );

	// Set occurrence for each color.
	foreach ( $colors as $color ) {
		// '_' is used to avoid 'exculpation' for numerical keys, eg, '000000' will become '0', etc...
		$color = '_' . $color;

		if ( ! isset( $stack[ $color ] ) ) {
			$stack[ $color ] = 0;
		}

		$stack[ $color ]++;
		$total_colors_count++;
	}

	// Free memory.
	unset( $colors );

	// Sort by value.
	asort( $stack );
	$stack = array_reverse( $stack );

	if ( $max_colors > MAX_COLORS_OCCURRENCE ) {
		$max_colors = MAX_COLORS_OCCURRENCE;
	}

	// Extract `MAX_COLORS_OCCURRENCE` from stack.
	$stack = array_slice( $stack, 0, $max_colors, true );

	// Calculate percentage.
	$percentage = array_map( function ( $value ) use ( $total_colors_count ) {
		return ( $value / $total_colors_count ) * 100;
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
			'statistics' => $statistics,
			'total_colors_count' => $total_colors_count,
			'unique_colors_count' => $unique_colors_count,
			'displayed_colors_count' => count( $stack ),
			'load_time' => microtime( true ) - $time_start,
		];
	}

	return $result;
}
