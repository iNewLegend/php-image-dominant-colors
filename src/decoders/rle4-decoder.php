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
 * The first of these specifies the number of consecutive pixels with the same pair of color.
 * The second byte defines two color indices.
 * The resulting pixel pattern will be interleaved high-order 4bits and low order 4 bits (ABABA...).
 * If the first byte is zero, the second defines an escape code.
 * The End-of-Bitmap is zero padded to end on a 32bit boundary.
 * Due to the 16bit-ness of this structure this will always be either two zero bytes or none.
 *
 * @credits https://gibberlings3.github.io/iesdp/file_formats/ie_formats/bmp.htm#Raster4RLE
 * @credits https://github.com/mike42/gfx-php/blob/master/src/Mike42/GfxPhp/Codec/Bmp/Rle4Decoder.php
 */
class RLE4_Decoder extends Base_Decoder {

	public function decode(): Multidimensional_Buffer {
		// Two bytes at a time.
		for ( $this->zero_offset(); false === $this->is_end(); $this->increment_offset() ) {
			list ( $byte_1, $byte_2 ) = $this->get_next_bytes();

			$switch_1 = new E_Compressions_Modes( (string) $byte_1 );
			$switch_2 = new E_Compression_Encoded_Modes( (string) $byte_2 );

			if ( $switch_1->is_encoded() ) {
				$this->handle_escape_mode( $switch_2 );
			} else if ( $switch_1->is_uncompressed() ) {
				$lower_byte2 = $byte_2 >> 4;
				$higher_byte2 = $byte_2 & 0x0F;

				for ( $j = 0; $j < $byte_1; $j++ ) {
					$this->buffer->set( $j % 2 == 0 ? $lower_byte2 : $higher_byte2 );
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

		$output = [];

		// Padding.
		$bytes_per_row = ceil( $this->width / 2 );
		$padding_bytes = $bytes_per_row % 4;
		$row_width = $bytes_per_row + $padding_bytes;

		$data = $this->buffer->get_data();

		foreach ( $data as $row_orig ) {
			// Combine two numbers 0-16 into one numeric value 0-256.
			$row = array_fill( 0, $row_width, 0 );
			$chunks = array_chunk( $row_orig, 2 );
			$chunks_length = count( $chunks );

			for ( $i = 0; $i < $chunks_length; $i++ ) {
				$chunk = $chunks[ $i ];

				if ( count( $chunk ) == 2 ) {
					$row[ $i ] = ( $chunk[0] << 4 ) + $chunk[1];
				} else {
					$row[ $i ] = $chunk[0];
				}
			}

			$output[] = pack( "C*", ...$row );
		}

		return implode( $output );
	}

	protected function handle_absolute() {
		// Absolute mode.
		$index = $this->get_offset() + 2;

		$pixels_length = $this->get_second_byte();
		$bytes_length = intdiv( ( $pixels_length + 1 ), 2 );

		$sources_bytes = substr( $this->binary_data, $index, $bytes_length );
		$values = array_fill( 0, $pixels_length, 0 );

		// Prepare the values.
		for ( $j = 0; $j < $pixels_length; $j++ ) {
			$source_byte = ord( $sources_bytes[ intdiv( $j, 2 ) ] );

			$lower_byte = $source_byte >> 4;
			$higher_byte = $source_byte & 0x0F;

			$values[ $j ] = ( $j % 2 == 0 ) ? $lower_byte : $higher_byte;
		}

		$this->increment_offset( $bytes_length );

		// Skip padding byte, eg ( 0x3 0x4	=> 0x0 0x4 0x0 ).
		if ( $bytes_length % 2 !== 0 ) {
			$this->increment_offset( 1 );
		}

		$this->buffer->push( $values );	}
}
