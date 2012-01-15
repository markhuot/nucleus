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

	public function data($key=FALSE) {
		if ($key) {
			return @$this->data[$key];
		}

		return $this->data;
	}

	/**
	 * ID
	 *
	 * Get the value of the primary key
	 */
	public function id() {
		return $this->data($this->model->pk());
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
		return $this->__get($key);
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