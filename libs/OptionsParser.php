<?php
/**
* This is a very basic option parser designed
* to quickly develop a plugin for Naemon using PHP
* 
* Daniel Ziegler <daniel@statusengine.org>
* 
* MIT License (MIT)
*
* Example usage:
* <?php
* use NookNaemonPlugin\OptionsParser;
* require_once "libs/OptionsParser.php";
* 
* $optionsParser = new NookNaemonPlugin\OptionsParser();
* $optionsParser->setOptions([ 
* 	'warning' => ['short' => 'w', 'desc' => 'The warning threshold e.g -w 50 or --warning 50'],
* 	'critical' => ['short' => 'c', 'desc' => 'The critical threshold e.g -w 100 or --critical 100'],
* ]);
* 
* $options = $optionsParser->parseOptions();
* print_r($options);
*
* CLI call:
* php5 test.php -w 50 --critical 100
* 
**/

namespace NookNaemonPlugin;
class OptionsParser{
	
	private $options = [];
	private $rawGivenOptions = [];
	private $givenOptions = [];
	
	public function setOptions($options){
		$this->options = $options;
	}
	
	public function parseOptions(){
		$options = $this->_prepareParamters();
		$givenOptions = [];
		foreach($_SERVER['argv'] as $key => $value){
			if($value == '--help' || $value == '-h'){
				$this->printHelp();
				exit(0);
			}
			
			if($key > 0){
				//Is current $value a option Name or a value of an option?
				if(array_key_exists($value, $options['longOptions'])){
					$optionName = $value;
				}elseif(array_key_exists($value, $options['shortOptions'])){
					$optionName = $options['shortOptions'][$value];
				}else{
					//This is not a option, its a value;
					$givenOptions[$optionName] = $value;
				}
			}
		}
		$this->rawGivenOptions = $givenOptions;
		
		$cleanOptions = [];
		foreach($this->rawGivenOptions as $key => $value){
			$key = str_replace('--', '', $key);
			$cleanOptions[$key] = $value;
		}
		
		$this->givenOptions = $cleanOptions;
		
		return $cleanOptions;
		
	}
	
	
	private function _prepareParamters(){
		$allOptions = [
			'longOptions'  => [],
			'shortOptions' => []
		];
		$this->options['help'] = ['short' => 'h', 'desc' => 'Print this help'];
		foreach($this->options as $longOption => $optionSettings){
			$allOptions['longOptions']['--'.$longOption] = null;
			if(isset($optionSettings['short'])){
				$allOptions['longOptions']['--'.$longOption] = '-'.$optionSettings['short'];
				
				$allOptions['shortOptions']['-'.$optionSettings['short']] = '--'.$longOption;
				
			}
		}
		return $allOptions;
	}
	
	private function printHelp(){
		foreach($this->options as $longOption => $optionsValue){
			echo '--'.$longOption."\t";
			if(isset($optionsValue['short'])){
				echo '-'.$optionsValue['short']."\t";
			}else{
				echo "\t";
			}
			
			if(isset($optionsValue['desc'])){
				echo $optionsValue['desc'];
			}
			
			echo PHP_EOL;
		}
	}
}