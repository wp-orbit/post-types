# Post Types

via WP Orbit

### PostType Class

This package provides an extensible *PostType* class which abstracts creating custom post types.

#### Basic usage:

For each custom post type you need, extend the base PostType class, then call registerPostType().

```php
<?php
use WPOrbit\PostTypes\PostType;

// Extend PostType
class AuthorPostType extends PostType
{
    protected function __construct()
    {
        $this->key = 'author';
        $this->slug = 'authors';
        $this->singular = 'Author';
        $this->plural = 'Authors';
    }
}

class BookPostType extends PostType
{
    protected function __construct()
    {
        $this->key = 'book';
        $this->slug = 'books';
        $this->singular = 'Book';
        $this->plural = 'Books';
    }
}

// Hook into WordPress...
AuthorPostType::getInstance()->registerPostType();
BookPostType::getInstance()->registerPostType();
```

#### Post Type Support

By default, the class is set to support: 'title', 'editor', 'author', and 'thumbnail'. 
Override *$this->supports* in the extending class' constructor to add other default functionality 
to your post type.

```php
<?php
protected function __construct() {
    $this->key = 'book';
    $this->slug = 'books';
    $this->singular = 'Book';
    $this->plural = 'Books';
    $this->supports = ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'];
}
```