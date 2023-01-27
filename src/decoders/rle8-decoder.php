<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Decoders;

use Enums\E_Compression_Encoded_Modes;
use Enums\E_Compressions_Modes;
use Exception;
use Utils\Multidimensional_Buffer;

/**
 * The pixel data is stored in 2bytes / 16bit chunks.
 * The first of these specifies the number of consecutive pixels with the same color.
 * The second byte defines their color index. If the first byte is zero, the second defines an escape code.
 * The End-of-Bitmap is zero padded to end on a 32bit boundary.
 * Due to the 16bit-ness of this structure this will always be either two zero bytes or none.
 *
 * @credits https://github.dev/mike42/gfx-php/blob/ed9ded2a9298e4084a9c557ab74a89b71e43dbdb/src/Mike42/GfxPhp/Codec/Bmp/Rle8Decoder.php#
 * @credits https://gibberlings3.github.io/iesdp/file_formats/ie_formats/bmp.htm#Raster8RLE
 */
class RLE8_Decoder extends Base_Decoder {

	public function decode(): Multidimensional_Buffer {
		// Two bytes at a time.
		for ( $this->zero_offset(); false === $this->is_end(); $this->increment_offset() ) {
			list ( $byte_1, $byte_2 ) = $this->get_next_bytes();

			$switch_1 = new E_Compressions_Modes( (string) $byte_1 );
			$switch_2 = new E_Compression_Encoded_Modes( (string) $byte_2 );

			if ( $switch_1->is_encoded() ) {
				$this->handle_escape_mode( $switch_2 );
			} else if ( $switch_1->is_uncompressed() ) {
				for ( $j = 0; $j < $byte_1; $j++ ) {
					$this->buffer->set( $byte_2 );
				}
			} else {
				throw new Exception( sprintf( 'Unexpected operation value: `%02X%02X`', $byte_1, $byte_2 ) );
			}
		}

		return $this->buffer;
	}

	public function stringify(): string {
		if ( $this->buffer->is_empty() ) {
			throw new Exception( 'Buffer is empty. Please decode first.' );
		}

		$data = $this->buffer->get_data();

		$output = [];
		$row_width = intdiv( ( 8 * $this->width + 31 ), 32 ) * 4;

		$padding = str_repeat( "\0", $row_width - $this->width );

		foreach ( $data as $row ) {
			$output[] = pack( "C*", ...$row );
		}

		return implode( $padding, $output ) . $padding;
	}

	protected function handle_absolute() {
		// Absolute mode.
		$index = $this->get_offset() + 2;

		$pixels_length = $this->get_second_byte();

		$sources_bytes = substr( $this->binary_data, $index, $pixels_length );

		// Prepare the values.
		for ( $j = 0; $j < $pixels_length; $j++ ) {
			$values[] = ord( $sources_bytes[ $j ] );
		}

		$this->increment_offset( $pixels_length );

		// Skip padding byte, eg ( 0x03 0x04 => 0x04 0x04 0x04 ).
		if ( $pixels_length % 2 !== 0 ) {
			$this->increment_offset( 1 );
		}

		$this->buffer->push( $values );
	}
}
