<?php

require_once dirname(__FILE__).'/../Nucleus.php';

class ConnectionTests extends Quiz {
	
	private $conn;
	private $db;

	public function __construct() {
		$this->conn = new Nucleus\Connection('sqlite::memory');
		$this->db = new Nucleus\Query();
	}

	public function canConnect() {
		return $this->conn;
	}

}
