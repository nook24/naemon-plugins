<?php
/**
* This file contains some test cases and examples for
* the PHP classes of libs folder...
* 
* Daniel Ziegler <daniel@statusengine.org>
* 
* MIT License (MIT)
**/

use NookNaemonPlugin\OptionsParser;
use NookNaemonPlugin\Naemon;
use NookNaemonPlugin\GPIO;

//Test option praser

require_once "libs/OptionsParser.php";

echo "Test option/argument parsing...".PHP_EOL;
$optionsParser = new NookNaemonPlugin\OptionsParser();
$optionsParser->setOptions([ 
	'warning' => ['short' => 'w', 'desc' => 'The warning threshold e.g -w 50 or --warning 50'],
	'critical' => ['short' => 'c', 'desc' => 'The critical threshold e.g -w 100 or --critical 100'],
]);

$options = $optionsParser->parseOptions();
print_r($options);

//Parse thresholds
require_once "libs/Naemon.php";

//Example call php5 tests.php -w 50:100 -c 200
// or invert a parameter for example: -w @50:100
echo "Test thresholds parsing...".PHP_EOL;
if(isset($options['warning'])){
	$warning = NookNaemonPlugin\Naemon::parseRangeParameter($options['warning']);
	print_r($warning);
}

if(isset($options['critical'])){
	$critical = NookNaemonPlugin\Naemon::parseRangeParameter($options['critical']);
	print_r($critical);
}

//Test Naemon output generation (using hardocded thresholds)
echo "Testing Naemon output generation (using hardcoded thresholds)...".PHP_EOL;
$testValue = rand(10, 200);
$string = 'Hello world';
NookNaemonPlugin\Naemon::setOutput('Put a float here %f and just a string here %s', [$testValue, $string]);
NookNaemonPlugin\Naemon::printOutput();

echo "Testing Naemon output + perfdata generation (using hardcoded thresholds)...".PHP_EOL;
NookNaemonPlugin\Naemon::setOutput('Put a float here %f, a integer here %d and just a string here %s', [$testValue, 5, $string]);
NookNaemonPlugin\Naemon::setPerfdata([
	[
		'label' => 'Test', //Required parameter!!!
		'value' => $testValue, //Required parameter!!!
		'warning' => 50,
		'critical' => 100,
		'min' => 20,
		'max' => 500
	],
	[
		'label' => 'int',
		'value' => 5
	]
]);
NookNaemonPlugin\Naemon::printOutput();


//Test GPIO stuff
echo "Test GPIO stuff...".PHP_EOL;
require_once "libs/GPIO.php";

$GPIO = $optionsParser = new NookNaemonPlugin\GPIO(18);

if($GPIO->check()){
	$value = $GPIO->read();
	var_dump($value);
}else{
	echo $GPIO->gpioMissing();
	NookNaemonPlugin\Naemon::exitUnknown();
}
