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

	public function connection() {
		return $this->connection;
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
		foreach ($this->from as $model) {
			$select[] = $model->sql_select();
		}
		foreach ($this->joins as $join) {
			$select[] = $join->sql_select();
		}

		// User defined selects can either be * or an array of fields. We
		// really don't care what they're selecting since we know the preious
		// section has us covered.
		// First we'll build a list of tables in this query.
		$tables = array();
		foreach ($this->from as $model) {
			$id = $model->identifier();
			$tables[$id] = $model;
		}
		foreach ($this->joins as $join) {
			$id = $join->foreign_table->identifier();
			$tables[$id] = $join->foreign_table;
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

	/**
	 * From
	 *
	 * Sets the FROM part of the SQL query. Input can be accepted in a number
	 * of ways:
	 *   1. from('posts') passing in, simply, the table you're looking for
	 *   2. from('users', 'assigned') with two parameters, passing in an
	 *      optional table alias
	 *   3. from('posts as p') with the alias assigned within the string
	 *   4. from('posts, users, comments') passing in the primary table first,
	 *      followed by optional join tables
	 */
	public function from($table, $alias=FALSE) {

		// Check if we're passing in multiple tables. If so split it out and
		// join on any subsequent tables.
		if (strpos($table, ',')) {
			$tables = preg_split('/\s*,\s*/', $table);
			$this->from(array_shift($tables));
			foreach ($tables as $table) {
				$this->join($table);
			}
			return $this;
		}

		// Check if we're setting the alias within the string
		if (preg_match('/^(.*)\s+as\s+(.*)$/i', $table, $matches)) {
			$table = $matches[1];
			$alias = $matches[2];
		}

		// If we're here we know we're dealing with a vanilla table. Add it.
		$this->from[] = $this->add_table($table, $alias, TRUE);;

		return $this;
	}

	public function build_from() {
		$sql = ' FROM ';
		foreach ($this->from as $model) {
			$table = $model->table_name();
			$identifier = $model->identifier();
			$sql.= "{$table} AS {$identifier}";
		}
		return $sql;
	}

	// ------------------------------------------------------------------------

	public function join() {
		if (func_num_args() == 1 && is_array(func_get_arg(0))) {
			$join = func_get_arg(0);
		}
		else if (func_num_args() == 1 && is_string(func_get_arg(0))) {
			$join = array(
				'foreign_table' => func_get_arg(0)
			);
		}
		else if (func_num_args() == 2) {
			$join = array_merge(array(
				'foreign_table' => func_get_arg(0)
			), func_get_arg(1));
		}

		$this->joins[] = $join;

		return $this;
	}

	public function prep_joins() {
		foreach ($this->joins as &$c) {

			// Check that the join hasn't already been converted to an
			// object. If it has, bail out, no need to do it again.
			if (!is_array($c)) {
				continue;
			}

			// Pass our connection through to the Join. It'll need it to
			// confirm whether the join fields are valid.
			$c['connection'] = $this->connection;
			
			// Determine the tables we're trying to relate here. This parses
			// the string for the following attributes:
			//     [primary_table].[foreign_table] as [alias]
			preg_match('/^(?:(.*)\.)?(.*?)(?:\s+?as\s+(.*))?$/i', $c['foreign_table'], $matches);

			// Check if we define the primary table in the string.
			if ($matches[1]) {
				$c['primary_table'] = $this->model_for_table_name(trim($matches[1]));
			}

			// If the primary table isn't set in the string, set it to the
			// primary table of this query
			else {
				$c['primary_table'] = $this->primary_table();
			}

			// Set the foreign table. There has to be a foreign table or there
			// isn't really a join, is there?
			$c['foreign_table'] = $matches[2];

			// If we're passing in an alias via the string, set it here
			if (isset($matches[3]) && $matches[3]) {
				$c['as'] = @$matches[3];
			}

			// If our primary table is still a string turn it into a model
			if (is_string($c['primary_table'])) {
				$c['primary_table'] = $this->model_for_table_name($c['primary_table']);
			}

			// Check if we're referring to a defined relationship on the
			// primary table.
			if ($config = $c['primary_table']->join_named($c['foreign_table'])) {
				$c = array_merge($c, $config);
			}

			// Make the foreign table into model.
			// Note: this shouldn't use the `model_for_table_name` method
			// because we don't ever want to "reuse" a model, specifically its
			// identifier. The foreign table is always a new table to this
			// query so it should always generate a new model.
			$c['foreign_table'] = Model::for_table($c['foreign_table'], $c['as']);
			$this->add_table($c['foreign_table'], $c['as']);

			// Finally, check if this is actually a valid join?
			if (($join = JoinOne::check($c)) !== FALSE || 
			    ($join = JoinMany::check($c)) !== FALSE || 
			    ($join = JoinManyMany::check($c)) !== FALSE) {

				$c = $join;
			}

			else {
				$c = NULL;
			}
		}

		// Clear out invalid joins
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
		$keys = array_keys($this->from);
		return @$this->from[$keys[0]]?:FALSE;
	}

	public function add_table($model, $alias=FALSE, $primary=FALSE) {
		if (is_string($model)) {
			$model = Model::for_table($model, $alias);
		}

		$key = $alias?:'t'.count($this->tables);

		if ($primary) {
			$this->tables = array_merge(array(
				$key => $model
			), $this->tables);
		}

		else {
			$this->tables[$key] = $model;
		}
		
		return $model;
	}

	public function model_for_identifier($table_identifier) {
		foreach ($this->tables as $model) {
			if ($model->identifier() == $table_identifier) {
				return $model;
			}
		}
		return FALSE;
	}

	public function table_identifier_for($table_name) {
		return array_search($table_name, $this->tables);
	}

	/**
	 * Model for Table Name
	 *
	 * Checks through all the existing models to see if there is a model
	 * matching the requested table name. If not it will create a new model.
	 */
	public function model_for_table_name($table_name) {
		if (strpos($table_name, '.')) {
			return $this->model_for_table_path($table_name);
		}

		foreach ($this->tables as $model) {
			if ($model->table_name() == $table_name ||
			    $model->alias() == $table_name) {
				return $model;
			}
		}

		throw new \Exception('You\'ve specified a table that doesn\'t exist: '.$table_name, 500);
	}

	/**
	 * Model for Table Path
	 *
	 * Takes a dot notation path of tables and determines the appropriate model
	 * for the right most table.
	 */
	public function model_for_table_path($path) {

		// Explode out our string
		$tables = preg_split('/\./', $path);

		// Find the initial model for the furthese left table
		$primary_table = $this->model_for_table_name(array_shift($tables));

		// Loop through subsequent tables
		foreach ($tables as $table) {

			// Get the join specified by the two tables
			$join = $this->join_for($primary_table, $table);

			// Make the right table (the foreign_table) the new
			// primary table in our loop
			$primary_table = $join->foreign_table;
		}

		// Finally update the primary table
		return $primary_table;
	}

	/**
	 * Join For
	 *
	 * Returns the join config for specified parameters. Two results are
	 * possible:
	 *   1. Passed a $model the method returns the join config used to attach
	 *      the table represented by $model
	 *   2. Passed a $name as well, the method returns the join used to attach
	 *      the table represented by $name.
	 */
	public function join_for($model, $name=FALSE) {
		$identifier = $model->identifier();

		foreach ($this->joins as $join) {

			// This method could be called while we're building the actual join
			// array. In which case we would be looping over the element of the
			// array that we're actually trying to join. Since we only really
			// care about put together joins at this point if the element isn't
			// an object, then continue on our way.
			// It's all very meta.
			if (is_array($join)) {
				continue;
			}

			if (func_num_args() == 1) {
				if ($join->foreign_table->identifier() == $identifier) {
					return $join;
				}
			}

			else if (func_num_args() == 2) {
				if ($join->primary_table->identifier() == $identifier &&
				   ($join->as == $name || 
					$join->foreign_table->table_name() == $name)) {
					return $join;
				}
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
