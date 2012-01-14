<?php

namespace Nucleus;

class Join {
	protected $connection;
	public $as;
	public $type;
	public $primary_class;
	public $primary_table;
	public $primary_key;
	public $foreign_class;
	public $foreign_table;
	public $foreign_key;
	
	public function __construct($config=array()) {
		foreach ($config as $key => $value) {
			$this->{$key} = $value;
		}
		
		$this->set_default('as', $this->foreign_table);
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
		$statement = $this->connection->prepare("SHOW COLUMNS FROM {$table} WHERE Field='{$column}'");
		if (!$statement->execute()) {
			throw new \Exception(
				$statement->errorInfo()."\n".$this->last_query(),
				500
			);
		}
		return $statement->rowCount();
	}

	protected function table_has_columns($table, $column) {
		$columns = array_slice(func_get_args(), 1);
		$sql = "'".implode("','", $columns)."'";

		$statement = $this->connection->prepare("SHOW COLUMNS FROM {$table} WHERE Field IN ({$sql})");
		if (!$statement->execute()) {
			throw new \Exception(
				$statement->errorInfo()."\n".$this->last_query(),
				500
			);
		}
		return $statement->rowCount() == count($columns);
	}
	
	public function sql() {
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
		return $sql;
	}
}
