<?php

class Nucleus_join_many_many extends Nucleus_join {
	public static check($config=array()) {
		$join_table = join_table_name($config['primary_table'], $config['foreign_table']);

		$join = new Nucleus_join_many_many(array_merge(array(
			'join_table' => $join_table,
			'join_id' => $this->add_table($join_table),
			'join_primary_key' => Nucleus::singular($config['primary_table']).'_id',
			'primary_key' => 'id',
			'foreign_key' => Nucleus::singular($config['foreign_table']).'_id'
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
	
	protected function sql() {
		$sql = ' '.strtoupper($config['type']);
		$sql.= ' JOIN ';
		$sql.= $config['join_table'];
		$sql.= ' AS ';
		$sql.= $config['join_id'];
		$sql.= ' ON ';
		$sql.= $config['join_id'];
		$sql.= '.';
		$sql.= $config['join_primary_key'];
		$sql.= '=';
		$sql.= $config['primary_id'];
		$sql.= '.';
		$sql.= $config['primary_key'];
		$sql.= ' ';
		$sql.= strtoupper($config['type']);
		$sql.= ' JOIN ';
		$sql.= $config['foreign_table'];
		$sql.= ' AS ';
		$sql.= $config['foreign_id'];
		$sql.= ' ON ';
		$sql.= $config['foreign_id'];
		$sql.= '.';
		$sql.= 'id';
		$sql.= '=';
		$sql.= $config['join_id'];
		$sql.= '.';
		$sql.= $config['foreign_key'];
		return $sql;
	}
}