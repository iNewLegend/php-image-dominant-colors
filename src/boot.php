<?php

// Avoid in CLI.
if ( 'cli' !== php_sapi_name()) {
	require_once __DIR__ . '/utils/errors-to-json.php';

	\Utils\errors_to_json();
}

spl_autoload_register( function ( $class ) {
	$class_lowercase = strtolower( $class );

	$class_lowercase = str_replace( '_', '-', $class_lowercase );

	$class_path = __DIR__ . '/'. str_replace( '\\', '/', $class_lowercase ) . '.php';

	require_once $class_path;
} );

require_once __DIR__ . '/core/bmp/bmp-statistics.php';
require_once __DIR__ . '/core/bmp/bmp-colors.php';

