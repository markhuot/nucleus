<?php

require_once dirname(__FILE__).'/../Database.php';

class ConnectionTests extends Quiz {
	
	private $db;

	public function canConnect() {
		$this->db = new Database_query('192.168.94.31', 'root', 'root');
		return $this->db->connection();
	}

	public function selectDatabase() {
		return $this->db->select_db('tmp');
	}
	
	public function testData() {
		return TRUE;
	}

}