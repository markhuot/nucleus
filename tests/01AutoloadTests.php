<?php

require_once dirname(__FILE__).'/../Database.php';

class AutoloadTests extends Quiz {
	
	public function databaseDefined() {
		return class_exists('Database');
	}

	public function queryDefined() {
		return class_exists('Database_query');
	}

	public function resultDefined() {
		return class_exists('Database_result');
	}

	public function recordDefined() {
		return class_exists('Database_record');
	}

}