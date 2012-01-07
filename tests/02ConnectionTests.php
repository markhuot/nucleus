<?php

require_once dirname(__FILE__).'/../Nucleus.php';

class ConnectionTests extends Quiz {
	
	private $db;

	public function __construct() {
		$this->db = new Nucleus\Query('192.168.94.31', 'root', 'root');
	}

	public function canConnect() {
		return $this->db->connection();
	}

	public function selectDatabase() {
		return $this->db->select_db('tmp');
	}
	
	public function testData() {
		return TRUE;
	}

}
