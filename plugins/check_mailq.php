#!/usr/bin/php5
<?php
/**
* A PHP Plugin I use to check my postfix mail queue
* 
* Daniel Ziegler <daniel@statusengine.org>
* 
* Usage: php5 plugins/check_mailq.php -w 30 -c 50
*
* MIT License (MIT)
**/

use NookNaemonPlugin\OptionsParser;
use NookNaemonPlugin\Naemon;

require_once __DIR__."/../libs/Naemon.php";
require_once __DIR__."/../libs/OptionsParser.php";

$optionsParser = new NookNaemonPlugin\OptionsParser();
$optionsParser->setOptions([ 
	'warning' => ['short' => 'w', 'desc' => 'Mails in queue warning  30'],
	'critical' => ['short' => 'c', 'desc' => 'Mails in queue critical 50'],
]);

$options = $optionsParser->parseOptions();

exec('mailq | tail -n 1 | cut -d " " -f 5', $output, $returncode);

$count = 0;
if(isset($output[0]) && is_numeric($output[0])){
	$count = $output[0];
}

NookNaemonPlugin\Naemon::setPerfdata([
    [
        'label' => 'mailq',
        'value' => $count,
        'warning' => $options['warning'],
        'critical' => $options['critical'],
    ],
]);

if($count >= $options['critical']){
	NookNaemonPlugin\Naemon::setOutput('Critical: Mails in queue %d', [$count]);
	NookNaemonPlugin\Naemon::printOutput();
	NookNaemonPlugin\Naemon::exitCritical();
}

if($count >= $options['warning']){
	NookNaemonPlugin\Naemon::setOutput('Warnign: Mails in queue %d', [$count]);
	NookNaemonPlugin\Naemon::printOutput();
	NookNaemonPlugin\Naemon::exitWarning();
}

if($count == 0){
	NookNaemonPlugin\Naemon::setOutput('Ok: Mail queue is empty.');
}else{
	NookNaemonPlugin\Naemon::setOutput('Ok: Mails in queue %d', [$count]);
}
NookNaemonPlugin\Naemon::printOutput();
NookNaemonPlugin\Naemon::exitOk();


