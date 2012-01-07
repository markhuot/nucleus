<?php

require_once dirname(__FILE__).'/../Nucleus.php';

class ConnectionTests extends Quiz {
	
	private $db;

	public function __construct() {
		$this->db = new Nucleus\Query('sqlite::memory:');
	}

	public function canConnect() {
		return $this->db->connection();
	}

	public function testData() {
		return TRUE;
	}

}
