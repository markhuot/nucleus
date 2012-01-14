<?php

class ModelTests extends Quiz {
	
	public function __construct() {
		$this->db = new Nucleus\Query(new Nucleus\Connection(
			'mysql:host=192.168.94.31;dbname=tmp',
			'root',
			'root'
		));
	}

	public function modelPath() {
		return Nucleus::config('model_path') !== FALSE;
	}

	public function anonymousModelLoaded() {
		$model = Nucleus\Model::for_table('categories');
		return get_parent_class($model) == 'Nucleus\Model';
	}

	public function modelLoaded() {
		$model = Nucleus\Model::for_table('posts');
		return get_class($model) == 'PostModel' && 
		       get_parent_class($model) == 'Nucleus\Model';
	}

	public function joinFromModel() {
		$this->db->from('posts');
		$this->db->join('tagged');
		$posts = $this->db->go();
	}

}