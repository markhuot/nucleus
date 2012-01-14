<?php

namespace Nucleus;

class Connection extends \PDO {
	static $connections = array();
	
	private static function guess_connection() {
		if ($conn = self::guess_codeigniter_connection()) {
			return $conn;
		}
	}

	private static function guess_codeigniter_connection() {
		if (!constant('APPPATH')) { return FALSE; }
		include APPPATH.'config/database'.EXT;
		extract($db[$active_group]);
		return new Connection(
			"{$dbdriver}:host={$hostname};dbname={$database}",
			$username,
			$password
		);
	}

	public static function active() {
		if (!count(self::$connections)) {
			return self::guess_connection();
		}

		return self::$connections[count(self::$connections)-1];
	}

	public function __construct($dsn=NULL, $user=NULL, $pass=NULL) {
		parent::__construct($dsn, $user, $pass);
		Connection::$connections[] = $this;
	}

	public function query($sql=FALSE, $vars=array()) {
		$statement = $this->prepare($sql);
		if (!$statement->execute($vars)) {
			throw new \Exception(
				implode(' ', $statement->errorInfo())."\n".$sql,
				500
			);
		}
		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}
}