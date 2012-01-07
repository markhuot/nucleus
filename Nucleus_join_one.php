<?php

class Nucleus_join_one extends Nucleus_join {
	public static check($config=array()) {
		$join = new Nucleus_join_one(array_merge(array(
			'primary_key' => $config['foreign_table'].'_id',
			'foreign_key' => 'id'
		), $config));

		return $join->_check_join_columns()?$join:FALSE;
	}
}