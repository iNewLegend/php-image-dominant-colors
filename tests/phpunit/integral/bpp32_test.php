<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace phpunit\integral;

use Integral\Bootstrap;
use PHPUnit\Framework\TestCase;

class BPP32_Test extends TestCase {

	public function test__colors() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '32/colors.bmp' );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp32-test/colors.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

	public function test__ensure_colors_sensitivity() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '32/colors.bmp', [
			'colors_sensitivity' => 20,
		] );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp32-test/colors-sensitivity.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

}
