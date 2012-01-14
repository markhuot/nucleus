<?php

namespace Nucleus;

class Record {
	private $result;
	private $table_name;
	private $table_identifier;
	private $pk = 'id';
	private $data;

	public function __construct($result=FALSE, $id=FALSE, $data=array()) {
		$this->set_result($result);
		$this->set_table_identifier($id);
		$this->set_data($data);
	}

	public function pk() {
		return $this->pk;
	}

	public function id() {
		return $this->data[$this->pk()];
	}

	public function table_identifier() {
		return $this->table_identifier;
	}

	public function set_table_identifier($table_identifier) {
		$this->table_identifier = $table_identifier;
	}

	public function set_result($result) {
		$this->result = $result;
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

	public function data($key=FALSE) {
		if ($key) {
			return @$this->data[$key];
		}

		return $this->data;
	}

	public function __call($method, $args) {
		if (isset($this->data[$method])) {
			return $this->data[$method];
		}

		return FALSE;
	}

	public function __get($key) {

		if (isset($this->data[$key])) {
			return $this->data[$key];
		}

		if ($related = $this->result->related($key, $this)) {
			return $related;
		}

		return FALSE;
	}
}