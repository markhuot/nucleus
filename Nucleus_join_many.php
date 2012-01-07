<?php

class Nucleus;

class Join_many extends Join {
	public static check($config=array()) {
		$join = new Join_many(array_merge(array(
			'primary_key' => 'id',
			'foreign_key' => singular($config['primary_table']).'_id'
		), $config));

		return $join->_check_join_columns()?$join:FALSE;
	}
}