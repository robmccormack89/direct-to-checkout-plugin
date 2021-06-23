<?php
namespace Rmcc;
use Timber\Timber;

class DirectToCheckout extends Timber {

  public function __construct() {
    parent::__construct();
    
    // timber stuff. the usual stuff
    add_filter('timber/twig', array($this, 'add_to_twig'));
    add_filter('timber/context', array($this, 'add_to_context'));
    
    // after plugins are loaded, do the checkout stuff. this is so other plugins have a chance to do their stuff first, including Woo! some actions require this
    add_action('plugins_loaded', array($this, 'on_plugins_loaded'));
    
    // override the template used for the checkout page. would be page.php from the theme but for this
    add_filter('page_template', array($this, 'direct_to_checkout_template'));
    
    // removes add_custom_demo_store_notice from Custom Store Notice plugin, but only on checkout.
    // we use use template_redirect to access is_checkout(). see https://codex.wordpress.org/Plugin_API/Action_Reference for more actions that can be used
    // this is useful for removing functions added to actions by other plugins
    // such functions which are added to theme via action hooks, need to be written outside the class
    // that way they can be removed as in remove_custom_store_notice_from_plugin_on_checkout
    add_action('template_redirect', array($this, 'remove_custom_store_notice_from_plugin_on_checkout'));
    
    // allow plugin's woo templates to override others
    add_filter('wc_get_template', array($this, 'wc_get_template'), 10, 5); 
  }
  
  // timber stuff. the usual stuff
  public function add_to_twig($twig) { 
    if(!class_exists('Twig_Extension_StringLoader')){
      $twig->addExtension(new Twig_Extension_StringLoader());
    }
    return $twig;
  }
  public function add_to_context($context) {
    $context['direct_to_checkout_url'] = DIRECT_TO_CHECKOUT_URL;
    // the last page
    $context['back_link'] = $_SERVER['HTTP_REFERER'];
    return $context;    
  }
  
  // after plugins are loaded, do the checkout stuff. this is so other plugins have a chance to do their stuff first, including Woo!
  public function on_plugins_loaded() {
    
    // remove basket button from minicart
    remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10);
    
    // remove payments from order review
    remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
    
    // disable order notes
    add_filter('woocommerce_enable_order_notes_field', '__return_false');
    
    // after logging in via woo login form, if cart has contents, go to checkout, else go to shop
    add_filter('woocommerce_login_redirect', array($this, 'checkout_redirect_login'));
    
    // redirect to checkout from single product's add to cart; without the messages
    add_filter('add_to_cart_redirect', array($this, 'redirect_to_checkout'));
    
    // allows removal of products in checkout
    add_filter('woocommerce_cart_item_name', array($this, 'woo_checkout_remove_item'), 10, 3);
    
    // ajax for removal
    add_action('wp_ajax_product_remove', array($this, 'custom_ajax_product_remove'));
    
    // ajax for removal
    add_action('wp_ajax_nopriv_product_remove', array($this, 'custom_ajax_product_remove'));
    
    // replace checkout js. see wc_checkout_coupons->remove_coupon->line:669
    add_action('wp_head', array($this, 'direct_to_checkout_js'));
    
    // remove checkout fields
    add_filter('woocommerce_checkout_fields', array($this, 'wc_remove_checkout_fields'));

    // pre-populate woo checkout field.
    add_filter('woocommerce_checkout_get_value', array($this, 'woocommerce_checkout_get_value'), 10, 2);
    
    // if cart is empty, redirect to shop page
    add_action('template_redirect', array($this, 'cart_empty_redirect_to_shop'));
  }
  
  // see on_plugins_loaded
  public function checkout_redirect_login() {
  
    global $woocommerce;
  
    if(!(WC()->cart->get_cart_contents_count() == 0)){
      return $woocommerce->cart->get_checkout_url();
    } else {
      return wc_get_page_permalink('shop');
    }
  
  }
  public function redirect_to_checkout() {
  
  	global $woocommerce;
  
  	// Remove the default `Added to cart` message
  	wc_clear_notices();
  
  	return $woocommerce->cart->get_checkout_url();
  
  }
  public function woo_checkout_remove_item($product_name, $cart_item, $cart_item_key) {
  	if ( is_checkout() ) {
  		$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);      
  		$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
  		$remove_link = apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
        '<a href="%s" class="remove remove_from_cart_button_off" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><i class="fas fa-times"></i></a>',
        esc_url( wc_get_cart_remove_url($cart_item_key)),
        esc_attr_x('Remove this item', 'Checkout', 'direct-to-checkout'),
        esc_attr($product_id),
        esc_attr($cart_item_key),
        esc_attr($_product->get_sku())
      ), $cart_item_key );
  		return '<span>' . $remove_link . '</span> </span>' . $_product->get_name() .'</span>';
  	}
  	return $product_name;
  }
  public function custom_ajax_product_remove() {
    // Get order review fragment
    ob_start();
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item)
    {
      if($cart_item['product_id'] == $_POST['product_id'] && $cart_item_key == $_POST['cart_item_key'] )
      {
        WC()->cart->remove_cart_item($cart_item_key);
      }
    }
  
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies();
  
    woocommerce_order_review();
    $woocommerce_order_review = ob_get_clean();
  }
  public function direct_to_checkout_js() {
    global $wp_scripts;
    $wp_scripts->registered[ 'wc-checkout' ]->src = DIRECT_TO_CHECKOUT_URL . 'public/js/checkout.js';
  }
  public function wc_remove_checkout_fields($fields) {
    
    // Billing fields
    unset( $fields['billing']['billing_company'] );
    // unset( $fields['billing']['billing_email'] );
    // unset( $fields['billing']['billing_phone'] );
    // unset( $fields['billing']['billing_state'] );
    // unset( $fields['billing']['billing_first_name'] );
    // unset( $fields['billing']['billing_last_name'] );
    unset( $fields['billing']['billing_address_1'] );
    unset( $fields['billing']['billing_address_2'] );
    // unset( $fields['billing']['billing_city'] );
    unset( $fields['billing']['billing_postcode'] );
  
    // Shipping fields
    unset( $fields['shipping']['shipping_company'] );
    unset( $fields['shipping']['shipping_phone'] );
    unset( $fields['shipping']['shipping_state'] );
    unset( $fields['shipping']['shipping_first_name'] );
    unset( $fields['shipping']['shipping_last_name'] );
    unset( $fields['shipping']['shipping_address_1'] );
    unset( $fields['shipping']['shipping_address_2'] );
    unset( $fields['shipping']['shipping_city'] );
    unset( $fields['shipping']['shipping_postcode'] );
  
    // Order fields
    unset( $fields['order']['order_comments'] );
  
    // return thr remaining
    return $fields;
  }
  public function woocommerce_checkout_get_value($input, $key) {
    global $current_user;
  
  	switch ($key) :
  		case 'billing_first_name':
  		case 'shipping_first_name':
  			return $current_user->first_name;
  		break;
  		
  		case 'billing_last_name':
  		case 'shipping_last_name':
  			return $current_user->last_name;
  		break;
  
  		case 'billing_email':
  			return $current_user->user_email;
  		break;
  
  		case 'billing_phone':
  			return $current_user->phone;
  		break;
  
  	endswitch;
  }
  public function cart_empty_redirect_to_shop() {
  	if (!is_checkout()) return;
  	if (0 == WC()->cart->get_cart_contents_count()) {
  		wp_safe_redirect( get_permalink(woocommerce_get_page_id('shop')) );
  		exit;
  	}
  }
  
  // override the template used for the checkout page. would be page.php from the theme but for this
  public function direct_to_checkout_template($page_template) {
    // if is the checkout page, use the new template
    if (is_checkout()) {
      $page_template = DIRECT_TO_CHECKOUT_PATH . 'templates/direct-to-checkout-template.php';
    }
    return $page_template;
  }
  
  // that way they can be removed as in remove_custom_store_notice_from_plugin_on_checkout
  public function remove_custom_store_notice_from_plugin_on_checkout() {
    if(is_checkout()){
      remove_action('rmcc_before_header', 'add_custom_demo_store_notice', 10);
      remove_action('rmcc_before_footer', 'subscribe_html', 10);
    }
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
}