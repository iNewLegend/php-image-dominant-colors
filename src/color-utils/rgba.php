<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Color_Utils;

class RGBA {

	const RGBA_MAX_DISTANCE = 510;

	const RGB_MAX_DISTANCE = 441.67295593006;

	protected ?int $red = null;
	protected ?int $green = null;
	protected ?int $blue = null;
	protected ?int $alpha = null;

	public static function create_from_unitis( int $red, int $green, int $blue, $alpha = null ) {
		return new RGBA( [ $red, $green, $blue, $alpha ] );
	}

	public static function create_from_hex( string $hex_color ) {
		return new RGBA( $hex_color, 'create_from_hex' );
	}

	private function __construct( $data, $context = 'create_from_units' ) {
		switch ( $context ) {
			case 'create_from_units':
				$this->red = $data[0];
				$this->green = $data[1];
				$this->blue = $data[2];
				$this->alpha = $data[3] ?? null;
				break;

			case 'create_from_hex':
				$data_length = strlen( $data );

				$data = base_convert( $data, 16, 10 );

				if ( $data_length === 8 ) {
					$this->red = ( $data & 0xFF000000 ) >> 24;
					$this->green = ( $data & 0x00FF0000 ) >> 16;
					$this->blue = ( $data & 0x0000FF00 ) >> 8;
					$this->alpha = $data & 0x000000FF;
				} else if ( $data_length === 6 ) {
					$this->red = ( $data & 0xFF0000 ) >> 16;
					$this->green = ( $data & 0x00FF00 ) >> 8;
					$this->blue = ( $data & 0x0000FF );
				} else {
					throw new \Exception( "Invalid hex color length: '$data_length'" );
				}
				break;

			default:
				throw new \Exception( "Invalid context: '$context'" );
		}
	}

	public function get_as_hex(): string {
		$rgb = sprintf( '%02X%02X%02X', $this->red, $this->green, $this->blue );

		if ( $this->alpha ) {
			$rgb .= sprintf( '%02X', $this->alpha );
		}

		return $rgb;
	}

	public function get_as_units(): array {
		return [ $this->red, $this->green, $this->blue, $this->alpha ];
	}

	public function get_average_to( RGBA $rgba ): RGBA {
		$r1 = $this->red;
		$g1 = $this->green;
		$b1 = $this->blue;
		$a1 = $this->alpha;

		$r2 = $rgba->red;
		$g2 = $rgba->green;
		$b2 = $rgba->blue;
		$a2 = $rgba->alpha;

		$r = intdiv( $r1 + $r2, 2 );
		$g = intdiv( $g1 + $g2, 2 );
		$b = intdiv( $b1 + $b2, 2 );
		$a = intdiv( $a1 + $a2, 2 );

		return self::create_from_unitis( $r, $g, $b, $a );
	}

	public function get_distance_to( RGBA $rgba ): float {
		$r1 = $this->red;
		$g1 = $this->green;
		$b1 = $this->blue;
		$a1 = $this->alpha;

		$r2 = $rgba->red;
		$g2 = $rgba->green;
		$b2 = $rgba->blue;
		$a2 = $rgba->alpha;

		if ( $a1 && $a2 ) {
			return sqrt(
				pow( $r2 - $r1, 2 ) +
				pow( $g2 - $g1, 2 ) +
				pow( $b2 - $b1, 2 ) +
				pow( $a2 - $a1, 2 ) );
		}

		return sqrt(
			pow( $r2 - $r1, 2 ) +
			pow( $g2 - $g1, 2 ) +
			pow( $b2 - $b1, 2 ) );
	}

	public function get_distance_percent_to( RGBA $rgba ): float {
		$max_distance = $this->alpha && $rgba->alpha ? self::RGBA_MAX_DISTANCE : self::RGB_MAX_DISTANCE;

		return 100 * $this->get_distance_to( $rgba ) / ( $max_distance );
	}

	public function __toString() {
		return $this->get_as_hex();
	}
}
