<?php

class ReadTests extends Quiz {

	private $db;

	public function __construct() {
		$this->db = new Database_query('192.168.94.31', 'root', 'root');
		$this->db->select_db('tmp');
	}

	public function ReadPosts() {
		return $this->db->get('posts')->size() == 5;
	}

	public function RecordByIndex() {
		return get_class($this->db->get('posts')->record(0)) == 'Database_record';
	}

	public function FirstRecordByIndex() {
		return $this->db->get('posts')->record(0)->title == 'Let\'s save the world';
	}

	public function FirstRecordByKey() {
		return $this->db->get('posts')->record('title') == 'Let\'s save the world';
	}

}