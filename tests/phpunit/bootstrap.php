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

	if ( ! preg_match( '/test\.php$/', $file->getFilename() ) ) {
		continue;
	}

	$test_suite->addTestFile( $file->getRealPath() );
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
