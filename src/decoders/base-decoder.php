<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Decoders;

use Enums\E_Compression_Encoded_Modes;
use Utils\Multidimensional_Buffer;

abstract class Base_Decoder {

	protected int $width = 0;
	protected int $height = 0;

	protected string $binary_data = '';
	protected int $binary_length = 0;
	protected int $binary_offset = 0;

	protected Multidimensional_Buffer $buffer;

	protected int $normalized_length = 0;

	public function __construct( int $width, int $height, string $binary_data, $binary_length = null ) {
		$this->width = $width;
		$this->height = $height;

		$this->binary_data = $binary_data;

		$this->buffer = new Multidimensional_Buffer( $width, $height );

		if ( null === $binary_length ) {
			$binary_length = strlen( $binary_data );
		}

		$this->binary_length = $binary_length;

		$this->normalized_length = intdiv( $this->binary_length, 2 ) * 2;
	}

	public abstract function decode(): Multidimensional_Buffer;

	public abstract function stringify(): string;

	protected function handle_escape_mode( E_Compression_Encoded_Modes $switch ): bool {
		$result = true;

		if ( $switch->is( E_Compression_Encoded_Modes::E_COMPRESSIONS_CODES_EOL ) ) {
			// Next line.
			$this->buffer->set_next_row();
		} else if ( $switch->is( E_Compression_Encoded_Modes::E_COMPRESSIONS_CODES_EOB ) ) {
			// End of bitmap.
			$this->binary_offset = $this->normalized_length;
		} else if ( $switch->is( E_Compression_Encoded_Modes::E_COMPRESSIONS_CODES_DELTA ) ) {
			// Set jump/delta.
			$index = $this->get_offset() + 2;

			list( $new_x, $new_y ) = $this->get_next_bytes( $index );

			$cursor = $this->buffer->get_cursor();

			$cursor->increment_y( $new_y );
			$cursor->increment_x( $new_x );

			$this->increment_offset();
		} else if ( $switch->is_absolute() ) {
			$this->handle_absolute();
		} else {
			$result = false;
		}

		return $result;
	}

	protected abstract function handle_absolute();

	protected function zero_offset(): void {
		$this->binary_offset = 0;
	}

	protected function get_offset(): int {
		return $this->binary_offset;
	}

	protected function increment_offset( int $amount = 2 ): void {
		$this->binary_offset += $amount;
	}

	protected function is_end(): bool {
		return $this->binary_offset >= $this->normalized_length;
	}

	protected function get_next_bytes( $offset = null ): array {
		if ( null === $offset ) {
			$offset = $this->get_offset();
		}

		return [
			$this->get_first_byte( $offset ),
			$this->get_second_byte( $offset ),
		];
	}

	protected function get_first_byte( $offset = null ): int {
		if ( null === $offset ) {
			$offset = $this->get_offset();
		}

		return ord( $this->binary_data[ $offset ] );
	}

	protected function get_second_byte( $offset = null ): int {
		if ( null === $offset ) {
			$offset = $this->get_offset();
		}

		return ord( $this->binary_data[ $offset + 1 ] );
	}

}
