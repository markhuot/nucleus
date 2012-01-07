<?php

require_once 'Nucleus_helper.php';

spl_autoload_register(function($class_name) {
	if (substr($class_name, 0, 8) === 'Nucleus') {
		$file_name = str_replace('\', '_', $class_name);
		require_once dirname(__FILE__).'/'.$file_name.'.php';
	}
});

class Nucleus {
	
	private $query;

	public function __construct($host=NULL, $user=NULL, $pass=NULL, $name=NULL) {
		$this->query = new Nucleus_query($host=NULL, $user=NULL, $pass=NULL, $name=NULL);
	}

	public function __call($method, $args) {
		return call_user_func_array(
			array($this->query, $method),
			$args
		);
	}
}
