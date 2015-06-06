<?php
/**
* This is a static class to generate exit codes
* Output with perofrmance data for Naemon
* 
* Daniel Ziegler <daniel@statusengine.org>
* 
* MIT License (MIT)
**/

namespace NookNaemonPlugin;
class Naemon{
	
	private static $output = null;
	private static $perfdata = null;
	
	public function setOutput($text = '', $values = []){
		self::$output = vsprintf($text, $values);
	}
	
	public function setPerfdata($perfdata = []){
		$additionalKeys = ['warning', 'critical', 'min', 'max'];
		self::$perfdata = '';
		foreach($perfdata as $datasource){
			$_perfdata = $datasource['label'].'=';
			$_perfdata .= $datasource['value'].';';
			
			$i = 1;
			foreach($additionalKeys as $key){
				if(isset($datasource[$key])){
					$_perfdata .= $datasource[$key];
					if($i < 4){
						$_perfdata .= ';';
					}
				}else{
					if($i < 4){
						$_perfdata .= ';';
					}
				}
				$i++;
			}
			self::$perfdata .= $_perfdata.' ';
		}
	}
	
	public function printOutput(){
		echo self::$output;
		if(self::$perfdata !== null){
			echo '|';
			echo self::$perfdata;
		}
		echo PHP_EOL;
	}
	
	public function parseRangeParameter($parameter){
		$result = explode(':', $parameter);
		
		$invert = false;
		
		if(preg_match('/^@/', $parameter)){
			$invert = true;
		}
		
		if(sizeof($result) == 2){
			return [
				'range' => true,
				'min' => $result[0],
				'max' => $result[1],
				'invert' => $invert
			];
		}
		
		return [
			'range' => false,
			'min' => $result[0],
			'max' => null,
			'insert' => $invert
		];
	}
	
	public static function exitOk(){
		exit(0);
	}
	
	public static function exitWarning(){
		exit(1);
	}
	
	public static function exitCritical(){
		exit(2);
	}
	
	public static function exitUnknown(){
		exit(3);
	}
}