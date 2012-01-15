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
			'as' => $config['foreign_table']->table_name(),
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
		$primary_id = $this->primary_table->identifier();
		$primary_pk = $this->primary_table->pk();
		$primary_key = $this->primary_key;
		$foreign_id = $this->foreign_table->identifier();
		$foreign_pk = $this->foreign_table->pk();
		$foreign_key = $this->foreign_key;

		return implode(',', array(
			"{$primary_id}.{$primary_pk} AS `{$primary_id}.{$primary_pk}`",
			"{$primary_id}.{$primary_key} AS `{$primary_id}.{$primary_key}`",
			"{$foreign_id}.{$foreign_pk} AS `{$foreign_id}.{$foreign_pk}`",
			"{$foreign_id}.{$foreign_key} AS `{$foreign_id}.{$foreign_key}`"
		));
	}
	
	public function sql_join() {
		$sql = ' '.strtoupper($this->join_type);
		$sql.= ' JOIN ';
		$sql.= $this->foreign_table;
		$sql.= ' AS ';
		$sql.= $this->foreign_table->identifier();
		$sql.= ' ON ';
		$sql.= $this->foreign_table->identifier();
		$sql.= '.';
		$sql.= $this->foreign_key;
		$sql.= '=';
		$sql.= $this->primary_table->identifier();
		$sql.= '.';
		$sql.= $this->primary_key;
		return $sql;
	}
}
