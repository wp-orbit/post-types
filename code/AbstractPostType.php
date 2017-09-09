<?php

namespace WPOrbit\PostTypes;

/**
 * Class AbstractPostType
 *
 * @package WPOrbit\PostTypes
 */
abstract class AbstractPostType {
	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * @return AbstractPostType
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	////////////////////////////

	protected function __construct( $args = [] ) {

		$args = wp_parse_args( $args, [
			'text_domain'             => 'wp_orbit',
			'key'                     => '',
			'singular'                => '',
			'plural'                  => '',
			'slug'                    => '',
			'supports'                => [ 'title', 'editor', 'author', 'thumbnail' ],
			'show_ui'                 => true,
			'show_in_menu'            => true,
			'use_custom_capabilities' => false
		] );

		// Set default arguments.
		foreach ( $args as $key => $arg ) {
			if ( property_exists( static::class, $key ) ) {
				$this->{$key} = $arg;
			}
		}

		// Register post type on 'init'.
		add_action( 'init', [ $this, 'register_post_type' ] );
	}

	/** @var string */
	protected $text_domain = '';

	/** @var bool Whether or not this class has already been initialized. */
	protected $has_initialized = false;

	/** @var string The custom post type's key, ie 'post', 'page', 'custom_post_type'. */
	protected $key = '';

	/**@var string Singular post type label. */
	protected $singular = '';

	/** @var string Plural post type label. */
	protected $plural = '';

	/** @var string */
	protected $menu_name = '';

	/** @var string */
	protected $slug = '';

	/** @var array */
	protected $supports = [];

	/**
	 * Whether to generate a default UI for managing this post type in the admin.
	 *
	 * @var bool
	 */
	protected $show_ui = true;

	/**
	 * Where to show the post type in the admin menu. show_ui must be true.
	 *
	 * Default: value of show_ui argument
	 * 'false' - do not display in the admin menu
	 * 'true' - display as a top level menu
	 * 'some string' - If an existing top level page such as 'tools.php' or 'edit.php?post_type=page', the post type will be placed as a sub menu of that.
	 */
	protected $show_in_menu = true;

	/** @return string */
	public function get_key() {
		return $this->key;
	}

	/** @return string */
	public function get_singular() {
		return $this->singular;
	}

	/** @return string */
	public function get_menu_name() {
		return $this->menu_name;
	}

	/** @return string */
	public function get_slug() {
		return $this->slug;
	}

	/** @return string */
	public function get_plural() {
		return $this->plural;
	}

	/** @var bool
	 */
	protected $use_custom_capabilities = false;

	/**
	 * Return a list of customized role capabilities for this post type.
	 *
	 * @return array
	 */
	public function get_custom_capabilities() {
		// String filter.
		$filter = function ( $str ) {
			$str = strtolower( $str );
			$str = str_replace( [
				' ',
				'-'
			], '_', $str );

			return $str;
		};

		// Get singular and plural strings.
		$singular = $filter( $this->get_singular() );
		$plural   = $filter( $this->get_plural() );

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

	public function get_args()
	{
		$text_domain = $this->text_domain;
		$plural      = $this->get_plural();
		$singular    = $this->get_singular();
		$menuName    = '' == $this->get_menu_name() ? $this->get_plural() : $this->get_menu_name();

		// Prepare post type labels.
		$labels = [
			'name'               => _x( $plural, 'post type general name', $text_domain ),
			'singular_name'      => _x( $singular, 'post type singular name', $text_domain ),
			'menu_name'          => _x( $menuName, 'admin menu', $text_domain ),
			'name_admin_bar'     => _x( $menuName, 'add new on admin bar', $text_domain ),
			'add_new'            => _x( 'Add New', $singular, $text_domain ),
			'add_new_item'       => __( 'Add New ' . $singular . '', $text_domain ),
			'new_item'           => __( 'New ' . $singular . '', $text_domain ),
			'edit_item'          => __( 'Edit ' . $singular . '', $text_domain ),
			'view_item'          => __( 'View ' . $singular . '', $text_domain ),
			'all_items'          => __( 'All ' . $plural . '', $text_domain ),
			'search_items'       => __( 'Search ' . $plural . '', $text_domain ),
			'parent_item_colon'  => __( 'Parent ' . $plural . ':', $text_domain ),
			'not_found'          => __( 'No ' . strtolower( $plural ) . ' found.', $text_domain ),
			'not_found_in_trash' => __( 'No ' . strtolower( $plural ) . ' found in Trash.', $text_domain )
		];

		// Declare arguments.
		$args = [
			'labels'             => $labels,
			'description'        => __( 'Description.', $text_domain ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => $this->show_ui,
			'show_in_menu'       => $this->show_in_menu,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => $this->slug ],
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => $this->supports,
		];

		// If custom_capabilities is set to true, then map unique capabilities for this post type.
		if ( $this->use_custom_capabilities ) {
			$args['map_meta_cap'] = true;
			$args['capabilities'] = $this->get_custom_capabilities();
		}

		$class_name = static::class;
		return apply_filters( "wp_orbit_register_post_type_args", $args, $class_name );

	}

	/**
	 * https://codex.wordpress.org/Function_Reference/register_post_type#Arguments
	 */
	public function register_post_type() {
		if ( $this->has_initialized ) {
			return;
		}

		// Register the post type.
		register_post_type( $this->get_key(), $this->get_args() );

		// Set initialized state.
		$this->has_initialized = true;
	}
}