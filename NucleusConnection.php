<?php

namespace Nucleus;

class Connection {
	static $connections;
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

		$connection = new \PDO($this->dsn, $this->user, $this->pass);

		if (!$connection) {
			throw new \Exception('Connection error.');
		}

		return $this->connections[] = $connection;
	}

	public function connection() {
		return $this->connection[count($this->connections)-1];
	}
}