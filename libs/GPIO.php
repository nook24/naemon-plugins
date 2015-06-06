<?php
/**
* A very baisc class to read 1 or 0 from a GPIO
* interface
* 
* Daniel Ziegler <daniel@statusengine.org>
* 
* MIT License (MIT)
**/

namespace NookNaemonPlugin;
class GPIO{
	
	private $pin = 0;
	
	public function __construct($pin = 0){
		$this->pin = $pin;
	}
	
	public function gpioMissing(){
		return 'Could not open GPIO, file not found. Try "sudo echo '.$this->pin.' > /sys/class/gpio/export"'.PHP_EOL;
	}
	
	public function check(){
		if(file_exists('/sys/class/gpio/gpio'.$this->pin.'/value')){
			return true;
		}
		
		return false;
	}
	
	public function read(){
		$fd = fopen('/sys/class/gpio/gpio'.$this->pin.'/value', 'r');
		$fds = [$fd];
		$read = null;
		$write = null;
		stream_select($read, $write, $fds, 60000);
		$result = trim(fread($fd, 1024));
		fseek($fd, 0, SEEK_SET);
	}
}