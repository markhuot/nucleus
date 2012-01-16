Nucleus is a zero-conf ORM for PHP. Once loaded into your framework of choice it's easy to pull back objects:

```php
<?php
$posts = $nucleus->get('posts');
```

Favoring convention over configuration writing a complex join statement is as simple as specifying which tables to include. Using standard naming conventions an entire SQL statement can be generated by simply running:

```php
<?php
$posts = $nucleus->get('posts, users, tags, comments, comments.users')
```

Once you've run a query you have full access to its properties and related objects through standard PHP OOP practices. For example:

```php
<?php
foreach ($posts as $post) {
	// The post title
	echo $post->title;

	// The related comments
	foreach ($post->comments as $comment) {

		// Comment data
		echo $comment->text;

		// Nested data
		echo $comment->user->name;
	}
}
```

At this point the library is simply a query library. There is no ability to update or save data.