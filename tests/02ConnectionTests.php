<?php

require_once dirname(__FILE__).'/../Nucleus.php';

class ConnectionTests extends Quiz {
	
	private $db;

	public function __construct() {
		$this->db = new Nucleus\Query();
	}

	public function canConnect() {
		return $this->db->connection();
	}

}
