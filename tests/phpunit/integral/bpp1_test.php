<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Integral;

use PHPUnit\Framework\TestCase;

class BPP1_Test extends TestCase {

	public function test__cross() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '1/cross.bmp' );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp1-test/cross.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

	public function test__cross_ensure_colors_sensitivity() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '1/cross.bmp', [
			'colors_merge_sensitivity' => 0.2,
		] );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp1-test/cross-sensitivity.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

	public function test__pal1() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '1/pal1.bmp' );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp1-test/pal1.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

	public function test__pal1_color_palette() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '1/pal1-color-palette.bmp' );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp1-test/pal1-color-palette.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}
}
