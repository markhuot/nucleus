<?php

require_once dirname(__FILE__).'/../Nucleus.php';

class ConnectionTests extends Quiz {
	
	private $conn;
	private $db;

	public function __construct() {
		$this->conn = new Nucleus\Connection('mysql:host=192.168.94.31;dbname=tmp', 'root', 'root');
		$this->db = new Nucleus\Query();
	}

	public function canConnect() {
		return $this->conn;
	}

}
