<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 *
 * #define RGB(r, g, b) ((r << 16) | (g << 8) | b)
 *
 * Move left 2 bytes.
 * $r = ( $value >> 16 ) & 0xFF;
 *
 * Move right 1 byte.
 * $g = ( $value >> 8 ) & 0xFF;
 *
 * Take lower byte and mask it on an byte
 * $b = $value & 0xFF;
 */

namespace Color_Utils;

class RGBA {

	protected int $red = 0;
	protected int $green = 0;
	protected int $blue = 0;

	protected string $value = '';

	public function __construct( int $red, int $green, int $blue, $alpha = null ) {
		$this->red = $red;
		$this->green = $green;
		$this->blue = $blue;

		$this->value = ( ( $red << 16 ) | ( $green << 8 ) | $blue );
	}

	public function get_as_hex(): string {
		return sprintf( '%02x%02x%02x', $this->red, $this->green, $this->blue );
	}

	public function __toString() {
		return $this->get_as_hex();
	}
}
