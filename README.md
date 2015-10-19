# nook24-naemon-plugins
Is a micro framework to create Naemon plugins using php.

If you are working with Naemon you nearly need to create a custom Plugin every day. To hack a quick and dirty plugin is not that problem but things like parameters, build the output or implement --help eats time. For this reaseon I hacked this framework.

Please take a look at tests.php to see how everything works...

# Libraries
All Libraries are placed in libs folder:
##OptionParser
**Basic usage:**
```php
<?php
use NookNaemonPlugin\OptionsParser;

require_once "libs/OptionsParser.php";

$optionsParser = new NookNaemonPlugin\OptionsParser();
$optionsParser->setOptions([ 
	'warning' => ['short' => 'w',  'desc' => 'The warning threshold e.g -w 50 or --warning 50'],
	'critical' => ['short' => 'c', 'desc' => 'The critical threshold e.g -w 200 or --critical 200'],
]);

$options = $optionsParser->parseOptions();
print_r($options);
/*
Call: php5 tests.php --warning 50 -c 200
Array
(
    [warning] => 50
    [critical] => 200
)
*/
```
**Range parameters:** (you can invert using @ e.g. @50:100)
```php
<?php
use NookNaemonPlugin\OptionsParser;
use NookNaemonPlugin\Naemon;

require_once "libs/OptionsParser.php";
require_once "libs/Naemon.php";

$optionsParser = new NookNaemonPlugin\OptionsParser();
$optionsParser->setOptions([ 
	'warning' => ['short' => 'w',  'desc' => 'The warning threshold e.g -w 50:100 or --warning 50:100'],
	'critical' => ['short' => 'c', 'desc' => 'The critical threshold e.g -w 200 or --critical 200'],
]);

$options = $optionsParser->parseOptions();
if(isset($options['warning'])){
	$warning = NookNaemonPlugin\Naemon::parseRangeParameter($options['warning']);
	print_r($warning);
}

if(isset($options['critical'])){
	$critical = NookNaemonPlugin\Naemon::parseRangeParameter($options['critical']);
	print_r($critical);
}
/*
Call: php5 tests.php --warning 50:100 -c 200
Array
(
    [range] => true
    [min] => 50
    [max] => 100
    [invert] => 
)
Array
(
    [range] => false
    [min] => 200
    [max] => 
    [insert] => 
)
*/
```

**Automatic generated --help:**
```php
$optionsParser->setOptions([ 
	'warning' => ['short' => 'w', 'desc' => 'The warning threshold e.g -w 50 or --warning 50'],
	'critical' => ['short' => 'c', 'desc' => 'The critical threshold e.g -w 100 or --critical 100'],
]);
```

```bash
php5 tests.php --help
--warning	-w	The warning threshold e.g -w 50 or --warning 50
--critical	-c	The critical threshold e.g -w 100 or --critical 100
--help	-h	Print this help
```
You can use --help and -h

##Naemon
The Naemon class can be used to generate output and perfdata output

**Output only example:**
```php
<?php
use NookNaemonPlugin\Naemon;

require_once "libs/Naemon.php";
NookNaemonPlugin\Naemon::setOutput('Put a integer here %d and just a string here "%s"', [50, 'Hello world']);
NookNaemonPlugin\Naemon::printOutput();

/*
Call: php5 tests.php
Put a integer here 50 and just a string here "Hello world"
*/
```

**Output + Perfdata example:**
```php
<?php
use NookNaemonPlugin\Naemon;

require_once "libs/Naemon.php";
NookNaemonPlugin\Naemon::setOutput('Put a float here %f, a integer here %d and just a string here "%s"', [1.337, 5, 'Hello World']);
NookNaemonPlugin\Naemon::setPerfdata([
	[
		'label' => 'Test',     //Required parameter!!!
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

/*
Call: php5 tests.php
Put a float here 1.337000, a integer here 5 and just a string here "Hello World"|Test=;50;100;20;500 int=5;;;;
*/
```

**Exit codes:**

You can simply use exit(0) to submit your state, but this makes your plugins source code maybe unreadable for people that are not as familiar with Naemon as you.
```php
<?php
use NookNaemonPlugin\Naemon;
require_once "libs/Naemon.php";
NookNaemonPlugin\Naemon::exitOk();
NookNaemonPlugin\Naemon::exitWarning();
NookNaemonPlugin\Naemon::exitCritical();
NookNaemonPlugin\Naemon::exitUnknown();
```

##GPIO
This very very basic GPIO class can be used to read 1 or 0 form a GPIO interface
```php
<?php
use NookNaemonPlugin\Naemon;
use NookNaemonPlugin\GPIO;

require_once "libs/Naemon.php";
require_once "libs/GPIO.php";

$GPIO = $optionsParser = new NookNaemonPlugin\GPIO(18);

if($GPIO->check()){
	$value = $GPIO->read();
	var_dump($value);
}else{
	echo $GPIO->gpioMissing();
	NookNaemonPlugin\Naemon::exitUnknown();
}
```

##AVR_NET_IO
You can use this call to AVR_NET_IO module

#Plugins
**notify_pushover.php**

Is a plugin you can use to send Naemon alerts using Pushover.net

Usage:
```
define command{
    command_name    host-notify-pushover
    command_line    $USER1$/nook24-naemon-plugins/plugins/notify_pushover.php --type host --user-key $YOUR_USER_KEY$ --token $YOUR_TOKEN$ --output "$HOSTOUTPUT$" --currentstate "$HOSTSTATE$" --hostname "$HOSTNAME$"
}
define command{
    command_name    service-notify-pushover
    command_line    $USER1$/nook24-naemon-plugins/plugins/notify_pushover.php --type service --user-key $YOUR_USER_KEY$ --token $YOUR_TOKEN$ --output "$SERVICEOUTPUT$" --currentstate "$SERVICESTATE$" --hostname "$HOSTDISPLAYNAME$" --servicename "$SERVICEDESC$"
}
```

**check_TEMPer.php**

A PHP wrapper plugin for [pcsensor](https://github.com/padelt/pcsensor-temper/) TEMPer sensors

Usage:

```
define command{
    command_name    check_TEMPer
    command_line    $USER1$/nook24-naemon-plugins/plugins/check_TEMPer.php -w 24:27 -c 22:33
    ;command_line    $USER1$/nook24-naemon-plugins/plugins/check_TEMPer.php -w $ARG1$ -c $ARG2$
}
```

**check_fishtank_leak.php**

A plugin to check via AVR_NET_IO module if my fish tank starts to leak

Usage:

```
define command{
    command_name    check_fishtank_leak
    command_line    $USER1$/nook24-naemon-plugins/plugins/check_fishtank_leak.php -H 192.168.0.10 -p 50290 -w 800 -c 400 --analogport 1
    ;command_line    $USER1$/nook24-naemon-plugins/plugins/check_fishtank_leak.php -H $HOSTADDRESS$ -p 50290 -w $ARG1$ -c $ARG2$ --analogport $ARG3$ (1 to 4)
}
```

**check_mailq.php**

A PHP Plugin I use to check my postfix mail queue

Usage:

```
define command{
    command_name    check_mailq
    command_line    $USER1$/nook24-naemon-plugins/plugins/check_mailq.php -w 30 -c 50
}
```

#Installation
To use the plugin skeleton or one of the provided plugins just clone this repository to your plugins location (libexec folder).

# License
MIT License
