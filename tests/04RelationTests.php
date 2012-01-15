<?php

class RelationTests extends Quiz {
	
	private $db;

	public function __construct() {
		$this->conn = new Nucleus\Connection('mysql:host=192.168.94.31;dbname=tmp', 'root', 'root');
		$this->db = new \Nucleus\Query();
	}

	public function hasManyClass() {
		$result = $this->db->from('posts')->join('comments')->go();
		$class = get_class($result->record(0)->comments);
		return $class == 'Nucleus\Result';
	}

	public function hasManyEntryClass() {
		$class = get_class($this->db->from('posts')->join('comments')->go()->record(0)->comments->record(0));
		return $class == 'Nucleus\Record';
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
		$class = @get_class($result->record(0)->post);
		return $class == 'Nucleus\Record';
	}

	public function hasOneEntryClass() {
		$result = $this->db->from('comments')->join('posts')->go();
		$class = @get_class($result->record(0)->post);
		return $class == 'Nucleus\Record';
	}

	public function hasOneEntryData() {
		$result = $this->db->from('comments')->join('posts')->go();
		$title = $result->record(0)->post->title;
		return $title == 'Let\'s save the world';
	}

	public function assumedJoin() {
		$result = $this->db
			->from('comments')
			->join('posts')
			->join('users')
			->go();
		$title = $result->record(0)->post->title;
		$user = $result->record(0)->user->name;
		return $title == 'Let\'s save the world' && $user == 'Nina Myers';
	}

	public function multiJoin() {
		$result = $this->db
			->from('comments')
			->join('comments.posts')
			->join('comments.users')
			->go();
		$title = $result->record(0)->post->title;
		$user = $result->record(0)->user->name;
		return $title == 'Let\'s save the world' && $user == 'Nina Myers';
	}

	public function nestedJoin() {
		$result = $this->db
			->from('comments')
			->join('comments.posts')
			->join('posts.users')
			->go();
		$title = $result->record(0)->post->title;
		$user = $result->record(0)->post->user->name;
		return $title == 'Let\'s save the world' && $user == 'Jack Bauer';
	}

	public function megaNestedJoin() {
		$result = $this->db
			->from('posts')
			->join('posts.users')
			->join('comments')
			->join('comments.users')
			->join('users.avatars')
			->go();
		$title = $result->record(0)->title;
		$user1 = $result->record(0)->user->name;
		$user2 = $result->record(0)->comments->record(0)->user->name;
		$avatar1 = $result->record(0)->user->avatar->url;
		return $title == 'Let\'s save the world' &&
		       $user1 == 'Jack Bauer' &&
		       $user2 == 'Nina Myers' &&
		       $avatar1 == 'Jack\'s Avatar';
	}

	public function multiNestedJoin() {
		$result = $this->db
			->from('comments')
			->join('comments.users')
			->join('comments.posts')
			->join('posts.users')
			->go();
		$comment = $result->record(0);
		$comment_user = $comment->user->name;
		$post_title = $comment->post->title;
		$post_user = $comment->post->user->name;

		return $post_title == "Let's save the world" && 
		       $comment_user == 'Nina Myers' &&
		       $post_user == 'Jack Bauer';
	}

	public function manyManyJoin() {
		$result = $this->db
			->from('posts')
			->join('categories')
			->go();
		$class = get_class($result->record(0)->categories);
		return $class == 'Nucleus\Result';
	}

	public function explicitDefinition() {
		$result = $this->db
			->from('posts')
			->join(array(
				'type' => 'habtm',
				'primary_table' => 'posts',
				'foreign_table' => 'categories'
			))
			->join('users', array(
				'type' => 'has_one',
				'foreign_table' => 'users',
				'as' => 'author'
			))
			->go();
		$class1 = get_class($result->record(0)->categories);
		$class2 = get_class($result->record(0)->author);
		return $class1 == 'Nucleus\Result' && $class2 == 'Nucleus\Record';
	}

	public function multiFrom() {
		$result = $this->db->get('posts, comments');
		return $result->record(0)->comments;
	}
	
	public function aliased() {
		$result = $this->db->get('posts as p, comments as c');
		return $result->record(0)->c;
	}

}
