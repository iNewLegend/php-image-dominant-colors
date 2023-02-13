<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ResultCache\DefaultResultCache;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\TextUI\Configuration\Builder;
use PHPUnit\TextUI\Output\Facade as OutputFacade;
use PHPUnit\TextUI\ShellExitCodeCalculator;
use PHPUnit\TextUI\TestRunner;

require __DIR__ . '/../../src/boot.php';
require __DIR__ . '/../../vendor/autoload.php';

const DEFAULT_TARGET = '';

$target = $_SERVER['argv'][1] ?? DEFAULT_TARGET;

// Read filter.
$suffix_key = array_search( '--test-suffix', $_SERVER['argv'] );

// Add manual support for `--test-suffix`.
if ( $suffix_key ) {
	$test_suffix = [
		'file' => $_SERVER['argv'][ $suffix_key + 1 ],
		'path' => $_SERVER['argv'][ $suffix_key + 2 ],
	];

	$target = basename($test_suffix['path']);
}

// Add manual support for `--filter`.
$filter_key = array_search( '--filter', $_SERVER['argv'] );

if ( $filter_key ) {
	$test_filter = $_SERVER['argv'][ $filter_key + 1 ];

	$test_filter_factory =  new \PHPUnit\Runner\Filter\Factory();

	$test_filter_factory->addNameFilter( $test_filter );
}

switch ( $target ) {
	case 'integral':
		break;

	default:
		throw new \Exception( 'Invalid target: ' . $target );
}

$target_path = __DIR__ . DIRECTORY_SEPARATOR . $target . DIRECTORY_SEPARATOR;

if ( ! is_dir( $target_path ) ) {
	throw new \Exception( 'Invalid target: ' . $target );
}

$files = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $target_path ) );

$config_builder = new Builder();

$config = $config_builder->build( [
	'--colors',
	'--stop-on-error',
	'--display-warnings',
	'--stop-on-warning',
] );

// Initialize those to get the results later.
OutputFacade::init( $config );
TestResultFacade::init();
EventFacade::seal();

$test_runner = new TestRunner();

$result_cache = new DefaultResultCache();

$test_suite = TestSuite::empty( $target );

// Add all tests files in the target directory.
foreach ( $files as $file ) {
	if ( ! $file->isFile() ) {
		continue;
	}

	if ( isset( $test_suffix['file'] ) && $file->getFilename() !== $test_suffix['file'] ) {
		continue;
	}

	if ( ! preg_match( '/test\.php$/', $file->getFilename() ) ) {
		continue;
	}

	$test_suite->addTestFile( $file->getRealPath() );

	if ( isset( $test_filter_factory ) ) {
		$test_suite->injectFilter( $test_filter_factory );
	}
}

// Clear memory.
unset( $files );

// Each suite has its own bootstrap.
$suite_bootstrap_path = $target_path . '__bootstrap__.php';

echo "Loading suite bootstrap: '{$suite_bootstrap_path}'" . PHP_EOL;

require( $suite_bootstrap_path );

echo "Running suite: '{$test_suite->getName()}' that includes total of " . $test_suite->count() . ' tests...' . PHP_EOL;

// Run suite.
$test_runner->run( $config, $result_cache, $test_suite );

$result = TestResultFacade::result();

OutputFacade::printResult( $result, [] );

$shellExitCode = (new ShellExitCodeCalculator)->calculate(
	$config->failOnEmptyTestSuite(),
	$config->failOnRisky(),
	$config->failOnWarning(),
	$config->failOnIncomplete(),
	$config->failOnSkipped(),
	$result
);

exit( $shellExitCode );
