#!/usr/bin/php5
<?php
/**
* A PHP Plugin I use to check my fish tank for leaks
* 
* Require a AVR_NET_IO module
*
* Daniel Ziegler <daniel@statusengine.org>
* 
* Usage: php5 plugins/check_fishtank_leak.php -H 192.168.0.10 -p 50290 -w 800 -c 400 --analogport 1 (1 to 4)
*
* MIT License (MIT)
**/

use NookNaemonPlugin\OptionsParser;
use NookNaemonPlugin\Naemon;

$pcsensorBinaryPath = __DIR__ . '/../../TEMPerl/pcsensor';

require_once __DIR__."/../libs/Naemon.php";
require_once __DIR__."/../libs/OptionsParser.php";
require_once __DIR__."/../libs/AVR_NET_IO.php";

$optionsParser = new NookNaemonPlugin\OptionsParser();
$optionsParser->setOptions([ 
	'warning' => ['short' => 'w', 'desc' => 'Warning value e.g. 800'],
	'critical' => ['short' => 'c', 'desc' => 'Critical value e.g. 400'],
	'hostaddress' => ['short' => 'H', 'desc' => 'The IP address of the AVR NET IO module'],
	'port' => ['short' => 'p', 'desc' => 'The port of the AVR NET IO module e.g. 50290'],
	'analogport' => ['desc' => 'The analog input port you like to query']
]);

$options = $optionsParser->parseOptions();

$AVR = new AVR_NET_IO($options['hostaddress'], $options['port']);
$result = $AVR->analog($options['analogport']);

if(is_numeric($result)){
	if($result <= $options['critical']){
		NookNaemonPlugin\Naemon::setOutput('Critical: Fish tank leak detected!');
		NookNaemonPlugin\Naemon::printOutput();
		NookNaemonPlugin\Naemon::exitCritical();
	}

	if($result <= $options['warning']){
		NookNaemonPlugin\Naemon::setOutput('Warnign: Fish tank maybe leak!');
		NookNaemonPlugin\Naemon::printOutput();
		NookNaemonPlugin\Naemon::exitWarning();
	}

	NookNaemonPlugin\Naemon::setOutput('Ok: No leak detected.');
	NookNaemonPlugin\Naemon::printOutput();
	NookNaemonPlugin\Naemon::exitOk();
}

NookNaemonPlugin\Naemon::setOutput('Unknown: %2', [$value]);
NookNaemonPlugin\Naemon::printOutput();
NookNaemonPlugin\Naemon::exitUnknown();

