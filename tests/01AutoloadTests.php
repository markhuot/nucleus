<?php

require_once dirname(__FILE__).'/../Nucleus.php';

class AutoloadTests extends Quiz {
	
	public function databaseDefined() {
		return class_exists('Nucleus');
	}

	public function queryDefined() {
		return class_exists('Nucleus_query');
	}

	public function resultDefined() {
		return class_exists('Nucleus_result');
	}

	public function recordDefined() {
		return class_exists('Nucleus_record');
	}

}