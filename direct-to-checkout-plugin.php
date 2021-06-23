<?php
/*
Plugin Name: Direct to Checkout for Woocommerce by RMcC
Plugin URI: #
Description: Allows for an optimized woocommerce chekout experience. Dont't forget to disable the basket page in woocommerce settngs -> advanced
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

// require the composer autoloader
if (file_exists($composer_autoload = __DIR__.'/vendor/autoload.php')) require_once $composer_autoload;

// then require the main plugin class. this class extends Timber/Timber which is required via composer
new Rmcc\DirectToCheckout;