#!/usr/bin/php5
<?php
/**
* A PHP Plugin to get results of a TEMPer sensor
* 
* Require a TEMPer sensor and https://github.com/padelt/pcsensor-temper/
* chmod u+s TEMPerl/pcsensor that naemon user can execute the tool as root
* Daniel Ziegler <daniel@statusengine.org>
* 
* Usage: php5 plugins/check_TEMPer.php -w 24:27 -c 22:33
*
* MIT License (MIT)
**/

use NookNaemonPlugin\OptionsParser;
use NookNaemonPlugin\Naemon;

$pcsensorBinaryPath = __DIR__ . '/../../TEMPerl/pcsensor';

require_once __DIR__."/../libs/Naemon.php";
require_once __DIR__."/../libs/OptionsParser.php";

$optionsParser = new NookNaemonPlugin\OptionsParser();
$optionsParser->setOptions([ 
	'warning' => ['short' => 'w', 'desc' => 'Warning temperature range'],
	'critical' => ['short' => 'c', 'desc' => 'Critical temperature range']
]);

$options = $optionsParser->parseOptions();

if(isset($options['warning'])){
	$warning = NookNaemonPlugin\Naemon::parseRangeParameter($options['warning']);
}

if(isset($options['critical'])){
	$critical = NookNaemonPlugin\Naemon::parseRangeParameter($options['critical']);
}

exec($pcsensorBinaryPath, $output);


if(isset($output[0]) && is_numeric($output[0])){
	$value = $output[0];
	
	NookNaemonPlugin\Naemon::setPerfdata([
		[
			'label' => 'Temperature',
			'value' => $value.'C'
		],
	]);
	
	if($value >= $warning['min'] && $value <= $warning['max']){
		NookNaemonPlugin\Naemon::setOutput('Ok: Fish tank temperature: %s°C', [$value]);
		NookNaemonPlugin\Naemon::printOutput();
		NookNaemonPlugin\Naemon::exitOk();
	}
	
	if($value <= $critical['min'] || $value >= $critical['max']){
		NookNaemonPlugin\Naemon::setOutput('Critical: Fish tank temperature: %s°C', [$value]);
		NookNaemonPlugin\Naemon::printOutput();
		NookNaemonPlugin\Naemon::exitCritical();
	}
	
	if($value <= $warning['min'] || $value >= $warning['max']){
		NookNaemonPlugin\Naemon::setOutput('Warning: Fish tank temperature: %s°C', [$value]);
		NookNaemonPlugin\Naemon::printOutput();
		NookNaemonPlugin\Naemon::exitWarning();
	}
}

NookNaemonPlugin\Naemon::exitUnknown();