<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Integral;

use PHPUnit\Framework\TestCase;

class BPP2_Test extends TestCase {

	public function test__pal_2_colored__ensure_not_supported() {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( '2-bit BMP images are not supported.' );

		Bootstrap::get_bitmap_statistics( '2/pal2-colored.bmp' );
	}

}
