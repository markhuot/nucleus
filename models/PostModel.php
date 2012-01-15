<?php

class PostModel extends \Nucleus\Model {
	
	protected $habtm = array(
		array(
			'as' => 'tagged',
			'join_table' => 'posts_tagged',
			'foreign_table' => 'tags'
		)
	);

}