<?php
/**
* A PHP class to communicate with an AVR-NET-IO module
* 
* Daniel Ziegler <daniel@statusengine.org>
* 
* MIT License (MIT)
**/

class AVR_NET_IO{
	
	function __construct($ip, $port){
		$this->ip = $ip;
		$this->port = $port;
		$this->relais = array(
								1 => false,
								2 => false,
								3 => false,
								4 => false,
								5 => false,
								6 => false,
								7 => false,
								8 => false
							);
		$this->connection = null;
		$this->timeout = 1;
		$this->connect();
	}
	
	function test(){
		if(is_resource($this->connection)){
			fwrite($this->connection, "GETIP");
			$this->checkResponse('ip');
			return true;
		}
		return false;
	}
	
	function connect(){
		$this->connection = @fsockopen($this->ip, $this->port, $errno, $errstr, $this->timeout);
	}
	
	function on($relais){
		$this->send($relais, true);
	}
	
	function off($relais){
		$this->send($relais, false);
	}
	
	function off_all(){
		foreach($this->relais as $single_relais => $state){
			$this->off($single_relais);
		}
	}
	
	function on_all(){
		foreach($this->relais as $single_relais => $state){
			$this->on($single_relais);
		}
	}
	
	function status($relais){
		fwrite($this->connection, "GETSTATUS\n");
		$i = 1;
		foreach(array_reverse(str_split($this->response())) as $state){
			if(!is_numeric($state)){
				continue;
			}
			$this->relais[$i] = (bool)$state;
			$i++;
		}
		return $this->relais[$relais];
	}
	
	function send($relais, $state){
		fwrite($this->connection, "SETPORT ".$relais.".".(int)$state."\n");
		if($this->checkResponse()){
			$this->relais[$relais] = $state;
		}
	}
	
	function response(){
		return trim(fgets($this->connection, 1024));
	}
	
	function checkResponse($type = null){
		switch($type){
			case 'ip':
				if(filter_var($this->response(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
					return true;
				}
				break;
			
			default:
				if($this->response() == "ACK"){
					return true;
				}
				break;
		}
		return false;
	}
}