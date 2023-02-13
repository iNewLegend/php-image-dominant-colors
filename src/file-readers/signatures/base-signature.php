<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace File_Readers\Signatures;

abstract class Base_Signature {
	abstract public function get_file_extension(): string;

	abstract public function get_magic_number(): string;

	abstract public function get_magic_number_length(): int;
}
