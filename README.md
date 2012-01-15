Nucleus is a zero-conf ORM for PHP. Once loaded into your framework of choice it's easy to pull back objects:

```php
<?php
$post = $this->nucleus->get('posts');
```

Favoring convention over configuration writing a complex join statement is as simple as:

```php
<?php
$this->nucleus->from('posts')
$this->nucleus->join('weblogs')
$this->nucleus->join('categories')
$this->nucleus->join('tags')
$this->nucleus->join('assets')
$this->nucleus->join('comments')
$posts = $this->nucleus->go();
```

Or…

```php
<?php
$this->nucleus->from('posts')
$this->nucleus->join('posts.users')
$this->nucleus->join('posts.tags')
$this->nucleus->join('posts.comments')
$this->nucleus->join('posts.comments.users')
$posts = $this->nucleus->go();
```

Or…

```php
<?php
$posts = $this->nucleus->get('posts, users, tags, comments, comments.users')
```

Once you've run a query you have full access to its properties and related objects through standard PHP OOP practices. For example:

```php
<?php
foreach ($posts as $post) {
	// The post title
	echo $post->title;

	// The related categories
	foreach ($post->categories as $category) {
		$category->name;
	}

	// The related weblog
	echo $post->weblog->name;
}
```

At this point the library is simply a query library. There is no ability to update or save data.