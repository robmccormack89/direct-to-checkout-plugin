<?php
namespace Rmcc;
use Timber\Timber;

// set where to look for timber files
Timber::$locations = array(
  BAREBONES_TIMBER_VIEWS,
  BAREBONES_TIMBER_VIEWS.'/winner',
  BAREBONES_TIMBER_VIEWS.'/list',
);

class BarebonesTimber extends Timber {

  public function __construct() {
    // timber stuff. the usual stuff
    add_filter( 'timber/twig', array($this, 'add_to_twig'));
    add_filter( 'timber/context', array($this, 'add_to_context'));
    
    // plugin stuff. these actions will be baked in
    add_action('init', array($this, 'register_post_types')); // register cpts on init action
    add_action('wp_enqueue_scripts', array($this, 'plugin_enqueue_assets')); // enqueue assets on wp_enqueue_scripts action
    
    // allow addition of custom page templates to page attributes dropdown
    add_action('plugins_loaded', array('Rmcc\CustomPageTemplater', 'get_instance'));

    parent::__construct();
  }

  public function add_to_twig($twig) { 
    /* this is where you can add your own functions to twig */
    $twig->addExtension(new \Twig_Extension_StringLoader());
    
    return $twig;
  }

  public function add_to_context($context) {
    $context['some_plugin_data'] = 'Lorem Ipsum Dolor...';
    
    // cpts
    $context['is_winners'] = is_post_type_archive( 'winners' );
    $context['is_entry_lists'] = is_post_type_archive( 'entry_lists' );

    // return context
    return $context;    
  }

  public function register_post_types() {
    $labels_winners = array(
      'name'                  => _x( 'Competition Winners', 'Competition Winners label: Plural', 'barebones-timber-plugin' ),
      'singular_name'         => _x( 'Competition Winner', 'Competition Winners label: Singular', 'barebones-timber-plugin' ),
      'menu_name'             => _x( 'Competition Winners', 'Competition Winners label: Plural', 'barebones-timber-plugin' ),
      'name_admin_bar'        => _x( 'Competition Winner', 'Competition Winners label: Singular', 'barebones-timber-plugin' ),
      'archives'              => _x( 'Competition Winners', 'Competition Winners label: Archive', 'barebones-timber-plugin' ),
      'attributes'            => 'Item Attributes',
      'parent_item_colon'     => 'Parent Item:',
      'all_items'             => 'All Items',
      'add_new_item'          => 'Add New Item',
      'add_new'               => 'Add New',
      'new_item'              => 'New Item',
      'edit_item'             => 'Edit Item',
      'update_item'           => 'Update Item',
      'view_item'             => 'View Item',
      'view_items'            => 'View Items',
      'search_items'          => 'Search Item',
      'not_found'             => 'Not found',
      'not_found_in_trash'    => 'Not found in Trash',
      'featured_image'        => 'Featured Image',
      'set_featured_image'    => 'Set featured image',
      'remove_featured_image' => 'Remove featured image',
      'use_featured_image'    => 'Use as featured image',
      'insert_into_item'      => 'Insert into item',
      'uploaded_to_this_item' => 'Uploaded to this item',
      'items_list'            => 'Items list',
      'items_list_navigation' => 'Items list navigation',
      'filter_items_list'     => 'Filter items list',
    );
    $args_winners = array(
      'label'                 => _x( 'Competition Winner', 'Competition Winners label: Singular', 'barebones-timber-plugin' ),
      'description'           => _x( 'Lucky Competition Winners...', 'Competition Winners description', 'barebones-timber-plugin' ),
      'labels'                => $labels_winners,
      'supports'              => array( 'title', 'editor', 'thumbnail', 'comments', 'revisions', 'custom-fields', 'page-attributes' ),
      'hierarchical'          => false,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 3,
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => true,
      'can_export'            => true,
      'has_archive'           => 'competition-winners',
      'exclude_from_search'   => true,
      'publicly_queryable'    => true,
      'query_var'             => false,
      'capability_type'       => 'page',
      'show_in_rest'          => false,
    );
    register_post_type( 'winners', $args_winners );

    $labels_lists = array(
      'name'                  => _x( 'Entry Lists', 'Entry Lists label: Plural', 'barebones-timber-plugin' ),
      'singular_name'         => _x( 'Entry List', 'Entry Lists label: Singular', 'barebones-timber-plugin' ),
      'menu_name'             => _x( 'Entry Lists', 'Entry Lists label: Plural', 'barebones-timber-plugin' ),
      'name_admin_bar'        => _x( 'Entry List', 'Entry Lists label: Singular', 'barebones-timber-plugin' ),
      'archives'              => _x( 'Entry Lists', 'Entry Lists label: Archive', 'barebones-timber-plugin' ),
      'attributes'            => 'Item Attributes',
      'parent_item_colon'     => 'Parent Item:',
      'all_items'             => 'All Items',
      'add_new_item'          => 'Add New Item',
      'add_new'               => 'Add New',
      'new_item'              => 'New Item',
      'edit_item'             => 'Edit Item',
      'update_item'           => 'Update Item',
      'view_item'             => 'View Item',
      'view_items'            => 'View Items',
      'search_items'          => 'Search Item',
      'not_found'             => 'Not found',
      'not_found_in_trash'    => 'Not found in Trash',
      'featured_image'        => 'Featured Image',
      'set_featured_image'    => 'Set featured image',
      'remove_featured_image' => 'Remove featured image',
      'use_featured_image'    => 'Use as featured image',
      'insert_into_item'      => 'Insert into item',
      'uploaded_to_this_item' => 'Uploaded to this item',
      'items_list'            => 'Items list',
      'items_list_navigation' => 'Items list navigation',
      'filter_items_list'     => 'Filter items list',
    );
    $args_lists = array(
      'label'                 => _x( 'Entry List', 'Entry Lists label: Singular', 'barebones-timber-plugin' ),
      'description'           => _x( 'Competition Entry List...', 'Entry Lists description', 'barebones-timber-plugin' ),
      'labels'                => $labels_lists,
      'supports'              => array( 'title', 'editor', 'revisions', 'custom-fields', 'page-attributes' ),
      'hierarchical'          => false,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 4,
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => true,
      'can_export'            => true,
      'has_archive'           => 'entry-lists',
      'exclude_from_search'   => true,
      'publicly_queryable'    => true,
      'query_var'             => false,
      'capability_type'       => 'page',
    );
    register_post_type( 'entry_lists', $args_lists );
  }

  public function plugin_enqueue_assets() {
    // enqueue some assets
  }

}
new BarebonesTimber;