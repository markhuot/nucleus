<?php

namespace Nucleus;

class Join {
	protected $as;
	protected $type;
	protected $primary_class;
	protected $primary_table;
	protected $primary_key;
	protected $foreign_class;
	protected $foreign_table;
	protected $foreign_key;
	
	public function __construct($config=array()) {
		foreach ($config as $key => $value) {
			$this->{$key} = $value;
		}
		
		$this->set_default('as', $this->foreign_table());
		$this->set_default('type', 'left');
	}

	public function set_default($key, $value) {
		if (!$this->{$key}) {
			$this->$key = $value;
		}
	}
	
	protected function _check_join_columns() {
		if (!$this->table_has_column(
			$this->primary_table,
			$this->primary_key)) {
			return FALSE;
		}

		if (!$this->table_has_column(
			$this->foreign_table,
			$this->foreign_key)) {
			return FALSE;
		}

		return TRUE;
	}
	
	protected function table_has_column($table, $column) {
		$query = mysql_query("SHOW COLUMNS FROM {$table} WHERE Field='{$column}'");
		return mysql_num_rows($query);
	}

	protected function table_has_columns($table, $column) {
		$columns = array_slice(func_get_args(), 1);
		$sql = "'".implode("','", $columns)."'";

		$query = mysql_query("SHOW COLUMNS FROM {$table} WHERE Field IN ({$sql})");
		return mysql_num_rows($query) == count($columns);
	}
	
	protected function sql() {
		$sql = ' '.strtoupper($this->type);
		$sql.= ' JOIN ';
		$sql.= $this->foreign_table;
		$sql.= ' AS ';
		$sql.= $this->foreign_id;
		$sql.= ' ON ';
		$sql.= $this->foreign_id;
		$sql.= '.';
		$sql.= $this->foreign_key;
		$sql.= '=';
		$sql.= $this->primary_id;
		$sql.= '.';
		$sql.= $this->primary_key;
		return $sql
	}
}
