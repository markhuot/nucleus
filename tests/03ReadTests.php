<?php

class ReadTests extends Quiz {

	private $db;

	public function __construct() {
		$this->db = new \Nucleus\Query('sqlite::memory:');
	}

	public function readPosts() {
		return $this->db->get('posts')->size() == 5;
	}

	public function recordByIndex() {
		return get_class($this->db->get('posts')->record(0)) == 'Nucleus_record';
	}

	public function firstRecordByIndex() {
		return $this->db->get('posts')->record(0)->title == 'Let\'s save the world';
	}

	public function firstRecordByKey() {
		return $this->db->get('posts')->record('title') == 'Let\'s save the world';
	}

	public function iterationTest() {
		$posts = $this->db->get('posts');
		$i = 0;
		foreach($posts as $post) {
			$i++;
		}
		return $i == $posts->size();
	}

}
