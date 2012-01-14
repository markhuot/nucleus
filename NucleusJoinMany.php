<?php

namespace Nucleus;

class JoinMany extends Join {
	public static function check($config=array()) {
		if (isset($config['type']) && !in_array($config['type'], array('many'))) {
			return FALSE;
		}

		$join = new JoinMany(array_merge(array(
			'primary_key' => 'id',
			'foreign_key' => singular($config['primary_table']).'_id'
		), $config));

		return $join->_check_join_columns()?$join:FALSE;
	}
}
