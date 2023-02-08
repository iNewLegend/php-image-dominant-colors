<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Core\BMP;

use Color_Utils\RGBA;
use File_Readers\Bitmap_File_Reader;
use Utils\Array_Utils;

const MAX_COLORS_OCCURRENCE = 20;

/**
 * Function get_merged_colors_by_sensitivity.
 *
 * Replace similar colors with an average color by sensitivity.
 *
 * @param float $sensitivity (0.0 - 100.0)
 * @param array $colors
 *
 * @return array
 */
function get_merged_colors_by_sensitivity( float $sensitivity, array $colors ): array {
	$result = [];

	$colors_count = count( $colors );

	$colors_instance_table = [];

	$i1 = 0;
	$i2 = 1;

	while ( $i1 !== $colors_count && $i2 !== $colors_count ) {
		if ( $i1 === $i2 ) {
			$i2++;
			continue;
		}

		if ( empty( $colors[ $i2 ] ) ) {
			$i2++;
			continue;
		}

		if ( empty( $colors[ $i1 ] ) ) {
			$i1++;
			continue;
		}

		$color_1 = $colors[ $i1 ];
		$color_2 = $colors[ $i2 ];

		if ( ! isset( $colors_instance_table[ $color_1 ] ) ) {
			$colors_instance_table[ $color_1 ] = RGBA::create_from_hex( $color_1 );
		}

		if ( ! isset( $colors_instance_table[ $color_2 ] ) ) {
			$colors_instance_table[ $color_2 ] = RGBA::create_from_hex( $color_2 );
		}

		$color1_instance = $colors_instance_table[ $color_1 ];
		$color2_instance = $colors_instance_table[ $color_2 ];

		$distance_percent = $color1_instance->get_distance_percent_to( $color2_instance );

		if ( ( $sensitivity + 1 ) > $distance_percent ) {
			$color_avg = $color1_instance->get_average_to( $color2_instance )->get_as_hex();

			$i1++;
			$i2++;

			$result[] = [
				'color_1' => $color_1,
				'color_2' => $color_2,
				'color_avg' => $color_avg,
			];

			continue;
		}

		$i2++;
	}

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

	// Free memory.
	unset( $file_reader );

	asort( $colors );

	$colors_unique_amount = Array_Utils::array_keys_as_values_unique_count( $colors );

	$initial_unique_colors_count = count( $colors_unique_amount );

	// Free memory.
	unset( $colors );

	$push_color_to_stack = function ( $color, $amount = 1 ) use ( &$stack, &$total_colors_count ) {
		// '_' is used to avoid 'exculpation' for numerical keys, eg, '000000' will become '0', etc...
		$color = '_' . $color;

		if ( ! isset( $stack[ $color ] ) ) {
			$stack[ $color ] = 0;
		}

		$stack[ $color ] += $amount;
		$total_colors_count += $amount;
	};

	$colors_merge_sensitivity = ! empty( $args['colors_merge_sensitivity'] ) ? (float) $args['colors_merge_sensitivity'] : 0.0;

	if ( $colors_merge_sensitivity ) {
		$merged_colors = get_merged_colors_by_sensitivity( $colors_merge_sensitivity, array_keys( $colors_unique_amount ) );

		$total_merged_colors = 0;
		$total_unique_colors = $initial_unique_colors_count;

		foreach ( $merged_colors as $item ) {
			$merged_amount = 0;

			if ( ! isset( $colors_unique_amount[ $item['color_avg'] ] ) ) {
				$colors_unique_amount[ $item['color_avg'] ] = 0;
			}

			$merged_amount += $colors_unique_amount[ $item['color_1'] ];
			$merged_amount += $colors_unique_amount[ $item['color_2'] ];
			$merged_amount += $colors_unique_amount[ $item['color_avg'] ];

			$colors_unique_amount[ $item['color_1'] ] = 0;
			$colors_unique_amount[ $item['color_2'] ] = 0;
			$colors_unique_amount[ $item['color_avg'] ] += $merged_amount;

			$total_merged_colors += 1;
			$total_unique_colors -= 1;
		}
	}

	foreach ( $colors_unique_amount as $color => $amount ) {
		$push_color_to_stack( $color, $amount );
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

		if ( ! empty( $total_merged_colors ) && ! empty( $total_unique_colors ) ) {
			$result['merge'] = [
				'total_colors_count' => $total_merged_colors,
				'unique_colors_count' => $total_unique_colors
			];
		}
	}

	return $result;
}
