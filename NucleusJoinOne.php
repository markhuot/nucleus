<?php

namespace Nucleus;

class JoinOne extends Join {
	public static function check($config=array()) {
		if (isset($config['type']) && !in_array($config['type'], array('one'))) {
			return FALSE;
		}

		$join = new JoinOne(array_merge(array(
			'as' => singular($config['foreign_table']),
			'primary_key' => singular($config['foreign_table']).'_id',
			'foreign_key' => 'id'
		), $config));

		return $join->_check_join_columns()?$join:FALSE;
	}
}
