<?php

class Database_query {
	private $connection;
	private $host;
	private $user;
	private $pass;
	private $name;

	private $queries = array();
	private $select = array();
	private $from = array();
	private $tables = array();
	private $joins = array();
	private $where = array();
	private $orderby = array();

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
		foreach ($this->tables as $table) {
			$query = mysql_query("DESCRIBE {$table}");
			
			if (!$query) {
				throw new Exception('Could not build SELECT, invalid table specified');
				return FALSE;
			}

			while($row = mysql_fetch_assoc($query)) {
				$field = $row['Field'];
				if (in_array($field, $this->select) || !$this->select) {
					$columns[] = "{$table}.{$field} AS `{$table}.{$field}`";
				}
			}
		}
		return ' SELECT '.implode(', ', $columns);
	}

	// ------------------------------------------------------------------------

	public function from($table, $alias=FALSE) {
		array_unshift($this->tables, $table);
		$this->from[] = $table;
		return $this;
	}

	public function build_from() {
		return ' FROM '.implode(', ', $this->from);
	}

	// ------------------------------------------------------------------------

	public function join($table, $config=array()) {
		if (is_string($table)) {
			$config['table_name'] = $table;
		}

		else if (is_array($table)) {
			$config = $table;
			$table = $config['table_name'];
		}

		$this->tables[] = $table;
		$this->joins[] = $config;
		return $this;
	}

	public function build_joins() {
		$sql = '';
		
		// Loop through each of our joins and build SQL for it
		foreach ($this->joins as $join_config) {

			// Loop through each of the tables specified by the query and see
			// if the join in question maps to the table in question.
			// Essentially we're going through each join and finding what table
			// it relates to.
			foreach ($this->tables as $table) {
				$sql.= $this->_check_has_one($join_config, $table);
				$sql.= $this->_check_has_many($join_config, $table);
			}
		}

		return $sql;
	}

	private function _check_has_one($join_config, $table) {
		// Determine some default variables for the join. Broken out
		// because silly PEP8 requires an 80 character limit and I
		// hate wraping in my text editor. ;)
		$join_table_name = $join_config['table_name'];

		// Merge our default config with the passed config. This will
		// search for a hasMany relationship.
		$join = array_merge(array(
			'as' => $join_table_name,
			'class_name' => ucfirst(Database::singular($join_table_name)),
			'primary_key' => Database::singular($join_table_name).'_id',
			'foreign_key' => 'id',
			'type' => 'left'
		), $join_config);

		// Check that each table has the necessary columns to support
		// this relationship.
		if (!$this->table_has_column($table, $join['primary_key']) || 
		    !$this->table_has_column($join['table_name'], $join['foreign_key']) || $table == $join['table_name']) {
			return '';
		}

		// Finally, assemble the SQL statement
		return ' '.strtoupper($join['type'])." JOIN {$join['table_name']} AS {$join['as']} ON {$table}.{$join['primary_key']}={$join['table_name']}.{$join['foreign_key']}";
	}

	private function _check_has_many($join_config, $table) {
		// Determine some default variables for the join. Broken out
		// because silly PEP8 requires an 80 character limit and I
		// hate wraping in my text editor. ;)
		$join_table_name = $join_config['table_name'];

		// Merge our default config with the passed config. This will
		// search for a hasMany relationship.
		$join = array_merge(array(
			'as' => $join_table_name,
			'class_name' => ucfirst(Database::singular($join_table_name)),
			'primary_key' => 'id',
			'foreign_key' => Database::singular($table).'_id',
			'type' => 'left'
		), $join_config);

		// Check that each table has the necessary columns to support
		// this relationship.
		if (!$this->table_has_column($table, $join['primary_key']) || 
		    !$this->table_has_column($join['table_name'], $join['foreign_key']) || $table == $join['table_name']) {
			return '';
		}

		// Finally, assemble the SQL statement
		return ' '.strtoupper($join['type'])." JOIN {$join['table_name']} AS {$join['as']} ON {$table}.{$join['primary_key']}={$join['table_name']}.{$join['foreign_key']}";
	}

	// ------------------------------------------------------------------------

	public function where($key, $value, $operator='=') {
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
		$this->queries[] = ($query = $this->_build_query());
		$result = new Database_result(
			$this->primary_table(),
			$this->_build_result($query)
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

	private function _build_result($sql) {
		$result = array();
		$query = mysql_query($sql);

		if (!$query) {
			echo mysql_error().'<br /><br />'.$this->last_query();
			die;
		}

		while ($row = mysql_fetch_assoc($query)) {
			$result[] = $row;
		}
		return $result;
	}

	// ------------------------------------------------------------------------

	public function primary_table() {
		return @$this->tables[0]?:FALSE;
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