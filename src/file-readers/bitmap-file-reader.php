<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace File_Readers;

use Color_Utils\RGBA;
use Enums\E_Bits_Per_Pixel;
use Enums\E_Bytes_Per_BPP;
use Enums\E_Compressions;
use Exception;
use File_Readers\Args\Bitmap_Reader_Args;

class Bitmap_File_Reader extends Base_File_Reader {
	const FILE_EXTENSION = 'bmp';

	const FILE_HEADER_MAGIC_NUMBER = '424d';
	const FILE_HEADER_SIZE = 14;
	const FILE_HEADER_INFO_SIZE = 40;

	const WINDOWS_COLORS_SIZE = 4;

	const OS21X_HEADER_SIZE = 12;
	const OS21X_COLORS_SIZE = 3;

	// --- Part of BMP_FILE_HEADER ---
	private int $file_size = 0;
	private int $reserved1 = 0;
	private int $reserved2 = 0;
	private int $pixels_offset = 0;

	// --- Part of BMP_INFO_HEADER ---
	private int $info_header_size = 0;
	private int $width = 0;
	private int $height = 0;
	private int $planes = 0;
	private E_Bits_Per_Pixel $bits_per_pixel;
	private E_Compressions $compression;
	private int $image_size = 0;
	private int $x_pixels_per_meter = 0;
	private int $y_pixels_per_meter = 0;
	private int $colors_used = 0;
	private int $colors_important = 0;

	private array $colors_palette = [];

	// Custom.
	private string $header = '';
	private string $body = '';

	private int $header_length = 0;
	private int $body_length = 0;

	private float $amount = 0;
	private int $padding = 0;

	public function read_data(): void {
		$args = $this->get_args();

		$this->seek( 2 ); // Skip magic number.

		// BMP_FILE_HEADER
		$this->file_size = $this->read_uint();      // bfSize.
		$this->reserved1 = $this->read_ushort();    // bfReserved1.
		$this->reserved2 = $this->read_ushort();    // bfReserved2.
		$this->pixels_offset = $this->read_uint();  // bfOffBits.

		if ( $args['get_file_size_manually'] && ! $this->file_size ) {
			$this->file_size = $this->get_file_size();
		}

		// BMP_INFO_HEADER.
		$this->info_header_size = $this->read_uint();   // biSize.

		$this->width = abs( $this->read_int() );        // biWidth..
		$this->height = abs( $this->read_int() );       // biHeight.

		$this->planes = $this->read_ushort();         // biPlanes (planes for the target device, this is always 1).

		$this->bits_per_pixel = new E_Bits_Per_Pixel( (string) $this->read_ushort() ); // biBitCount.

		if ( E_Bits_Per_Pixel::E_BPP_2 === $this->bits_per_pixel->get_value() ) {
			throw new Exception( '2-bit BMP images are not supported.' );
		}

		$this->compression = new E_Compressions( (string) $this->read_uint() ); // biCompression.

		$this->validate_compression();

		$this->image_size = $this->read_uint();         // biSizeImage.

		$this->x_pixels_per_meter = $this->read_int();  // biXPelsPerMeter.
		$this->y_pixels_per_meter = $this->read_int();  // biYPelsPerMeter.

		$this->colors_used = $this->read_uint();        // biClrUsed (Colors pallet).

		$this->validate_colors_used();

		$this->colors_important = $this->read_uint();   // biClrImportant;

		$this->read_colors_palette();

		$this->body_length = $this->file_size - $this->pixels_offset;
		$this->header_length = $this->file_size - $this->body_length;

		$this->seek( 0 );

		$this->header = $this->read( $this->header_length );

		$this->seek( $this->pixels_offset );

		$this->body = $this->read( $this->body_length );

		// Extra data.
		$this->set_amount();
		$this->set_padding();

		$this->is_data_read = true;
	}

	public function get_file_extension(): string {
		return self::FILE_EXTENSION;
	}

	public function validate_header(): bool {
		return self::FILE_HEADER_MAGIC_NUMBER === bin2hex( $this->read( 2 ) );
	}

	protected function get_args_class(): string {
		return Bitmap_Reader_Args::class;
	}

	protected function get_user_data(): array {
		return [
			'file_size' => $this->file_size,
			//'reserved1' => $this->reserved1,
			//'reserved2' => $this->reserved2,
			'pixels_offset' => $this->pixels_offset,
			//'header_size' => $this->header_size,
			'width' => $this->width,
			'height' => $this->height,
			//'planes' => $this->planes,
			'bits_per_pixel' => $this->bits_per_pixel,
			'compression' => $this->compression->get_value(),
			//'image_size' => $this->image_size,
			//'x_pixels_per_meter' => $this->x_pixels_per_meter,
			//'y_pixels_per_meter' => $this->y_pixels_per_meter,
			'colors_used' => $this->colors_used,
			//'colors_important' => $this->colors_important,
			'colors_palette' => $this->colors_palette,
			'header' => $this->header,
			'body' => $this->body,
			'header_length' => $this->header_length,
			'body_length' => $this->body_length,
			'amount' => $this->amount,
			'padding' => $this->padding,
		];
	}

	private function validate_compression(): void {
		if ( empty( $this->get_args()['bypass_compression_check'] ) ) {
			$result = $this->compression->is( E_Compressions::E_COMPRESSIONS_BI_RLE4 ) &&
			          ! $this->bits_per_pixel->is( E_Bits_Per_Pixel::E_BPP_4 );

			if ( $result ) {
				throw new Exception( 'RLE4 compression is only supported for 4-bit BMP images.' );
			}

			$result = $this->compression->is( E_Compressions::E_COMPRESSIONS_BI_RLE8 ) &&
			          ! $this->bits_per_pixel->is( E_Bits_Per_Pixel::E_BPP_8 );

			if ( $result ) {
				throw new Exception( 'RLE8 compression is only supported for 8-bit BMP images.' );
			}
		}
	}

	private function validate_colors_used(): void {
		if ( ! empty( $this->get_args()['bypass_colors_used_check'] ) ) {
			// If this is set to '0' in decimal :- 2^BitsPerPixel colors are used.
			if ( ! $this->colors_used ) {
				$this->colors_used = pow( 2, $this->bits_per_pixel->get_as_int() );
			}
		}
	}

	private function read_colors_palette(): void {
		// Check bits_per_pixel range. (
		if ( $this->bits_per_pixel->less_then( E_Bits_Per_Pixel::E_BPP_24 ) ) {
			$colors_count = $this->colors_used;

			$osx_header_size_calc = self::OS21X_HEADER_SIZE + self::FILE_HEADER_SIZE;

			// Validate whatever we deal with OSX or Windows BMP.
			$is_osx_header = $this->info_header_size === self::OS21X_HEADER_SIZE;

			// For sure OSX header.
			if ( $is_osx_header && $colors_count * self::OS21X_COLORS_SIZE + $osx_header_size_calc > $this->offset ) {

				$calculated_palette_size = intdiv( ( $this->offset - $osx_header_size_calc ), 3 );

				// Ensure that palette size.
				if ( $calculated_palette_size < $colors_count ) {
					$colors_count = $calculated_palette_size;
				}

				for( $i = 0; $i < $colors_count; $i++ ) {
					$chunk = $this->read_bytes( self::OS21X_COLORS_SIZE );

					$this->colors_palette[] = new RGBA( $chunk[3], $chunk[2], $chunk[1] );
				}

				return;
			}

			// Windows header.
			$win_header_size_calc = $this->info_header_size + self::FILE_HEADER_SIZE;

			for( $i = 0; $i < $colors_count; $i++ ) {
				$chunk = $this->read_bytes( self::WINDOWS_COLORS_SIZE );

				$this->colors_palette[] = new RGBA( $chunk[3], $chunk[2], $chunk[1] );
			}

			if ( $colors_count * self::WINDOWS_COLORS_SIZE + $win_header_size_calc >  $this->offset ) {
				throw new Exception( 'Invalid colors palette size.' );
			}
		}
	}

	private function set_amount(): void {
		switch ( $this->bits_per_pixel ) {
			case E_Bits_Per_Pixel::E_BPP_24:
				$this->amount = E_Bytes_Per_BPP::E_BYTES_PER_BPP_24;
				break;

			case E_Bits_Per_Pixel::E_BPP_32:
				$this->amount = E_Bytes_Per_BPP::E_BYTES_PER_BPP_32;
				break;
		}
	}

	private function set_padding() {
		$bits_per_pixel = $this->bits_per_pixel->get_as_int();
		$width = $this->width;
		$bytes_amount = $this->amount;

		switch ( $this->bits_per_pixel ) {
			// Credits: https://youtu.be/NcEE5xmpgQ0?t=648
			case E_Bits_Per_Pixel::E_BPP_24:
			case E_Bits_Per_Pixel::E_BPP_32:
				$this->padding = (
					( $bits_per_pixel - ( $width * $bytes_amount ) % $bits_per_pixel )
					% $bits_per_pixel
				);
				break;
		}
	}

}
