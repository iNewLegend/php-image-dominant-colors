<?php
/*
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Utils;

use Exception;

abstract class Enum {
	private $value;

	public function __construct( $value = null ) {
		if ( null === $value ) {
			throw new Exception( 'Value cannot be null' );
		}

		if ( ! is_string( $value ) ) {
			throw new Exception( 'Value must be a string' );
		}

		$reflection = new \ReflectionClass( $this );

		$constants = $reflection->getConstants();

		// Ensure all constants are strings.
		foreach ( $constants as $constant ) {
			if ( ! is_string( $constant ) ) {
				throw new Exception( 'All constants must be strings' );
			}
		}

		$this->value = $value;
	}

	public function get_value(): string {
		return $this->value;
	}

	public function get_as_int() {
		return intval( $this->value );
	}

	public function __toString() {
		return $this->get_value();
	}

	public function is( $value ): bool {
		return $this->value === (string) $value;
	}

	public function higher_then( $value ): bool {
		return $this->get_as_int() > (int) $value;
	}

	public function less_then( $value ): bool {
		return $this->get_as_int() < (int) $value;
	}
}
