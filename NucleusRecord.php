<?php

namespace Nucleus;

class Record {
	private $result;
	private $model;
	private $data;

	public function __construct($result=FALSE, $model=FALSE, $data=array()) {
		$this->result = $result;
		$this->model = $model;
		$this->set_data($data);
	}

	public function model() {
		return $this->model;
	}

	public function table_identifier() {
		return $this->model->identifier();
	}

	public function set_data($key, $value=FALSE) {
		if (is_array($key) && $value == FALSE) {
			foreach ($key as $key => $value) {
				$this->set_data($key, $value);
			}
			return;
		}

		$this->data[$key] = $value;
	}

	/**
	 * The `__call` and `__get` magic methods both just run our internal
	 * `__access` method.
	 */
	public function __call($method, $args) {
		return $this->__access($method, $args);
	}
	public function __get($key) {
		return $this->__access($key);
	}

	/**
	 * Or universal getter method checks for three things:
	 * 
	 * 1. If the property is a method on our model. If so we'll call the
	 * model's method and return the result. Additionally, for convienience,
	 * we'll copy our record's data over to the model so the model can refer
	 * to things like `$this->title` or `$this->user_id`.
	 * 2. If the property refers to the data of this record simply return the
	 * value.
	 * 3. If the property refers to a related record or result return the
	 * appropriate result.
	 */
	private function __access($property, $args=array()) {
		if (method_exists($this->model, $property)) {
			foreach ($this->data as $key => $value) {
				$this->model->{$key} = $value;
			}
			$result = call_user_func_array(
				array($this->model, $property),
				$args
			);
			foreach ($this->data as $key => $value) {
				unset($this->model->{$key});
			}
			return $result;
		}

		if (isset($this->data[$property])) {
			return $this->data[$property];
		}

		if ($related = $this->result->related($this, $property)) {
			return $related;
		}

		return FALSE;
	}
}