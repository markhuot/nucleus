<?php

namespace Nucleus;

class JoinManyMany extends Join {
	public static function check($config=array()) {
		$join_table = join_table_name($config['primary_table'], $config['foreign_table']);

		$join = new JoinManyMany(array_merge(array(
			'join_table' => $join_table,
			'join_id' => $config['foreign_id'].'j',
			'join_primary_key' => singular($config['foreign_table']).'_id',
			'primary_key' => 'id',
			'foreign_key' => singular($config['primary_table']).'_id'
		), $config));

		return $join->_check_join_columns()?$join:FALSE;
	}
	
	protected function _check_join_columns() {
		if (!$this->table_has_columns(
			$this->join_table,
			$this->join_primary_key,
			$this->foreign_key)) {
			return FALSE;
		}

		return TRUE;
	}
	
	public function sql() {
		$sql = ' '.strtoupper($this->type);
		$sql.= ' JOIN ';
		$sql.= $this->join_table;
		$sql.= ' AS ';
		$sql.= $this->join_id;
		$sql.= ' ON ';
		$sql.= $this->join_id;
		$sql.= '.';
		$sql.= $this->foreign_key;
		$sql.= '=';
		$sql.= $this->primary_id;
		$sql.= '.';
		$sql.= $this->primary_key;
		$sql.= ' ';
		$sql.= strtoupper($this->type);
		$sql.= ' JOIN ';
		$sql.= $this->foreign_table;
		$sql.= ' AS ';
		$sql.= $this->foreign_id;
		$sql.= ' ON ';
		$sql.= $this->foreign_id;
		$sql.= '.';
		$sql.= 'id';
		$sql.= '=';
		$sql.= $this->join_id;
		$sql.= '.';
		$sql.= $this->join_primary_key;
		return $sql;
	}
}
