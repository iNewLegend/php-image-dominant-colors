<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Core\BMP;

use Decoders\RLE4_Decoder;
use Decoders\RLE8_Decoder;
use Enums\E_Bits_Per_Pixel;
use Enums\E_Compressions;
use Exception;
use File_Readers\Bitmap_File_Reader;

/**
 * Function get_colors_simple().
 *
 * No padding, simple one data unit per pixel.
 *
 * @param Bitmap_File_Reader $file_reader
 * @param array $data
 *
 * @return array
 */
function get_colors_simple( Bitmap_File_Reader $file_reader, array $data ): array {
	$result = [];

	for( $y = 0; $y < $file_reader['height']; $y++ ) {
		for( $x = 0; $x < $file_reader['width']; $x++ ) {
			$result[] = get_bmp_color( $file_reader, $data[ $y ][ $x ] );
		}
	}

	return $result;
}

/**
 * Function get_colors_binary().
 *
 * Every byte holds 8 pixels, its highest order bit representing the leftmost pixel of those.
 * There are 2 color table entries. Some readers will ignore them though, and assume that 0 is black and 1 is white.
 * If you are storing black and white pictures you should stick to this, with any other 2 colors this is not an issue.
 * Remember padding with zeros up to a 32bit boundary (This can be up to 31 zeros/pixels!)
 *
 * @param Bitmap_File_Reader $file_reader
 *
 * @return array
 */
function get_colors_binary_1bpp( Bitmap_File_Reader $file_reader ): array {
	$result = [];
	$total_bits = 0;

	$width = $file_reader['width'];

	for ( $i = 0; $i < $file_reader['body_length']; ++$i ) {
		$byte = ord( $file_reader['body'][ $i ] );
		$byte = str_pad( decbin( $byte ), 8, '0', STR_PAD_LEFT );

		$byte_required = substr( $byte, 0, $width );

		// Entering binary resolution.
		$binary = str_split( $byte_required, 1 );
		$binary_length = count( $binary );

		for ( $bit = 0; $bit < $binary_length; ++$bit ) {
			$result[] = get_bmp_color( $file_reader, $binary[ $bit ] );

			$total_bits++;
		}

		// Padding.
		if ( $total_bits >= $width ) {
			$diff = strlen( $byte ) - $width;

			if ( $diff > 0 ) {
				$i += $diff;
			}

			$total_bits = 0;
		}
	}

	return $result;
}

/**
 * Function get_colors_binary_4bpp().
 *
 * Every byte holds 2 pixels, its high order 4 bits representing the left of those.
 * There are 16 color table entries.
 * These colors do not have to be the 16 MS-Windows standard colors.
 * Padding each line with zeros up to a 32bit boundary will result in up to 28 zeros = 7 'wasted pixels'.
 *
 * @param Bitmap_File_Reader $file_reader
 *
 * @return array
 */
function get_colors_binary_4bpp( Bitmap_File_Reader $file_reader ): array {
	$result = [];

	$width = $file_reader['width'];

	// Credits to GPT3, once for lower byte and once for higher byte.
	$bytes_per_row = ceil( $width / 2 );
	$padding_bytes = $bytes_per_row % 4;
	$bytes_to_read = $bytes_per_row + $padding_bytes;

	for ( $y = 0; $y < $file_reader['height']; $y++ ) {
		for ( $x = 0; $x < $bytes_to_read; $x++ ) {
			$byte = ord( $file_reader['body'][ intval( $y * $bytes_to_read + $x ) ] );

			$lower_byte = $byte >> 4;
			$higher_byte = $byte & 0x0F;

			if ( $x * 2 < $width ) {
				$result[] = get_bmp_color( $file_reader, $lower_byte );
			}

			if ( $x * 2 + 1 < $width ) {
				$result[] = get_bmp_color( $file_reader, $higher_byte );
			}
		}
	}

	return $result;
}

/**
 * Function get_colors_binary_8bpp().
 *
 * Every byte holds 1 pixel.
 * There are 256 color table entries.
 * Padding each line with zeros up to a 32bit boundary will result in up to 3 bytes of zeros = 3 'wasted pixels'.
 *
 * @param Bitmap_File_Reader $file_reader
 *
 * @return array
 */
function get_colors_byte_8bpp( Bitmap_File_Reader $file_reader ): array {
	$result = [];

	$total_bytes = 0;

	for ( $i = 0; $i < $file_reader['body_length']; ++$i ) {
		$byte = ord( $file_reader['body'][ $i ] );

		// Padding bytes.
		if ( $total_bytes >= $file_reader['width'] ) {
			$total_bytes = 0;

			continue;
		}

		$result[] = get_bmp_color( $file_reader, $byte );

		$total_bytes++;
	}

	return $result;
}

/**
 * Function get_colors_rgb_higher_then_24bpp().
 *
 * Every 4bytes / 32bit holds 1 pixel.
 * The first holds its red, the second its green, and the third its blue intensity.
 * The fourth byte is reserved or used when 32bpp uses the method.
 * There are no color table entries.
 * The pixels are no color table pointers.
 * No zero padding necessary.
 *
 * @param Bitmap_File_Reader $file_reader
 *
 * @return array
 */
function get_colors_rgb_higher_then_24bpp( Bitmap_File_Reader $file_reader ): array {
	$results = [];

	$bytes_amount = $file_reader['amount'];
	$width = $file_reader['width'];

	for ( $y = 0; $y < $file_reader['height']; $y++ ) {
		for ( $x = 0; $x < $width; $x++ ) {
			$pixel_data = substr( $file_reader['body'], ( $x + $y * $width ) * $bytes_amount, $bytes_amount );

			$results[] = get_bmp_color( $file_reader, $pixel_data );

			$x += $file_reader['padding'];
		}
	}

	return $results;
}

/**
 * Function get_bmp_color().
 *
 * Return color from color table, for lower formats < 8bpp.
 *
 * Return RGB or RGBA color, for higher formats >= 24bpp.
 *
 * @param Bitmap_File_Reader $file_reader
 * @param mixed $context
 *
 * @return string RGB color.
 */
function get_bmp_color( Bitmap_File_Reader $file_reader, mixed $context ): string {
	$result = '';

	/**
	 * @var E_Bits_Per_Pixel $bits_per_pixel
	 */
	$bits_per_pixel = $file_reader['bits_per_pixel'];

	switch ( $bits_per_pixel ) {
		case E_Bits_Per_Pixel::E_BPP_1:
		case E_Bits_Per_Pixel::E_BPP_4:
		case E_Bits_Per_Pixel::E_BPP_8:
			if ( $file_reader['colors_used'] || ! empty( $file_reader['colors_palette'] ) ) {
				$result = $file_reader['colors_palette'][ $context ];

				if ( empty( $result ) ) {
					throw new Exception( 'Color not found in palette.' );
				}
			}
			break;

		case E_Bits_Per_Pixel::E_BPP_24:
			list( , $r, $g, $b ) = unpack( 'C3', $context );

			$result = sprintf( '%02X%02X%02X', $b, $g, $r );

			break;

		case E_Bits_Per_Pixel::E_BPP_32;
			list( , $r, $g, $b, $a ) = unpack( 'C4', $context );

			$result = sprintf( '%02X%02X%02X%02X', $b, $g, $r, $a );

			break;

		default:
			throw new Exception( "Unsupported bits per pixel value: '$bits_per_pixel'." );
	}


	return $result;
}

/**
 * Function get_bmp_colors().
 *
 * Return array of all colors for given pixels data
 *
 * @param Bitmap_File_Reader $file_reader
 *
 * @return array
 */
function get_bmp_colors( Bitmap_File_Reader $file_reader): array {
	/**
	 * @var E_Bits_Per_Pixel $bits_per_pixel
	 */
	$bits_per_pixel = $file_reader['bits_per_pixel'];

	switch ( $bits_per_pixel->get_value() ) {
		// Check research in `<project-root>/tests/1bpp/1bpp-test.php` for more info.
		case E_Bits_Per_Pixel::E_BPP_1:
			$result = get_colors_binary_1bpp( $file_reader );
			break;

		case E_Bits_Per_Pixel::E_BPP_4:
			if ( E_Compressions::E_COMPRESSIONS_BI_RLE4 === (string) $file_reader['compression'] ){
				$decoder = new RLE4_Decoder(
					$file_reader['width'],
					$file_reader['height'],
					$file_reader['body'],
					$file_reader['body_length']
				);

				$result = get_colors_simple( $file_reader, $decoder->decode()->get_data() );

				break;
			}

			$result = get_colors_binary_4bpp( $file_reader );

			break;

		case E_Bits_Per_Pixel::E_BPP_8:
			if ( E_Compressions::E_COMPRESSIONS_BI_RLE8 === (string) $file_reader['compression'] ){
				$decoder = new RLE8_Decoder(
					$file_reader['width'],
					$file_reader['height'],
					$file_reader['body'],
					$file_reader['body_length']
				);

				$result = get_colors_simple( $file_reader, $decoder->decode()->get_data() );

				break;
			}

			$result = get_colors_byte_8bpp( $file_reader );

			break;

		case E_Bits_Per_Pixel::E_BPP_24:
		case E_Bits_Per_Pixel::E_BPP_32:
			$result = get_colors_rgb_higher_then_24bpp( $file_reader );
			break;

		default:
			throw new Exception( "Unsupported bits per pixel value: '$bits_per_pixel'." );
	}

	return $result;
}
