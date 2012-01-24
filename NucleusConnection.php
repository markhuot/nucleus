<?php

namespace Nucleus;

class Connection extends \PDO {
	static $connections = array();

	// ------------------------------------------------------------------------
	
	private static function guess_connection() {
		if (($config = self::guess_codeigniter_connection()) ||
		    ($config = self::guess_default_connection())) {
			$dsn = "{$config['dbtype']}:";
			$dsn.= (@$config['dbhost']) ? "host={$config['dbhost']};" : '';
			$dsn.= (@$config['dbsock']) ? "unix_socket={$config['dbsock']};" : '';
			$dsn.= ($config['dbname']) ? "dbname={$config['dbname']};" : '';

			return new Connection(
				$dsn,
				$config['dbuser'],
				$config['dbpass']
			);
		}

		return FALSE;
	}

	private static function guess_codeigniter_connection() {
		if (!defined('APPPATH')) { return FALSE; }
		return array(
			'dbtype' => \Nucleus::config('dbdriver'),
			'dbhost' => \Nucleus::config('hostname'),
			'dbname' => \Nucleus::config('database'),
			'dbuser' => \Nucleus::config('username'),
			'dbpass' => \Nucleus::config('password')
		);
	}

	private static function guess_default_connection() {
		include rtrim(__DIR__, '/').'/config.php';
		return $config;
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