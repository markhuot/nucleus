<?php

require_once 'NucleusHelper.php';

spl_autoload_register(function($class_name) {
	if (substr($class_name, 0, 7) === 'Nucleus') {
		$file_name = str_replace('_', '/', $class_name);
		$file_name = str_replace('\\' , '', $file_name);
		$path = dirname(__FILE__).'/'.$file_name.'.php';
		require_once $path;
	}
});

class Nucleus {
	
	private $query;

	public function __construct($host=NULL, $user=NULL, $pass=NULL, $name=NULL) {
		$this->query = new \Nucleus\Query($host=NULL, $user=NULL, $pass=NULL, $name=NULL);
	}

	public function __call($method, $args) {
		return call_user_func_array(
			array($this->query, $method),
			$args
		);
	}
}
