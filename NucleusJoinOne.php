<?php

namespace Nucleus;

class JoinOne extends Join {
	public static function check($config=array()) {
		$join = new JoinOne(array_merge(array(
			'primary_key' => singular($config['foreign_table']).'_id',
			'foreign_key' => 'id'
		), $config));

		return $join->_check_join_columns()?$join:FALSE;
	}
}
