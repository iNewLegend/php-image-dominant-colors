<?php

$input = [
	'0x80' => '10000000',
	'0x40' => '01000000',
	'0x20' => '00100000',
	'0x10' => '00010000',
	'0x08' => '00001000',
	'0x04' => '00000100',
	'0x02' => '00000010',
	'0x01' => '00000001',
];

// Loop through $input.
foreach ( $input as $hex => $should_be_binary ) {
	// Convert $hex to a decimal value
	$decimal = hexdec($hex);

	// Convert $decimal to a binary string
	$binary = base_convert($decimal, 10, 2);

	// Add leading zeroes to the binary string if necessary
	$binary = str_pad($binary, 8, "0", STR_PAD_LEFT);

	// Assert that the binary string is the same as the expected binary string
	assert($binary === $should_be_binary);

	echo $binary . PHP_EOL;
}


