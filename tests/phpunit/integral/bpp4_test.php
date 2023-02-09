<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Integral;

use PHPUnit\Framework\TestCase;

class BPP4_Test extends TestCase {

	public function test__pal_4_colored() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '4/pal4-colored.bmp' );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp4-test/pal4-colored.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

	public function test__pal_4_colored__ensure_colors_sensitivity() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '4/pal4-colored.bmp', [
			'colors_merge_sensitivity' => 5.55,
		] );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp4-test/pal4-colored-sensitivity.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

	public function test__pal4_compressed_rle() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '4/pal4-compressed-rle.bmp' );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp4-test/pal4-compressed-rle.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

	public function test__pal4_gray() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '4/pal4-gray.bmp' );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp4-test/pal4-gray.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

}
