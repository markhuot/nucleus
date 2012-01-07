<?php

namespace Nucleus;

class Connection {
	private $dsn;
	private $user;
	private $pass;

	public function connect($dsn=NULL, $user=NULL, $pass=NULL) {
		$this->dsn = $dsn?:$this->dsn;
		$this->user = $user?:$this->user;
		$this->pass = $pass?:$this->pass;

		if (!$this->dsn) {
			return FALSE;
		}

		$this->connection = new \PDO($this->dsn, $this->user, $this->pass);

		if (!$this->connection) {
			throw new \Exception('Connection error.');
		}
	}

	public function connection() {
		return $this->connection;
	}
}