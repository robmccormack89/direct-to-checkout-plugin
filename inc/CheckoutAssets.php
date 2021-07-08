<?php
namespace Rmcc;

class CheckoutAssets {

  public function __construct() {
    // after plugins are loaded, do this...
    add_action('plugins_loaded', array($this, 'checkout_assets_plugins_loaded'));
  }
  
  public function checkout_assets_plugins_loaded() {
    // enqueue the plugin scripts & styles.
    add_action('wp_enqueue_scripts', array($this, 'direct_checkout_assets'), 99);
    
    // replace the woocommerce checkout script with my own
    add_action('wp_head', array($this, 'replace_woo_checkout_script'));
    
    // remove the active theme's styles & scripts
    add_action('wp_print_scripts', array($this, 'remove_theme_all_scripts'), 100);
    add_action('wp_print_styles', array($this, 'remove_theme_all_styles'), 100);
    
    // removes some of woocommerce's styles & scripts
    add_action('wp_enqueue_scripts', array($this, 'remove_woo_script_styles'), 99);
  }
  
  /*

  * The main checkout script

  */
  
  public function direct_checkout_assets(){
    if (!is_checkout()) return;
      
    // plugin base scripts
    wp_enqueue_script(
      'direct-checkout',
      DIRECT_TO_CHECKOUT_URL . 'public/js/base.js',
      '',
      '1.0.0',
      false
    );
    
    // plugin base css
    wp_enqueue_style(
      'direct-checkout',
      DIRECT_TO_CHECKOUT_URL . 'public/css/base.css'
    );
    
    // plugin base css
    wp_enqueue_style(
      'direct-checkout-custom',
      DIRECT_TO_CHECKOUT_URL . 'public/css/direct-checkout.css'
    );
    
    // plugin additional & localized script/s
    wp_enqueue_script('direct-checkout-custom', DIRECT_TO_CHECKOUT_URL . 'public/js/direct-checkout.js', 'jquery', '1.0.0', true);
    // localize the script with the 'add_quantity' variable
    wp_localize_script('direct-checkout-custom', 'add_quantity', 
      array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
      )
    );
  }
  
  public function remove_woo_script_styles() {
    if (!is_checkout()) return;
    remove_action('wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );
    wp_dequeue_style('woocommerce_frontend_styles');
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
    wp_dequeue_style('woocommerce_fancybox_styles');
    wp_dequeue_style('woocommerce_chosen_styles');
    wp_dequeue_style('woocommerce_prettyPhoto_css');
    wp_dequeue_script('selectWoo');
    wp_deregister_script('selectWoo');
    wp_dequeue_script('select2');
    wp_deregister_script('select2');
    wp_dequeue_style('select2'); // doesnt seem needed
    wp_dequeue_style('wc-block-style');
    wp_dequeue_style('wc-block-vendors-style');
  }
  
  public function remove_theme_all_scripts() {
    if (!is_checkout()) return;
    
    global $wp_scripts;
    
    $stylesheet_uri = get_stylesheet_directory_uri();
    $new_scripts_list = array(); 
    
    foreach( $wp_scripts->queue as $handle ) {
      $obj = $wp_scripts->registered[$handle];
      $obj_handle = $obj->handle;
      $obj_uri = $obj->src;
  
      if ( strpos( $obj_uri, $stylesheet_uri ) === 0 )  {
        //Do Nothing
      } else {
        $new_scripts_list[] = $obj_handle;
      }
    }
    
    $wp_scripts->queue = $new_scripts_list;
  }
  
  public function remove_theme_all_styles() {
    if (!is_checkout()) return;
    
    global $wp_styles;
    
    $stylesheet_uri = get_stylesheet_directory_uri();
    $new_styles_list = array(); 
    
    foreach( $wp_styles->queue as $handle ) {
      $obj = $wp_styles->registered[$handle];
      $obj_handle = $obj->handle;
      $obj_uri = $obj->src;
      if ( strpos( $obj_uri, $stylesheet_uri ) === 0 )  {
        //Do Nothing
      } else {
        $new_styles_list[] = $obj_handle;
      }
    }
    
    $wp_styles->queue = $new_styles_list;
  }
  
  /*

  * Replace woocommerce checkout script with overwritten one
  * Just to make checkout coupon work with the ajax
  * I'm sure there is a simpler way tbh, must check this out later
  * see line 669 @wc_checkout_coupons->remove_coupon

  */
  
  public function replace_woo_checkout_script() {
    if (!is_checkout()) return;
    
    global $wp_scripts;
    
    $wp_scripts->registered['wc-checkout']->src = DIRECT_TO_CHECKOUT_URL . 'public/js/checkout.js';
  }

}