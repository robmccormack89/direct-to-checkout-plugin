<?php
namespace Rmcc;
use Timber\Timber;

// set where to look for timber files
Timber::$locations = array(
  BAREBONES_TIMBER_VIEWS,
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

    // return context
    return $context;    
  }

  public function register_post_types() {
    // register some post types
  }

  public function plugin_enqueue_assets() {
    // enqueue some assets
  }

}
new BarebonesTimber;