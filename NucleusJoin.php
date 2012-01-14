<?php

namespace Nucleus;

class Join {
	protected $connection;
	public $as;
	public $type;
	public $join_type;
	public $primary_class;
	public $primary_table;
	public $primary_key;
	public $foreign_class;
	public $foreign_table;
	public $foreign_key;
	public $join_table;
	public $join_id;
	
	public function __construct($config=array()) {
		$config = array_merge(array(
			'as' => $config['foreign_table'],
			'join_type' => 'left'
		), $config);

		foreach ($config as $key => $value) {
			$this->{$key} = $value;
		}
	}

	protected function _check_join_columns() {
		if (!$this->connection->table_has_column(
			$this->primary_table,
			$this->primary_key)) {
			return FALSE;
		}

		if (!$this->connection->table_has_column(
			$this->foreign_table,
			$this->foreign_key)) {
			return FALSE;
		}

		return TRUE;
	}

	public function sql_select() {
		return array(
			"{$this->primary_id}.id AS `{$this->primary_id}.id`",
			"{$this->primary_id}.{$this->primary_key} AS `{$this->primary_id}.{$this->primary_key}`",
			"{$this->foreign_id}.id AS `{$this->foreign_id}.id`",
			"{$this->foreign_id}.{$this->foreign_key} AS `{$this->foreign_id}.{$this->foreign_key}`"
		);
	}
	
	public function sql_join() {
		$sql = ' '.strtoupper($this->join_type);
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
