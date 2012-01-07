<?php

class Nucleus_query {
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
	private $where = array();         // Any defined where statements
	private $orderby = array();       // The requested order

	// ------------------------------------------------------------------------

	public function __construct($host=NULL, $user=NULL, $pass=NULL, $name=NULL) {
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this->name = $name;

		$this->reset();

		if ($host && $user && $pass) {
			$this->connect();
		}
		
		if ($name) {
			$this->select_db();
		}
	}

	// ------------------------------------------------------------------------

	public function reset() {
		$defaults = (object)get_class_vars('Nucleus_query');
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
			throw new Exception('No database defined.');
		}

		if (!mysql_select_db($this->name)) {
			throw new Exception('Error selecting database.');
		}

		return TRUE;
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

	public function join($foreign_table, $c=array()) {
	
		// Determine the tables we're trying to relate here
		preg_match('/^(?:(.*?)\.)?(.*)$/', $foreign_table, $matches);
		$c['primary_table'] = $matches[1]?:$this->primary_table();
		$c['primary_id'] = $this->table_identifier_for($c['primary_table']);
		$c['foreign_table'] = $matches[2];
		$c['foreign_id'] = $this->add_table($c['foreign_table']);

		if (($join = Nucleus_join_one::check($c)) !== FALSE || 
		    ($join = Nucleus_join_many::check($c)) !== FALSE || 
		    ($join = Nucleus_join_many_many::check($c)) !== FALSE) {
		    
			$this->joins[$join->primary_id().'.'.$join->as()] = $join;
		}

		return $this;
	}

	public function build_joins() {
		$sql = '';

		foreach ($this->joins as $key => $config) {
			$sql.= $join->sql();
		}

		return $sql;
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
		$rows = $this->_fetch_rows($sql);
		$result = new Nucleus_result(
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
			throw new Exception(mysql_error()."\n".$this->last_query());
			return $rows;
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

	public function primary_table_identifier() {
		$keys = array_keys($this->tables);
		return @$keys[0]?:FALSE;
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

	public function join_config($key=FALSE) {
		return @$this->joins[$key];
	}

	public function join_for_foreign_id($identifier) {
		foreach ($this->joins as $join) {
			if ($join['foreign_id'] == $identifier) {
				return $join;
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function query() {
		return $this->_build_query();
	}

	public function last_query() {
		return @$this->queries[count($this->queries)-1]?:FALSE;
	}

	// ------------------------------------------------------------------------

	
}

class Nucleus_join_one extends Nucleus_join {
	public static check($config=array()) {
		$join = new Nucleus_join_one(array_merge(array(
			'primary_key' => $config['foreign_table'].'_id',
			'foreign_key' => 'id'
		), $config));

		return $join->_check_join_columns()?$join:FALSE;
	}
}

class Nucleus_join_many extends Nucleus_join {
	public static check($config=array()) {
		$join = new Nucleus_join_many(array_merge(array(
			'primary_key' => 'id',
			'foreign_key' => Nucleus::singular($config['primary_table']).'_id'
		), $config));

		return $join->_check_join_columns()?$join:FALSE;
	}
}