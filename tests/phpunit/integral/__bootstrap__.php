<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 * @note   Bootstrap for each suite.
 */

namespace Integral;

use File_Readers\Bitmap_File_Reader;
use function Core\BMP\get_bmp_statistics;
use const Core\BMP\MAX_COLORS_OCCURRENCE;

class Bootstrap {

	const BITMAPS_COLLECTION_PATH = __DIR__ . '/../../../bitmaps_collection/';

	public static function init() {
		// ...
	}

	public static function get_bitmap_statistics( string $relative_path, array $args = [] ): array {
		$bitmap = new Bitmap_File_Reader( Bootstrap::BITMAPS_COLLECTION_PATH . $relative_path );

		$bitmap->open();
		$bitmap->read_data();
		$bitmap->get_data();
		$bitmap->close();

		$result = get_bmp_statistics( $bitmap, array_merge( [
			'max_colors' => MAX_COLORS_OCCURRENCE,
			'colors_merge_sensitivity' => 0,
		], $args ) );

		// Avoid 'load_time'.
		unset( $result['general_settings']['load_time'] );

		return $result;
	}

	public static function file_get_json( $relative_path ) {
		return json_decode( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . $relative_path ), true );
	}

}
