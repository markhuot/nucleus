<?php

class Database_query {
	private $connection;
	private $host;
	private $user;
	private $pass;
	private $name;

	private $queries = array();       // Each query run by this object
	private $select = array();        // The requested selections
	private $from = array();          // The primary table to pull from
	private $tables = array();        // Every table referenced in this query
	private $joins = array();         // The requested joins (indexed by name)
	private $join_configs = array();  // Store successful join configs
	private $where = array();         // Any defined where statements
	private $orderby = array();       // The requested order

	// ------------------------------------------------------------------------

	public function __construct($host=NULL, $user=NULL, $pass=NULL, $name=NULL) {
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this->name = $name;

		$this->reset();
		$this->connect();
		$this->select_db();
	}

	// ------------------------------------------------------------------------

	public function reset() {
		$defaults = (object)get_class_vars('Database_query');
		$this->select = $defaults->select;
		$this->from = $defaults->from;
		$this->tables = $defaults->tables;
		$this->joins = $defaults->joins;
		$this->join_configs = $defaults->join_configs;
		$this->where = $defaults->where;
		$this->orderby = $defaults->orderby;
	}

	// ------------------------------------------------------------------------

	public function connect($host=NULL, $user=NULL, $pass=NULL) {
		$this->host = $host?:$this->host;
		$this->user = $user?:$this->user;
		$this->pass = $pass?:$this->pass;

		if (!$this->host || !$this->user || !$this->pass) {
			return FALSE;
		}

		$this->connection = @mysql_connect($this->host, $this->user, $this->pass);

		if (!$this->connection) {
			throw new Exception('Connection error.');
		}
	}

	public function connection() {
		return $this->connection;
	}

	public function select_db($name=NULL) {
		$this->name = $name?:$this->name;

		if (!$this->name) {
			return FALSE;
		}

		return mysql_select_db($this->name);
	}

	// ------------------------------------------------------------------------

	public function select($key) {
		if (is_array($key)) {
			foreach ($key as $k) {
				$this->select($k);
			}
		}
		else if (is_string($key)) {
			$this->select[] = $key;
		}
		return $this;
	}

	public function build_select() {
		$columns = array();
		foreach ($this->tables as $identifier => $table) {
			$query = mysql_query("DESCRIBE {$table}");
			
			if (!$query) {
				throw new Exception('Could not build SELECT, invalid table specified');
			}

			while($row = mysql_fetch_assoc($query)) {
				$field = $row['Field'];
				if (in_array($field, $this->select) || !$this->select) {
					$columns[] = "{$identifier}.{$field} AS `{$identifier}.{$field}`";
				}
			}
		}
		return ' SELECT '.implode(', ', $columns);
	}

	// ------------------------------------------------------------------------

	public function from($table, $alias=FALSE) {
		$key = $this->add_table($table, $alias, TRUE);
		$this->from[$key] = $table;
		return $this;
	}

	public function build_from() {
		$sql = ' FROM ';
		foreach ($this->from as $key => $table) {
			$sql.= "{$table} AS {$key}";
		}
		return $sql;
	}

	// ------------------------------------------------------------------------

	// 'as'             How we'll refer to the related entries
	// 'primary_class'  The primary class name
	// 'primary_table'  The primary table name
	// 'primary_id'     The primary table identifier
	// 'primary_key'    The primary table key
	// 'foreign_class'  The related class name
	// 'foreign_table'  The related table name
	// 'foreign_id'     The related table identifier
	// 'foreign_key'    The related table key
	// 'type'           The type of join

	public function join($table, $config=array()) {
		if (is_string($table)) {
			preg_match('/^(?:(.*?)\.)?(.*)$/', $table, $matches);
			$config['primary_table'] = $matches[1]?:null;
			$config['foreign_table'] = $matches[2];
			$table = $matches[2];
		}

		else if (is_array($table)) {
			$config = $table;
		}
		
		$this->joins[] = array_merge(array(
			'type' => 'left',
			'primary_table' => $this->primary_table(),
			'foreign_id' => $this->add_table($config['foreign_table'], @$config['as'])
		), $config);
		
		return $this;
	}

	public function build_joins() {
		$sql = '';

		// Loop through each of our joins and build SQL for it
		foreach ($this->joins as $config) {
		
			if ($config = $this->_check_has_one($config) || 
				$config = $this->_check_has_many($config)) {
				
				// This was a successful join, store the utilized config
				$this->join_configs[$config['foreign_id']] = $config;

				// Finally, assemble the SQL statement
				$sql.= ' ';
				$sql.= strtoupper($config['type']);
				$sql.= ' JOIN ';
				$sql.= $config['foreign_table'];
				$sql.= ' AS ';
				$sql.= $config['foreign_id'];
				$sql.= ' ON ';
				$sql.= $config['foreign_id'];
				$sql.= '.';
				$sql.= $config['foreign_key'];
				$sql.= '=';
				$sql.= $config['primary_id'];
				$sql.= '.';
				$sql.= $config['primary_key'];
			}
		}

		return $sql;
	}

	private function _check_has_one($config) {
		
		// Merge our default config with the passed config.
		$config = array_merge(array(
			'primary_key' => Database::singular($config['foreign_table']).'_id',
			'foreign_key' => 'id'
		), $config);

		return $this->_check_join_tables($config)?$config:FALSE;
	}

	private function _check_has_many($config) {

		// Merge our default config with the passed config.
		$config = array_merge(array(
			'primary_key' => 'id',
			'foreign_key' => Database::singular($config['primary_table']).'_id'
		), $config);

		return $this->_check_join_tables($config)?$config:FALSE;
	}

	private function _check_join_tables($config) {
		if ($this->table_has_column($config['primary_table'], $config['primary_key']) && 
		    $this->table_has_column($config['foreign_table'], $config['foreign_key'])) {
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function where($key, $value) {
		if (func_num_args() == 2) {
			$key = func_get_arg(0);
			$operator = '=';
			$value = func_get_arg(1);
		}
		if (func_num_args() == 3) {
			$key = func_get_arg(0);
			$operator = func_get_arg(1);
			$value = func_get_arg(2);
		}

		if ($value === TRUE) { $value = 1; }
		if ($value === FALSE) { $value = 0; }
		if ($value === NULL) { $operator = 'IS'; $value = 'NULL'; }

		$this->where[] = "{$key} {$operator} {$value}";
		return $this;
	}

	public function build_where() {
		$sql = '';
		if ($this->where) {
			$sql.= ' WHERE ';
			$sql.= implode(' AND ', $this->where);
		}
		return $sql;
	}

	// ------------------------------------------------------------------------

	public function orderby($key, $sort='asc') {
		$this->orderby[] = "{$key} {$sort}";
		return $this;
	}

	public function build_orderby() {
		$sql = '';

		if ($this->orderby) {
			$sql.= ' ORDER BY ';
			$sql.= implode(', ', $this->orderby);
		}

		return $sql;
	}

	// ------------------------------------------------------------------------

	public function get($table) {
		return $this->from($table)->go();
	}

	// ------------------------------------------------------------------------

	public function go() {
		$this->queries[] = ($sql = $this->_build_query());
		$rows = $this->_fetch_rows($sql)
		$result = new Database_result(
			clone $this,
			$rows
		);

		$this->reset();
		return $result;
	}

	private function _build_query() {
		$sql = $this->build_select();
		$sql.= $this->build_from();
		$sql.= $this->build_joins();
		$sql.= $this->build_where();
		$sql.= $this->build_orderby();
		return trim($sql);
	}

	private function _fetch_rows($sql) {
		$rows = array();
		$query = mysql_query($sql);

		if (!$query) {
			echo mysql_error().'<br /><br />'.$this->last_query();
			die;
		}

		while ($row = mysql_fetch_assoc($query)) {
			$rows[] = $row;
		}

		return $rows;
	}

	// ------------------------------------------------------------------------

	public function primary_table() {
		$keys = array_keys($this->tables);
		return @$this->tables[$keys[0]]?:FALSE;
	}

	public function add_table($table, $alias=FALSE, $primary=FALSE) {
		$key = $alias?:'t'.count($this->tables);
		$this->tables[$key] = $table;
		return $key;
	}

	public function table_identifier_for($table_name) {
		return array_search($table_name, $this->tables);
	}

	public function table_name_for($identifier) {
		return @$this->tables[$identifier];
	}

	public function join_configs($identifier=FALSE) {
		if ($identifier === FALSE) {
			return $this->join_configs;
		}

		return @$this->join_configs[$identifier];
	}

	// ------------------------------------------------------------------------

	public function query() {
		return $this->_build_query();
	}

	public function last_query() {
		return @$this->queries[count($this->queries)-1]?:FALSE;
	}

	// ------------------------------------------------------------------------

	public function table_has_column($table, $column) {
		$query = mysql_query("SHOW COLUMNS FROM {$table} WHERE Field='{$column}'");
		return mysql_num_rows($query);
	}
}