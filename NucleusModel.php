<?php

namespace Nucleus;

class Model {

	private static $identifier_index = 0;
	private static $models = array();

	protected $table_name;
	protected $alias;
	protected $identifier;
	protected $pk = 'id';

	public function __construct() {
		$this->set_identifier('t'.Model::$identifier_index++);
		Model::$models[$this->identifier()] = $this;
	}

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

	public function alias() {
		return $this->alias;
	}

	public function set_alias($alias) {
		$this->alias = $alias;
	}

	/**
	 * SQL Select
	 *
	 * Generates the required SQL string to identify this record.
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
	public static function for_table($table, $alias=FALSE) {
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
		$model->set_alias($alias);

		return $model;
	}

	public static function for_identifier($table_identifier) {
		foreach (Model::$models as $model) {
			if ($model->identifier() == $table_identifier) {
				return $model;
			}
		}
		return FALSE;
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

			// Check that there are joins defined for this key
			if (isset($this->{$key})) {

				// Check that the join has numerical indexes. If it doesn't
				// then our author forgot to nest the join witin an arry so
				// we'll fix that for them.
				$keys = array_keys($this->{$key});
				if (!is_numeric($keys[0])) {
					$this->{$key} = array($this->{$key});
				}

				// Loop through each join.
				foreach ($this->{$key} as $join) {

					// If it's a match return it.
					if (isset($join['as']) && $join['as'] == $name) {
						$join['type'] = $key;
						return $join;
					}

					// If no :as is defined, check the table name
					else if (!isset($join['as']) && $join['foreign_table'] == $name) {
						$join['type'] = $key;
						return $join;
					}
				}
			}
		}

		return FALSE;
	}

}