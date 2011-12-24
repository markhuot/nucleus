<?php

class RelationTests extends Quiz {
	
	private $db;

	public function __construct() {
		$this->db = new Database_query('192.168.94.31', 'root', 'root');
		$this->db->select_db('tmp');
	}

	public function HasManyClass() {
		$class = get_class($this->db->from('posts')->join('comments')->go()->record(0)->comments);
		return $class == 'Database_result';
	}

	public function HasManyEntryClass() {
		$class = get_class($this->db->from('posts')->join('comments')->go()->record(0)->comments->record(0));
		return $class == 'Database_record';
	}

	public function HasManyEntryData() {
		$comment = $this->db->from('posts')->join('comments')->go()->record(0)->comments->record('comment');
		return $comment == 'Yea!';
	}

	public function HasManyIterator() {
		$comment = $this->db->from('posts')->join('comments')->go()->record(0)->comments->record(1)->comment;
		return $comment == 'Woot!';
	}

	public function HasOneClass() {
		$result = $this->db->from('comments')->join('posts')->go();
		print_r($result);
		$class = @get_class($result->record(0)->posts);
		return $class == 'Database_result';
	}

}