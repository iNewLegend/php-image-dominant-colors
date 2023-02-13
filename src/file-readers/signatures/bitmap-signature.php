<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace File_Readers\Signatures;

class Bitmap_Signature extends Base_Signature {

	public function get_file_extension(): string {
		return 'bmp';
	}

	public function get_magic_number(): string {
		return '424d';
	}

	public function get_magic_number_length(): int {
		return 2;
	}
}
