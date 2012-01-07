<?php

namespace Nucleus;

class Connection extends \PDO {
	static $connections = array();
	
	public function __construct($dsn=NULL, $user=NULL, $pass=NULL) {
		parent::__construct($dsn, $user, $pass);
		self::$connections[] = $this;
	}
}