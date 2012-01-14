<?php

namespace Nucleus;

class Model {

	protected $table_name;
	protected $identifier;
	protected $pk = 'id';

	public function __toString() {
		return $this->table_name;
	}

	public function pk() {
		return $this->pk;
	}

	public function table_name() {
		return $this->table_name;
	}

	public function set_table_name($table) {
		$this->table_name = $table;
	}

	public function identifier() {
		return $this->identifier;
	}

	public function set_identifier($identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * SQL Select
	 *
	 * Generates the SQL string to identify this record.
	 */
	public function sql_select() {
		$pk = $this->pk();
		$identifier = $this->identifier();
		return "{$identifier}.{$pk} AS `{$identifier}.{$pk}`";
	}

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
			$model = new $model_name;
		}

		else {
			$model = new AnonymousModel;
		}

		$model->set_table_name($table);

		return $model;
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
			'habtm'
		) as $key) {
			if (isset($this->{$key})) {
				$keys = array_keys($this->{$key});
				if (!is_numeric($keys[0])) {
					$this->{$key} = array($this->{$key});
				}
				foreach ($this->{$key} as $join) {
					if (isset($join['as']) && $join['as'] == $name) {
						$join['type'] = $key;
						return $join;
					}
				}
			}
		}

		return FALSE;
	}

}