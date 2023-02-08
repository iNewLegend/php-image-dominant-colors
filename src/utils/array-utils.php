<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Utils;

class Array_Utils {

	/**
	 * Function array_keys_as_values_unique_count().
	 *
	 * Make array with keys as values and count of each value as value.
	 *
	 * @param $array
	 *
	 * @return array
	 */
	public static function array_keys_as_values_unique_count( $array ): array {
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

