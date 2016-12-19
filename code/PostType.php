<?php
namespace WPOrbit\PostTypes;

/**
 * Class PostType
 * @package WPOrbit\PostTypes
 * @see
 */
abstract class PostType
{
    /**
     * @var static
     */
    protected static $instance;

    /**
     * PostType constructor.
     */
    private function __construct()
    {}

    /**
     * @return PostType
     */
    public static function getInstance()
    {
        return new static;
    }

    /**
     * @var string The custom post type's key, ie 'post', 'page', 'custom_post_type'.
     */
    protected $key = '';

    /**
     * @var string Singular post type label.
     */
    protected $singular = '';

    /**
     * @var string Plural post type label.
     */
    protected $plural = '';

    /**
     * @var string
     */
    protected $menuName = '';

    /**
     * @var string
     */
    protected $slug = '';

    /**
     * @var array
     */
    protected $supports = ['title', 'editor', 'author', 'thumbnail'];

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getSingular()
    {
        return $this->singular;
    }

    /**
     * @return string
     */
    public function getMenuName()
    {
        return $this->menuName;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getPlural()
    {
        return $this->plural;
    }

    /**
     * @var bool
     */
    protected $customCapabilities = false;

    /**
     * Return a list of customized role capabilities for this post type.
     * @return array
     */
    public function getCustomCapabilities()
    {
        // String filter.
        $filter = function($str) {
            $str = strtolower( $str );
            $str = str_replace( [' ', '-'], '_', $str );
            return $str;
        };

        // Get singular and plural strings.
        $singular = $filter( $this->getSingular() );
        $plural = $filter( $this->getPlural() );

        // Return the edit post.
        return [
            'edit_post'              => "edit_{$singular}",
            'read_post'              => "read_{$singular}",
            'delete_post'            => "delete_{$singular}",
            // primitive/meta caps
            'create_posts'           => "create_{$plural}",
            // primitive caps used outside of map_meta_cap()
            'edit_posts'             => "edit_{$plural}",
            'edit_others_posts'      => "manage_{$plural}",
            'publish_posts'          => "manage_{$plural}",
            'read_private_posts'     => "read",
            // primitive caps used inside of map_meta_cap()
            'read'                   => "read",
            'delete_posts'           => "manage_{$plural}",
            'delete_private_posts'   => "manage_{$plural}",
            'delete_published_posts' => "manage_{$plural}",
            'delete_others_posts'    => "manage_{$plural}",
            'edit_private_posts'     => "edit_{$plural}",
            'edit_published_posts'   => "edit_{$plural}"
        ];
    }

    /**
     * https://codex.wordpress.org/Function_Reference/register_post_type#Arguments
     */
    public function registerPostType()
    {
        add_action( 'init', function()
        {
            $textDomain = 'textdomain';
            $plural = $this->getPlural();
            $singular = $this->getSingular();
            $menuName = '' == $this->getMenuName() ? $this->getPlural() : $this->getMenuName();

            // Prepare post type labels.
            $labels = [
                'name'               => _x( $plural, 'post type general name', $textDomain ),
                'singular_name'      => _x( $singular, 'post type singular name', $textDomain ),
                'menu_name'          => _x( $menuName, 'admin menu', $textDomain ),
                'name_admin_bar'     => _x( $menuName, 'add new on admin bar', $textDomain ),
                'add_new'            => _x( 'Add New', $singular, $textDomain ),
                'add_new_item'       => __( 'Add New ' . $singular . '', $textDomain ),
                'new_item'           => __( 'New ' . $singular . '', $textDomain ),
                'edit_item'          => __( 'Edit ' . $singular . '', $textDomain ),
                'view_item'          => __( 'View ' . $singular . '', $textDomain ),
                'all_items'          => __( 'All ' . $plural . '', $textDomain ),
                'search_items'       => __( 'Search ' . $plural . '', $textDomain ),
                'parent_item_colon'  => __( 'Parent ' . $plural . ':', $textDomain ),
                'not_found'          => __( 'No ' . strtolower($plural) . ' found.', $textDomain ),
                'not_found_in_trash' => __( 'No ' . strtolower($plural) . ' found in Trash.', $textDomain )
            ];

            // Declare arguments.
            $args = [
                'labels'             => $labels,
                'description'        => __( 'Description.', $textDomain ),
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'query_var'          => true,
                'rewrite'            => ['slug' => $this->slug],
                'has_archive'        => true,
                'hierarchical'       => false,
                'menu_position'      => null,
                'supports'           => $this->supports,
            ];

            // If customCapabilities is set to true, then map unique capabilities for this post type.
            if ( $this->customCapabilities ) {
                $args['map_meta_cap'] = true;
                $args['capabilities'] = $this->getCustomCapabilities();
            }

            // Register the post type.
            register_post_type( $this->getKey(), $args );
        });
    }
}