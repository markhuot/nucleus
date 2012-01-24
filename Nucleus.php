<?php

/**
 * Nucleus is a zero-conf ORM.
 */

// Load in the helpers.
require_once 'NucleusHelper.php';

/**
 * Set up the autoloader. This will be called anytime an undefined class within
 * the `\Nucleus` namespace is requested. We'll then require the appropriate
 * file, defining our class.
 * 
 * The pattern is to turn underscores (_) to directory separators mash
 * together namespaces. So, the class \Nucleus\Connection becomes
 * NucleusQuery.php and the class \Nucleus\Connection_Mysql can be found in the
 * NucleusConnection folder in a file named Mysql.php
 */
spl_autoload_register(function($class_name) {
	if (substr($class_name, 0, 7) === 'Nucleus') {
		$file_name = str_replace('_', '/', $class_name);
		$file_name = str_replace('\\' , '', $file_name);
		$path = dirname(__FILE__).'/'.$file_name.'.php';
		require_once $path;
	}
});

/**
 * The base class sits outside the `Nucleus` namespace. This is essentially a
 * dummy class without much purpose beyond proxying requests to the `Query`
 * object. This way you can utilize Nucleus without creating numerous
 * `Nucleus\Query` objects.
 * In other words, you can access Nucleus in two ways, the first is through
 * this class.
 * 
 *      $nucleus = new \Nucleus;
 *      $posts = $nucleus->get('posts');
 *      $users = $nucleus->get('users');
 * 
 * The second way is through the query object directly.
 * 
 *     $query1 = new \Nucleus\Query;
 *     $posts = $query1->get('posts');
 *     
 *     $query2 = new \Nucleus\Query;
 *     $users = $query2->get('users');
 * 
 */
class Nucleus {
	private $query;
	private static $config;

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
		if (!self::$config) {
			self::$config = array();
			if ($config = self::guess_codeigniter_config()) {
				self::$config = array_merge(self::$config, $config);
			}
			else if ($config = self::guess_default_config()) {
				self::$config = array_merge(self::$config, $config);
			}
		}

		if ($key === FALSE) {
			return self::$config;
		}

		return @self::$config[$key];
	}

	public static function guess_codeigniter_config() {
		if (!defined('APPPATH')) { return FALSE; }
		$config = array();
		$config['model_path'] = APPPATH.'models/';
		if (is_file($nucleus_config = APPPATH.'config/nucleus'.EXT)) {
			include $nucleus_config;
		}
		if (is_file($database_config = APPPATH.'config/database'.EXT)) {
			include $database_config;
			$config['dbtype'] = $db[$active_group]['dbdriver'];
			$config['dbhost'] = $db[$active_group]['hostname'];
			$config['dbname'] = $db[$active_group]['database'];
			$config['dbuser'] = $db[$active_group]['username'];
			$config['dbpass'] = $db[$active_group]['password'];
		}
		return $config;
	}

	public static function guess_default_config() {
		include rtrim(__DIR__, '/').'/config.php';
		return $config;
	}
}
