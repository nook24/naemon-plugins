#!/usr/bin/php5
<?php
/**
* A Naemon notification script to send messages using pushover.net
* 
* Require php5-curl
*
* Daniel Ziegler <daniel@statusengine.org>
* 
* MIT License (MIT)
**/

use NookNaemonPlugin\OptionsParser;
use NookNaemonPlugin\Naemon;

require_once __DIR__."/../libs/Naemon.php";

if(!function_exists("curl_init")){
	echo 'Please install php5-curl first!'.PHP_EOL;
	NookNaemonPlugin\Naemon::exitUnknown();
}

require_once __DIR__."/../libs/OptionsParser.php";

$optionsParser = new NookNaemonPlugin\OptionsParser();
$optionsParser->setOptions([ 
	'type' => ['short' => 't', 'desc' => 'Set host for a host- and service for a service notification'],
	'user-key' => ['short' => 'k', 'desc' => 'Your user key you get from pushover.net'],
	'token' => ['desc' => 'The API Token for your App you get from pushover.net'],
	'output' => ['short' => 'm', 'desc' => 'The message you like to send'],
	'currentstate' => ['desc' => 'Ok, Warning, Critical etc.'],
	'hostname' => ['desc' => 'The hostname'],
	'servicename' => ['desc' => 'The name of the service you want notify']
]);

$options = $optionsParser->parseOptions();

print_r($options);

$requiredKeys = ['type', 'user-key', 'token', 'output'];
$exit = false;
foreach($requiredKeys as $requiredKey){
	if(!isset($options[$requiredKey])){
		echo 'Missing parameter "'.$requiredKey.'"'.PHP_EOL;
		$exit = true;
	}
}

if($exit){
	NookNaemonPlugin\Naemon::exitUnknown();
}

if($options['type'] == 'host'){
	$message = $options['hostname'].' is '.$options['currentstate'].'!'.PHP_EOL;
	$message.= 'Output: '.$options['output'].PHP_EOL;
	$message.= 'Time: '. date('d.m.y H:i:s');
}else{
	$message = $options['hostname'].'/'.$options['servicename'].' is '.$options['currentstate'].'!'.PHP_EOL;
	$message.= 'Output: '.$options['output'].PHP_EOL;
	$message.= 'Time: '. date('d.m.y H:i:s');
}

$ch = curl_init();
curl_setopt_array($ch, [
	CURLOPT_SAFE_UPLOAD => true,
	CURLOPT_URL => 'https://api.pushover.net/1/messages.json',
	CURLOPT_POSTFIELDS => [
		'token' => $options['token'],
		'user' => $options['user-key'],
		'message' => $message
	]
]);

$return = curl_exec($ch);
curl_close($ch);

NookNaemonPlugin\Naemon::exitOk();