<?php

class ModelTests extends Quiz {
	
	public function __construct() {
		define('APPPATH', __DIR__.'/../../../');
		define('EXT', '.php');
		$this->db = new Nucleus\Query(new Nucleus\Connection(
			'mysql:host=192.168.94.31;dbname=tmp',
			'root',
			'root'
		));
	}

	public function modelPath() {
		return Nucleus::config('model_path') !== FALSE;
	}

	public function modelLoaded() {
		return Nucleus\Model::for_table('posts');
	}

}