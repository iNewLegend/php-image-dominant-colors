<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Utils;

class Point {
	private int $x;
	private int $y;

	public function __construct( $x = 0, $y = 0 ) {
		$this->x = $x;
		$this->y = $y;
	}

	public function set( $x, $y ): void {
		$this->x = $x;
		$this->y = $y;
	}

	public function set_x( int $x ): void {
		$this->x = $x;
	}

	public function set_y( int $y ): void {
		$this->y = $y;
	}

	public function get_x(): int {
		return $this->x;
	}

	public function get_y(): int {
		return $this->y;
	}

	public function increment_x( $amount = 1 ) {
		$this->x += $amount;
	}

	public function increment_y( $amount = 1) {
		$this->y += $amount;
	}
}
