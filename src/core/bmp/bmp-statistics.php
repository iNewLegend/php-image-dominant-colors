<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Core\BMP;

use Color_Utils\RGBA;
use File_Readers\Bitmap_File_Reader;

const MAX_COLORS_OCCURRENCE = 20;

function get_merged_colors_by_sensitivity( $sensitivity, &$colors_target ): int {
	$result = 0;

	$unique_colors = array_unique( $colors_target, SORT_REGULAR );

	asort( $colors_target );

	$done = false;

	do {
		$prev_count = count( $unique_colors );

		if ( $prev_count <= 2 ) {
			break;
		}

		foreach ( $unique_colors as $color1 ) {
			foreach ( $unique_colors as $color2 ) {
				if ( $color1 === $color2 ) {
					continue;
				}

				$color1_instance = RGBA::create_from_hex( $color1 );
				$color2_instance = RGBA::create_from_hex( $color2 );

				$distance_percent = $color1_instance->get_distance_percent_to( $color2_instance );

				if ( $distance_percent + $sensitivity > 100 ) {
					$avg_color = $color1_instance->get_average_to( $color2_instance );

					$unique_colors = array_diff( $unique_colors, [ $color1, $color2, $avg_color ] );

					$colors_count = count( $colors_target );

					$colors_target = array_diff( $colors_target, [ $color1, $color2, $avg_color ] );

					$remainder_colors_count = $colors_count - count( $colors_target );

					$result += $remainder_colors_count;

					$colors_target = array_merge( $colors_target, array_fill( 0, $remainder_colors_count, $avg_color ) );

					break 2;
				}
			}
		}

		$current_count = count( $unique_colors );

		if ( $prev_count === $current_count ) {
			$done = true;
		}
	} while ( ! $done );

	return $result;
}

/**
 * Function get_bmp_statistics().
 *
 * Return array of statistics for given bitmap file.
 *
 * @param Bitmap_File_Reader $file_reader
 * @param array              $args
 *
 * @return array
 */
function get_bmp_statistics( Bitmap_File_Reader $file_reader, array $args ): array {
	$time_start = microtime( true );

	$result = [
		'success' => false,
		'message' => 'Failed to get BMP file statistics',
	];

	$stack = [];
	$total_colors_count = 0;

	$file_reader->get_data();

	$colors = get_bmp_colors( $file_reader );

	$initial_unique_colors_count = count( array_unique( $colors, SORT_REGULAR ) );

	// Free memory.
	unset( $file_reader );

	if ( ! empty( $args['colors_sensitivity_merge'] ) && ( $colors_sensitivity_merge = floatval( $args['colors_sensitivity_merge'] ) ) >= 0.1 ) {
		$total_merged_colors = get_merged_colors_by_sensitivity( $colors_sensitivity_merge, $colors );
	}

	foreach ( $colors as $color ) {
		// '_' is used to avoid 'exculpation' for numerical keys, eg, '000000' will become '0', etc...
		$color = '_' . $color;

		if ( ! isset( $stack[ $color ] ) ) {
			$stack[ $color ] = 0;
		}

		$stack[ $color ]++;
		$total_colors_count++;
	}

	// Sort by value.
	asort( $stack );
	$stack = array_reverse( $stack );

	$max_colors = $args['max_colors'];

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
			'unique_colors_count' => $initial_unique_colors_count,
			'displayed_colors_count' => count( $stack ),
			'load_time' => microtime( true ) - $time_start,
		];

		if ( ! empty( $total_merged_colors ) ) {
			$result['merge'] = [
				'total_colors_count' => $total_merged_colors,
				'unique_colors_count' => count( array_unique( $colors, SORT_REGULAR ) ),
			];
		}
	}

	return $result;
}
