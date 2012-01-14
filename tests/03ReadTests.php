<?php

class ReadTests extends Quiz {

	private $db;

	public function __construct() {
		$this->conn = new Nucleus\Connection('mysql:host=192.168.94.31;dbname=tmp', 'root', 'root');
		$this->db = new \Nucleus\Query();
	}

	public function readPosts() {
		return $this->db->get('posts')->size() == 5;
	}

	public function recordByIndex() {
		return get_class($this->db->get('posts')->record(0)) == 'Nucleus\Record';
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

	public function selectionTest() {
		$this->db->select('posts.title');
		$this->db->select('comments.comment');
		$this->db->from('posts');
		$this->db->join('comments');
		$result = $this->db->go();
		return !$result->record('user_id') && !$result->record(0)->comments->record('user_id');
	}

}
