<?php

namespace Nucleus;

class Query {
	private $connection;

	private $queries = array();       // Each query run by this object
	private $select = array();        // The requested selections
	private $from = array();          // The primary table to pull from
	private $tables = array();        // Every table referenced in this query
	private $joins = array();         // The requested joins (indexed by name)
	private $where = array();         // Any defined where statements
	private $orderby = array();       // The requested order

	// ------------------------------------------------------------------------

	public function __construct($connection=FALSE) {
		$this->connection = $connection ?: Connection::active();
		$this->reset();
	}

	// ------------------------------------------------------------------------

	public function reset() {
		$defaults = (object)get_class_vars('\Nucleus\Query');
		$this->select = $defaults->select;
		$this->from = $defaults->from;
		$this->tables = $defaults->tables;
		$this->joins = $defaults->joins;
		$this->where = $defaults->where;
		$this->orderby = $defaults->orderby;
	}

	// ------------------------------------------------------------------------

	public function select($key) {
		if (is_array($key)) {
			foreach ($key as $k) {
				$this->select($k);
			}
		}
		else if (is_string($key) && $key !== '*') {
			$this->select[] = $key;
		}
		return $this;
	}

	public function build_select() {
		$select = array();

		// Required selects are the primary and foreign key fields which help
		// us identify how tables are related. These are added in no
		// matter what.
		foreach ($this->from as $identifier => $table) {
			$select[] = "{$identifier}.id AS `{$identifier}.id`";
		}
		foreach ($this->joins as $join) {
			$select = array_merge($join->sql_select(), $select);
		}

		// User defined selects can either be * or an array of fields. We
		// really don't care what they're selecting since we know the preious
		// section has us covered.
		// First we'll build a list of tables in this query.
		$tables = $this->from;
		foreach ($this->joins as $join) {
			$tables[$join->foreign_id] = $join->foreign_table;
		}

		// Now we'll go through and identify which columns to select.
		foreach ($tables as $identifier => $table) {
			$columns = $this->query("DESCRIBE {$table}");

			if (!$columns) {
				throw new \Exception('Could not build SELECT, invalid table specified', 500);
			}

			foreach($columns as $column) {
				$field = $column['Field'];
				if (in_array($field, $this->select) || !$this->select || in_array("{$table}.*", $this->select) || in_array("{$table}.{$field}", $this->select)) {
					$select[] = "{$identifier}.{$field} AS `{$identifier}.{$field}`";
				}
			}
		}

		return ' SELECT '.implode(', ', array_unique($select));
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

	public function join() {
		if (func_num_args() == 1 && is_array(func_get_arg(0))) {
			$this->joins[] = func_get_arg(0);
		}
		else if (func_num_args() == 1 && is_string(func_get_arg(0))) {
			$this->joins[] = array(
				'foreign_table' => func_get_arg(0)
			);
		}
		else if (func_num_args() == 2) {
			$this->joins[] = array_merge(array(
				'foreign_table' => func_get_arg(0)
			), func_get_arg(1));
		}

		return $this;
	}

	public function prep_joins() {
		foreach ($this->joins as &$c) {
			if (!is_array($c)) {
				continue;
			}
			
			// Determine the tables we're trying to relate here
			preg_match('/^(?:(.*?)\.)?(.*)$/', $c['foreign_table'], $matches);
			$c['primary_table'] = $matches[1]?:$this->primary_table();
			$c['primary_id'] = $this->table_identifier_for($c['primary_table']);
			$c['foreign_table'] = $matches[2];
			$c['foreign_id'] = $this->add_table($c['foreign_table']);
			$c['connection'] = $this->connection;

			if (($join = JoinOne::check($c)) !== FALSE || 
			    ($join = JoinMany::check($c)) !== FALSE || 
			    ($join = JoinManyMany::check($c)) !== FALSE) {

				$c = $join;
			}

			else {
				$c = NULL;
			}
		}
		$this->joins = array_filter($this->joins);
	}

	public function build_joins() {
		$sql = '';
		foreach ($this->joins as $join) {
			$sql.= $join->sql_join();
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

		$this->where[$key] = array(
			'operator' => $operator,
			'value' => $value
		);

		return $this;
	}

	public function build_where() {
		$sql = '';
		if ($this->where) {
			$sql.= ' WHERE ';

			foreach ($this->where as $key => $w) {
				$sql.= "{$key} {$w['operator']} :{$key}";
			}
		}
		return $sql;
	}

	public function build_where_parameters() {
		$where = array();
		foreach ($this->where as $key => $w) {
			$where[$key] = $w['value'];
		}
		return $where;
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

	private function _build_query() {
		$this->prep_joins();

		$sql = $this->build_select();
		$sql.= $this->build_from();
		$sql.= $this->build_joins();
		$sql.= $this->build_where();
		$sql.= $this->build_orderby();
		
		return trim($sql);
	}

	public function go() {
		$rows = $this->query();
		$result = new Result(
			clone $this,
			$rows
		);
		$this->reset();
		return $result;
	}

	private function query($sql=FALSE) {
		if (!$sql) {
			$sql = $this->_build_query();
		}
		return $this->connection->query(
			$this->queries[] = $sql,
			$this->build_where_parameters()
		)->fetchAll(\PDO::FETCH_ASSOC);
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

		if ($primary) {
			$this->tables = array_merge(array(
				$key => $table
			), $this->tables);
		}

		else {
			$this->tables[$key] = $table;
		}
		
		return $key;
	}

	public function table_identifier_for($table_name) {
		return array_search($table_name, $this->tables);
	}

	public function join_config($table_identifier, $name) {
		foreach ($this->joins as $join) {
			if ($join->primary_id == $table_identifier && $join->as == $name) {
				return $join;
			}
		}

		return FALSE;
	}

	public function join_for_foreign_id($identifier) {
		foreach ($this->joins as $join) {
			if ($join->foreign_id == $identifier) {
				return $join;
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function current_query() {
		return $this->_build_query();
	}

	public function last_query() {
		return @$this->queries[count($this->queries)-1]?:FALSE;
	}

	// ------------------------------------------------------------------------

	
}
