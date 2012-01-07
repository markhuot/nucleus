<?php

namespace Nucleus;

class Join_one extends Join {
	public static check($config=array()) {
		$join = new Join_one(array_merge(array(
			'primary_key' => $config['foreign_table'].'_id',
			'foreign_key' => 'id'
		), $config));

		return $join->_check_join_columns()?$join:FALSE;
	}
}