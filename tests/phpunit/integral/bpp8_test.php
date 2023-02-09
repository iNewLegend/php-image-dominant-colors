<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Integral;

use PHPUnit\Framework\TestCase;

class BPP8_Test extends TestCase {
	public function test__pal_8_0() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '8/pal8-0.bmp' );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp8-test/pal8-0.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

	public function test__pal_8_0_sensitivity() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '8/pal8-0.bmp', [
			'colors_merge_sensitivity' => 20,
		] );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp8-test/pal8-0-sensitivity.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

	public function test__pal_8_gray() {
		$actual_statistics = Bootstrap::get_bitmap_statistics( '8/pal8-gray.bmp' );

		$asserted_statistics = Bootstrap::file_get_json( 'bpp8-test/pal8-gray.json' );

		$this->assertEquals( $asserted_statistics, $actual_statistics );
	}

	public function test__pal8_os2__ensure_not_supported() {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Invalid BMP file, OS/2 format not supported.' );

		Bootstrap::get_bitmap_statistics( '8/pal8-os2.bmp' );
	}
}
