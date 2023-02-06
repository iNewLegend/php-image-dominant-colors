<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace Utils;

// Move errors to frontend, except fatal.
function api_error( $e1 = null, $e2 = null, $e3 = null, $e4 = null ) {
	$error = error_get_last();

	if ( ! $error && ! $e1 ) {
		return false;
	}

	if ( $e1 && $e2 && $e3 && $e4 ) {
		$error = [
			'type' => $e1,
			'message' => $e2,
			'file' => $e3,
			'line' => $e4,
		];
	} else if ( $e1 instanceof \Error || $e1 instanceof \Exception ) {
		$error = [
			'type' => $e1->getCode(),
			'message' => $e1->getMessage(),
			'file' => $e1->getFile(),
			'line' => $e1->getLine(),
			'trace' => $e1->getTrace(),
		];
	} else {
		$error = [
			'success' => false,
			'type' => $error['type'],
			'message' => $error['message'],
			'file' => $error['file'],
			'line' => $error['line'],
		];
	}

	exit( json_encode( $error, JSON_PRETTY_PRINT ) );
}

function errors_to_json(): void {
	header('Content-Type: application/json; charset=utf-8');

	set_error_handler( '\Utils\api_error', E_ALL );
	set_exception_handler( '\Utils\api_error' );
}

