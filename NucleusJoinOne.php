<?php

namespace Nucleus;

class JoinOne extends Join {
	public static check($config=array()) {
		$join = new JoinOne(array_merge(array(
			'primary_key' => $config['foreign_table'].'_id',
			'foreign_key' => 'id'
		), $config));

		return $join->_check_join_columns()?$join:FALSE;
	}
}
