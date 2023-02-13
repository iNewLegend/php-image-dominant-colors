<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */
require_once __DIR__ . '/../src/boot.php';

use File_Readers\Base_File_Reader;
use File_Readers\Bitmap_File_Reader;

use function Core\BMP\get_bmp_statistics;

function get_file_statistics( Base_File_Reader $file_reader ): array {
	$result = [
		'success' => false,
		'message' => 'Failed to get file statistics',
	];

	$args = [
		'max_colors' => 0,
		'colors_merge_sensitivity' => 0,
	];

	$args = array_merge( $args, $_POST );

	switch ( $file_reader->get_signature()->get_file_extension() ) {
		case 'bmp':
			/** @var Bitmap_File_Reader $file_reader */
			$result = get_bmp_statistics( $file_reader, $args );
			break;

		default:
			$result['message'] = 'Unsupported file type';
			break;
	}

	return $result;
}


function handle_file( $file_data ): array {
	$file_reader = null;

	switch ( $file_data['type'] ) {
		case 'image/bmp':
			$file_reader = new Bitmap_File_Reader( $file_data['tmp_name'] );
			break;

			break;

		default:
			trigger_error( '[' . __FUNCTION__ . '] ' . 'Unsupported file type: ' . $file_data['type'] );
	}

	if ( ! $file_reader->open() ) {
		return [
			'success' => false,
			'message' => 'Could not open file',
		];
	}

	$file_reader->read_data();

	$file_reader->close();

	return get_file_statistics( $file_reader );
}

// Ensuring the frontend sent file.
if ( ! count( $_FILES ) || ! is_uploaded_file( $_FILES['file']['tmp_name'] ) ) {
	exit( json_encode( [
		'success' => false,
		'message' => 'File not uploaded correctly',
	] ) );
}

echo json_encode( handle_file( $_FILES['file'] ) );
