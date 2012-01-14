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
	private static $config = array(
		'model_path' => FALSE
	);

	public function __construct() {
		$this->query = new \Nucleus\Query();
	}

	public function __call($method, $args) {
		return call_user_func_array(
			array($this->query, $method),
			$args
		);
	}

	public static function config($key=FALSE) {
		if ($config = self::guess_default_config()) {
			self::$config = array_merge(self::$config, $config);
		}
		if ($config = self::guess_codeigniter_config()) {
			self::$config = array_merge(self::$config, $config);
		}
		return @self::$config[$key];
	}

	public static function guess_default_config() {
		include rtrim(__DIR__, '/').'/config.php';
		return $config;
	}

	public static function guess_codeigniter_config() {
		if (!defined('APPPATH')) { return FALSE; }
		include APPPATH.'config/nucleus'.EXT;
		return $config;
	}
}
