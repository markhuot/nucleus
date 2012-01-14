Nucleus is a zero-conf ORM for PHP. Favoring convention over configuration writing a complex join statement is as simple as:

    $q = new Nucleus\Query();
    $q->from('posts')
    $q->join('weblogs')
    $q->join('categories')
    $q->join('tags')
    $q->join('assets')
    $q->join('comments')
    $posts = $q->go();

Once you've run a query you have full access to its properties and related objects through standard PHP OOP practices. For example:

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

At this point the library is simply a query library. There is no ability to update or save data.