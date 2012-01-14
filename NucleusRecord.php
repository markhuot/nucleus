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

	/**
	 * Call
	 *
	 * There are two ways to access record properties, the first is through a
	 * method call matching the key. So something like Post::title() would
	 * look for the `title` column. Both are needed to account for the various
	 * template languages (such as Mustache and Twig which both use different
	 * access methodologies).
	 */
	public function __call($key, $args) {
		
		if (isset($this->data[$key])) {
			return $this->data[$key];
		}

		if ($related = $this->result->related($this, $key)) {
			return $related;
		}

		return FALSE;
	}

	/**
	 * Get
	 *
	 * The second way to find a property is through direct access on the
	 * object. This would happen like so: $post->title and would also look for
	 * a `title` column. Both are needed to account for the various template
	 * languages (such as Mustache and Twig which both use different access
	 * methodologies).
	 */
	public function __get($key) {

		if (isset($this->data[$key])) {
			return $this->data[$key];
		}

		if ($related = $this->result->related($this, $key)) {
			return $related;
		}

		return FALSE;
	}
}