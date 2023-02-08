<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Utils;

class Array_Utils {

	public static function array_keys_as_values_unique_count( $array ) {
		$result = [];

		foreach ( $array as $item ) {
			if ( ! isset( $result[ $item ] ) ) {
				$result[ $item ] = 0;
			}

			$result[ $item ]++;
		}

		return $result;
	}
}

