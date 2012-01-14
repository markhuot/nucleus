<?php

namespace Nucleus;

class Model {

	/**
	 * Load Model for Table
	 *
	 * Takes a table name and loads the corresponding model file. If no model 
	 * is defined we'll create an anomyous model on the author's behalf.
	 */
	public static function for_table($table) {
		$model_base_path = \Nucleus::config('model_path');
		$model_name = ucfirst(singular($table)).'Model';
		$model_path = "{$model_base_path}/".$model_name.'.php';
		if (file_exists($model_path)) {
			require_once $model_path;
			return new $model_name;
		}

		else {
			return new AnonymousModel;
		}
	}

	/**
	 * Join Named
	 *
	 * Look through each of the joins defined on this model and check for any
	 * matching the requested name.
	 */
	public function join_named($name) {
		foreach (array(
			'has_one',
			'has_many',
			'habtm',
			'has_and_belongs_to_many'
		) as $key) {
			if (isset($this->{$key})) {
				$keys = array_keys($this->{$key});
				if (!is_numeric($keys[0])) {
					$this->{$key} = array($this->{$key});
				}
				foreach ($this->{$key} as $join) {
					if (isset($join['as']) && $join['as'] == $name) {
						return $join;
					}
				}
			}
		}

		return FALSE;
	}

}