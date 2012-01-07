<?php

class Nucleus_join_many extends Nucleus_join {
	public static check($config=array()) {
		$join = new Nucleus_join_many(array_merge(array(
			'primary_key' => 'id',
			'foreign_key' => Nucleus::singular($config['primary_table']).'_id'
		), $config));

		return $join->_check_join_columns()?$join:FALSE;
	}
}