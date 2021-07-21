<?php
/*
Plugin Name: Direct to Checkout for Woocommerce by RMcC
Plugin URI: #
Description: Allows for an optimized Woocommerce chekout experience, without the need for a basket. Dont't forget to disable the basket page in Woocommerce Settngs > Advanced. This plugin is translation-ready.
Version: 1.0.0
Author: robmccormack89
Author URI: #
Version: 1.0.0
License: GNU General Public License v2 or later
License URI: LICENSE
Text Domain: direct-to-checkout
Domain Path: /languages/
*/

// don't run if someone access this file directly
defined('ABSPATH') || exit;

// define some constants
if (!defined('DIRECT_TO_CHECKOUT_PATH')) define('DIRECT_TO_CHECKOUT_PATH', plugin_dir_path( __FILE__ ));
if (!defined('DIRECT_TO_CHECKOUT_URL')) define('DIRECT_TO_CHECKOUT_URL', plugin_dir_url( __FILE__ ));
if (!defined('DIRECT_TO_CHECKOUT_BASE')) define('DIRECT_TO_CHECKOUT_BASE', dirname(plugin_basename( __FILE__ )));

// if Woo class exists, do some stuff
if (class_exists( 'WooCommerce')) {
  // require the composer autoloader
  if (file_exists($composer_autoload = __DIR__.'/vendor/autoload.php')) require_once $composer_autoload;
  
  // then require the main plugin class. this class extends Timber/Timber which is required via composer
  new Rmcc\DirectToCheckout;
  
  // initialize the assets class. for styles & scripts management
  new Rmcc\CheckoutAssets;
  
  // initialize the checkout fields class. for managing the checkout fields stuff
  new Rmcc\CheckoutFields;
  
  // initialize the order item removal class. for removing items from the order form via ajax
  new Rmcc\ProductRemove;
  
  // initialize the order item quantity select class. for changing order items quantity on checkout via ajax
  new Rmcc\ProductQuantity;
}