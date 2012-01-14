<?php

namespace Nucleus;

class Record {
	private $_result;
	private $_table_name;
	private $_table_identifier;
	private $_pk = 'id';
	private $_data;

	public function __construct($result=FALSE, $id=FALSE, $data=array()) {
		$this->set_result($result);
		$this->set_table_identifier($id);
		$this->set_data($data);
	}

	public function pk() {
		return $this->_pk;
	}

	public function id() {
		return $this->_data[$this->pk()];
	}

	public function table_identifier() {
		return $this->_table_identifier;
	}

	public function set_table_identifier($table_identifier) {
		$this->_table_identifier = $table_identifier;
	}

	public function set_result($result) {
		$this->_result = $result;
	}

	public function set_data($key, $value=FALSE) {
		if (is_array($key) && $value == FALSE) {
			foreach ($key as $key => $value) {
				$this->set_data($key, $value);
			}
			return;
		}

		$this->{$key} = $value;
		$this->_data[$key] = $value;
	}

	public function data($key=FALSE) {
		if ($key) {
			return @$this->_data[$key];
		}

		return $this->_data;
	}

	public function __get($key) {

		if (isset($this->_data[$key])) {
			return $this->_data[$key];
		}

		if ($related = $this->_result->related($key, $this)) {
			return $related;
		}

		return FALSE;
	}
}