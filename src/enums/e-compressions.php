<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Enums;

use Utils\Enum;

class E_Compressions extends Enum {
	const E_COMPRESSIONS_BI_RGB = '0';    // No compression - straight BGR data.
	const E_COMPRESSIONS_BI_RLE8 = '1';   // 8-bit run-length compression.
	const E_COMPRESSIONS_BI_RLE4 = '2';   // 4-bit run-length compression.
}

class E_Compressions_Modes extends Enum {
	// Turn on the encoded mode.
	const E_COMPRESSIONS_CODES_ENCODED_MODE = '0';

	public function is_encoded(): bool {
		return static::E_COMPRESSIONS_CODES_ENCODED_MODE === $this->get_value();
	}

	/*
	 * `byte_1` pixels are to be drawn. The 1st, 3rd, 5th, etc.
	 * pixel colours is the high-order 4 bits of `byte_2`, the
	 * 2nd, 4th, 6th, etc. pixel colours is in the
	 * low-order 4 bits of `byte_2`. If both colours are the
	 * same, it results in just n pixels of colour `byte_2`.
	 * `byte_1` is also twice (or twice + 1) the number of byte
	 * `byte_2` output.
	 * Be careful not to output an n of zero, as that is an end-of-line.
	 */
	public function is_uncompressed(): bool {
		return (int)$this->get_value() > (int)static::E_COMPRESSIONS_CODES_ENCODED_MODE;
	}
}

class E_Compression_Encoded_Modes extends Enum {
	// End of line.
	const E_COMPRESSIONS_CODES_EOL = '0';

	// End of bitmap.
	const E_COMPRESSIONS_CODES_EOB = '1';

	// Delta. The following 2 bytes define an unsigned offset in x and y direction (y being up) The skipped pixels should get a color zero.
	const E_COMPRESSIONS_CODES_DELTA = '2';

	// Absolute mode is signaled by the first byte in the pair being set to zero and the second byte to a value between 0x03 and 0xFF.
	const E_COMPRESSIONS_CODES_ABSOLUTE = '3';

	public function is_absolute(): bool {
		return (int)$this->get_value() >= (int)static::E_COMPRESSIONS_CODES_ABSOLUTE;
	}
}

