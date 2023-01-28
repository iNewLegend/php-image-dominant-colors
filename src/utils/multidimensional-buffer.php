<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */
namespace Utils;

class Multidimensional_Buffer {

	private Point $cursor;

	private array $data;

	public function __construct( int $width, int $height ) {
		$this->cursor = new Point();

		$this->data = array_fill( 0, $height, array_fill( 0, $width, 0 ) );
	}

	public function flatten(): array {
		return array_reduce( $this->data, 'array_merge', [] );
	}

	public function set( $value ): void {
		$this->data[ $this->cursor->get_y() ][ $this->cursor->get_x() ] = $value;

		$this->cursor->increment_x();
	}

	public function set_next_row(): void {
		$this->cursor->set_x( 0 );
		$this->cursor->increment_y();
	}

	public function push( array $values ): void {
		foreach ( $values as $value ) {
			$this->set( $value );
		}
	}

	public function get_cursor(): Point {
		return $this->cursor;
	}

	public function get_data(): array {
		return $this->data;
	}

	public function is_empty(): bool {
		if ( empty( $this->data ) ) {
			return true;
		}

		$sum = array_reduce( $this->data, function ( $carry, $item ) {
			return $carry + array_sum( $item );
		}, 0 );

		return $sum === 0;
	}
}
