<?php
namespace Rmcc;
use Timber\Timber;

class DirectToCheckout extends Timber {

  public function __construct() {
    parent::__construct();
    add_filter('timber/twig', array($this, 'add_to_twig'));
    add_filter('timber/context', array($this, 'add_to_context'));
    add_action('plugins_loaded', array($this, 'plugin_timber_locations'));
    add_action('init', array($this, 'plugin_text_domain_init')); 
    add_filter('wc_get_template', array($this, 'wc_get_template'), 10, 5);
    add_filter('page_template', array($this, 'direct_to_checkout_template'));
    
    // after plugins are loaded, do this...
    add_action('plugins_loaded', array($this, 'direct_to_checkout_prep'));
    add_action('wp', array($this, 'direct_to_checkout_pre_wp'));
  }
  
  // timber stuff. the usual stuff
  public function add_to_twig($twig) { 
    if(!class_exists('Twig_Extension_StringLoader')){
      $twig->addExtension(new Twig_Extension_StringLoader());
    }
    return $twig;
  }
  public function add_to_context($context) {
    $context['site'] = new \Timber\Site;
    $context['direct_to_checkout_url'] = DIRECT_TO_CHECKOUT_URL;
    // the last page
    $context['back_link'] = $_SERVER['HTTP_REFERER'];
    return $context;    
  }
  
  // plugins timber locations & text domain
  public function plugin_timber_locations() {
    // if timber::locations is empty (another plugin hasn't already added to it), make it an array
    if(!Timber::$locations) Timber::$locations = array();
    // add a new views path to the locations array
    array_push(
      Timber::$locations, 
      DIRECT_TO_CHECKOUT_PATH . 'views',
      DIRECT_TO_CHECKOUT_PATH . 'views/checkout',
      DIRECT_TO_CHECKOUT_PATH . 'views/checkout/details'
    );
  }
  public function plugin_text_domain_init() {
    load_plugin_textdomain('direct-to-checkout', false, DIRECT_TO_CHECKOUT_BASE. '/languages');
  }
  
  // allow plugin's woo templates to override others
  public function wc_get_template($located, $template_name, $args, $template_path, $default_path) {
    
    // if defult path deosnt exists, set it
    if (!$default_path) {
      global $woocommerce;
      $default_path = $woocommerce->plugin_path() . '/templates/';
    }

    $plugin_path = DIRECT_TO_CHECKOUT_PATH.'woocommerce/'.$template_name;
    
    // if the file exists in current plugin, set located to that
    if(@file_exists($plugin_path)) {
      $located = $plugin_path;
    } elseif(@file_exists($located) && !@file_exists($plugin_path)) {
      $located = $located;
    } elseif(!@file_exists($located) && !@file_exists($plugin_path) && @file_exists($default_path)) {
      $located = $default_path;
    }

    return $located;
  }
  
  // override the template used for the checkout page. would be page.php from the theme but for this
  public function direct_to_checkout_template($page_template) {
    // if is the checkout page, use the new template
    if (is_checkout() && empty(is_wc_endpoint_url('order-received'))) {
      $page_template = DIRECT_TO_CHECKOUT_PATH . 'templates/direct-to-checkout-template.php';
    }
    return $page_template;
  }
  
  // basically prepatory work to be done before anything more comprehensive
  public function direct_to_checkout_prep() {
    // the redirects setup. global
    new CheckoutRedirects;
    // initialize the order item removal class. for removing items from the order form via ajax
    new ProductRemove;
    // initialize the order item quantity select class. for changing order items quantity on checkout via ajax
    new ProductQuantity;
    // remove basket button from minicart
    remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10);
    // change the added to cart view basket button link & text
    add_filter( 'woocommerce_get_script_data', array($this, 'change_archives_view_cart_link'),10,2 );
    // added to cart message: change link to checkout
    add_filter( 'wc_add_to_cart_message_html', array($this, 'added_to_cart_message_html'), 10, 2 );
  }
  // basically prepatory work to be done before anything more comprehensive
  public function direct_to_checkout_pre_wp() {
    // checkout specific, or at least not on the orer received page
    if (empty(is_wc_endpoint_url('order-received'))) {
      if (is_checkout()) {
        // initialize the assets class. for styles & scripts management
        new CheckoutAssets;
        // initialize the checkout fields class. for managing the checkout fields stuff
        new CheckoutFields;
        // remove payments from order review. the markup for this is added back in manually. see direct-to-checkout.twig
        remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
        // disable order notes from the checkout
        add_filter('woocommerce_enable_order_notes_field', '__return_false');
        // Make “Create an account” to be default Checked
        add_filter('woocommerce_create_account_default_checked', '__return_true');
      }
    }
  }
  
  public function change_archives_view_cart_link($params, $handle) {
    switch ($handle) {
      case 'wc-add-to-cart':
        $params['i18n_view_cart'] = _x( 'Go to checkout', 'Added to cart button text', 'direct-to-checkout' ); //chnage Name of view cart button
        $params['cart_url'] = wc_get_checkout_url();
      break;
    }
    return $params;
  }
  
  public function added_to_cart_message_html( $message, $products ) {
  
  	$count = 0;
  	$titles = array();
  	foreach ( $products as $product_id => $qty ) {
  		$titles[] = ( $qty > 1 ? absint( $qty ) . ' &times; ' : '' ) . sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'direct-to-checkout' ), strip_tags( get_the_title( $product_id ) ) );
  		$count += $qty;
  	}
  
  	$titles     = array_filter( $titles );
  	$added_text = sprintf( _n(
  		'You have added %s', // Singular
  		'You have added %s', // Plural
  		$count, // Number of products added
  		'direct-to-checkout' // Textdomain
  	), wc_format_list_of_items( $titles ) );
  	$message    = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', wc_get_checkout_url(), _x( 'Go to checkout', 'Added to cart button text', 'direct-to-checkout' ), esc_html( $added_text ) );
  
  
  	return $message;
  }
  
}