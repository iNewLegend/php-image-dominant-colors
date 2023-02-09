<?php

// Avoid in CLI.
if ( 'cli' !== php_sapi_name()) {
	require_once __DIR__ . '/utils/errors-to-json.php';

	\Utils\errors_to_json();
}

spl_autoload_register( function ( $class ) {
	$class_lowercase = strtolower( $class );

	$class_lowercase = str_replace( '_', '-', $class_lowercase );

	$clas_file = __DIR__ . '/'. str_replace( '\\', '/', $class_lowercase ) . '.php';

	if ( is_file( $clas_file ) ) {
		require_once $clas_file;
	}
} );

require_once __DIR__ . '/core/bmp/bmp-statistics.php';
require_once __DIR__ . '/core/bmp/bmp-colors.php';

