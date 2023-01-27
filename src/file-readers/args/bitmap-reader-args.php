<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace File_Readers\Args;

use Utils\Args_Object;

class Bitmap_Reader_Args extends Args_Object {

	protected function get_default_args(): array {
		return [
			'bypass_compression_check' => false,
			'bypass_colors_used_check' => true,
			'get_file_size_manually' => true,
		];
	}
}
