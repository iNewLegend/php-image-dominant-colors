<?php

function reverse_and_print_multi_content_string( $content ) {
	$array = [];
	$lines = explode( PHP_EOL, $content );

	// Build multidimensional array, from output.
	for( $y = 0 ; $y < count($lines) ; ++$y ) {
		$line = $lines[ $y ];

		$line = str_split($line, 1);

		for( $x = 0 ; $x < count($line) ; ++$x ) {
			$array[ $y ][ $x ] = $line[ $x ];
		}
	}

	// `bmp` stores pixels in reverse order.
	$array = array_reverse( $array );

	// Build multidimensional array, from output.
	for ( $y = 0; $y < count($array); ++$y ) {
		for ( $x = 0; $x < count($array[ $y ]); ++$x ) {
			echo $array[ $y ][ $x ];
		}

		echo PHP_EOL;
	}
}
