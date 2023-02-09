<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Integral;

use PHPUnit\Framework\TestCase;

class BPP24_Test extends TestCase {

	public function test__9768() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '24/9768.bmp' );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp24-test/9768.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

	public function test__ensure_colors_sensitivity() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '24/9768.bmp', [
			'colors_sensitivity' => 20,
		] );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp24-test/9768-sensitivity.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

}
