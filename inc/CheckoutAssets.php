<?php
namespace Rmcc;

class CheckoutAssets {

  public function __construct() {
    // after plugins are loaded, do this...
    add_action('plugins_loaded', array($this, 'checkout_assets_plugins_loaded'));
    // enqueue the plugin scripts, this is ajaxified
    add_action('wp_enqueue_scripts', array($this, 'direct_checkout_script'), 99);
  }
  
  public function checkout_assets_plugins_loaded() {
    add_action('wp_head', array($this, 'replace_woo_checkout_script'));
  }
  
  /*

  * The main checkout script

  */
  
  public function direct_checkout_script(){
    if ( is_checkout() ) {
      wp_enqueue_script('direct-checkout', DIRECT_TO_CHECKOUT_URL . 'public/js/direct-checkout.js', 'jquery', '', true);
      // localize the script to work with wp ajax
      $localize_script = array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
      );
      // localize the script with the 'add_quantity' variable
      wp_localize_script('direct-checkout', 'add_quantity', $localize_script);
      // wp_localize_script('direct-checkout', 'wc_checkout_params', $localize_script);
    }
  }
  
  /*

  * Replace woocommerce checkout script with overwritten one
  * Just to make checkout coupon work with the ajax
  * I'm sure there is a simpler way tbh, must check this out later
  * see line 669 @wc_checkout_coupons->remove_coupon

  */
  
  public function replace_woo_checkout_script() {
    global $wp_scripts;
    $wp_scripts->registered['wc-checkout']->src = DIRECT_TO_CHECKOUT_URL . 'public/js/checkout.js';
  }

}