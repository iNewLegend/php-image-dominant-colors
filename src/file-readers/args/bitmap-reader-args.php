<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace File_Readers\Args;

use Utils\Args_Object;

class Bitmap_Reader_Args extends Args_Object {

	protected function get_default_args(): array {
		return [
			'bypass_compression_check' => false,    // Bypass the compression check.
			'bypass_colors_used_check' => true,     // Bypass the colors used check, if not `colors_used` is negative.
			'get_file_size_manually' => true,       // Get the file size manually, if the file size is not available.
		];
	}
}
