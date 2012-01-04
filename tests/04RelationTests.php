<?php

class RelationTests extends Quiz {
	
	private $db;

	public function __construct() {
		$this->db = new Nucleus_query('192.168.94.31', 'root', 'root');
		$this->db->select_db('tmp');
	}

	public function hasManyClass() {
		$result = $this->db->from('posts')->join('comments')->go();
		$class = get_class($result->record(0)->comments);
		return $class == 'Nucleus_result';
	}

	public function hasManyEntryClass() {
		$class = get_class($this->db->from('posts')->join('comments')->go()->record(0)->comments->record(0));
		return $class == 'Nucleus_record';
	}

	public function hasManyEntryData() {
		$comment = $this->db->from('posts')->join('comments')->go()->record(0)->comments->record('comment');
		return $comment == 'Yea!';
	}

	public function hasManyIterator() {
		$comment = $this->db->from('posts')->join('comments')->go()->record(0)->comments->record(1)->comment;
		return $comment == 'Woot!';
	}

	public function hasOneClass() {
		$result = $this->db->from('comments')->join('posts')->go();
		$class = @get_class($result->record(0)->posts);
		return $class == 'Nucleus_result';
	}

	public function hasOneEntryClass() {
		$result = $this->db->from('comments')->join('posts')->go();
		$class = @get_class($result->record(0)->posts->record(0));
		return $class == 'Nucleus_record';
	}

	public function hasOneEntryData() {
		$result = $this->db->from('comments')->join('posts')->go();
		$title = $result->record(0)->posts->record('title');
		return $title == 'Let\'s save the world';
	}

	public function assumedJoin() {
		$result = $this->db
			->from('comments')
			->join('posts')
			->join('users')
			->go();
		$title = $result->record(0)->posts->record('title');
		$user = $result->record(0)->users->record('name');
		return $title == 'Let\'s save the world' && $user == 'Nina Myers';
	}

	public function multiJoin() {
		$result = $this->db
			->from('comments')
			->join('comments.posts')
			->join('comments.users')
			->go();
		$title = $result->record(0)->posts->record('title');
		$user = $result->record(0)->users->record('name');
		return $title == 'Let\'s save the world' && $user == 'Nina Myers';
	}

	public function nestedJoin() {
		$result = $this->db
			->from('comments')
			->join('comments.posts')
			->join('posts.users')
			->go();
		$title = $result->record(0)->posts->record('title');
		$user = $result->record(0)->posts->record(0)->users->record('name');
		return $title == 'Let\'s save the world' && $user == 'Jack Bauer';
	}

	public function multiNestedJoin() {
		$result = $this->db
			->from('comments')
			->join('comments.users')
			->join('comments.posts')
			->join('posts.users')
			->go();
		$comment = $result->record(0);
		$comment_user = $comment->users->record('name');
		$post_title = $comment->posts->record('title');
		$post_user = $comment->posts->record(0)->users->record('name');

		return $post_title == "Let's save the world" && 
		       $comment_user == 'Nina Myers' &&
		       $post_user == 'Jack Bauer';
	}

}