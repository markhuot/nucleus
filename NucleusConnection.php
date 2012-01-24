<?php

namespace Nucleus;

class Connection extends \PDO {
	static $connections = array();

	// ------------------------------------------------------------------------
	
	private static function guess_connection() {
		$dsn = \Nucleus::config('dbtype').':';

		if (\Nucleus::config('dbhost')) {
			$dsn.= 'host='.\Nucleus::config('dbhost').';';
		}

		if (\Nucleus::config('dbsock')) {
			$dsn.= 'socket='.\Nucleus::config('dbsock').';';
		}
		
		$dsn.= 'dbname='.\Nucleus::config('dbname').';';

		return new Connection(
			$dsn,
			\Nucleus::config('dbuser'),
			\Nucleus::config('dbpass')
		);
	}

	// ------------------------------------------------------------------------

	public static function active() {
		if (!count(self::$connections)) {
			return self::guess_connection();
		}

		return self::$connections[count(self::$connections)-1];
	}

	// ------------------------------------------------------------------------

	public function __construct($dsn=NULL, $user=NULL, $pass=NULL) {
		parent::__construct($dsn, $user, $pass);
		Connection::$connections[] = $this;
	}

	// ------------------------------------------------------------------------

	public function query($sql=FALSE, $vars=array()) {
		$statement = $this->prepare($sql);
		if (!$statement->execute($vars)) {
			throw new \Exception(
				implode(' ', $statement->errorInfo())."\n".$sql,
				500
			);
		}
		return $statement;
	}

	// ------------------------------------------------------------------------

	public function table_exists($table) {
		$sql = "SHOW TABLES LIKE :table";
		return $this->query($sql, array(
			'table' => $table
		))->rowCount()==1;
	}

	public function table_has_column($table, $column) {
		$sql = "SHOW COLUMNS FROM {$table} WHERE Field='{$column}'";
		return $this->query($sql)->rowCount();
	}

	public function table_has_columns($table, $column) {
		$columns = array_slice(func_get_args(), 1);
		$sql = "'".implode("','", $columns)."'";

		return $this->query("SHOW COLUMNS FROM {$table} WHERE Field IN ({$sql})")->rowCount() == count($columns);
	}
}