<?php

class Database_record {
	private $result;
	private $table_name;
	private $pk = 'id';
	private $fk = array();
	private $id;
	private $data;

	public function __construct($result=FALSE, $table_name=FALSE, $data=array()) {
		$this->result = $result;
		$this->set_table_name($table_name);
		$this->set_data($data);
	}

	public function pk() {
		return $this->pk;
	}

	public function fk() {
		return $this->fk;
	}

	public function id() {
		return $this->id;
	}

	public function set_table_name($table_name) {
		$this->table_name = $table_name;
	}

	public function table_name() {
		return $this->table_name;
	}

	public function set_data($key, $value=FALSE) {
		if (is_array($key) && $value == FALSE) {
			foreach ($key as $key => $value) {
				$this->set_data($key, $value);
			}
			return;
		}

		if ($key == $this->pk) {
			$this->id = $value;
		}

		if (preg_match('/_id$/', $key)) {
			$this->fk[$key] = $value;
		}

		$this->data[$key] = $value;
	}

	public function __get($key) {

		if ($value = @$this->data[$key]) {
			return $value;
		}

		// Check for an exact match on the relationship type
		if ($related = $this->result->related($key, $this->id())) {
			return $related;
		}

		// There's no programattic way to determine a has_many v. a has_one. So
		// we'll check that we're not referring to a has_one here. For example,
		// if we had a table `book` and each book had one `cover` then we would
		// check for the table `covers` here.
		else if ($related = $this->result->related(Database::plural($key), $this->id())) {
			return $related->record(0);
		}

		return FALSE;
	}
}