<?php
/*
Plugin Name: Barebones Timber
Plugin URI: #
Description: A barebones plugin using timber/timber
Version: 1.0.0
Author: Robmccormack89
Author URI: #
Version: 1.0.0
License: GNU General Public License v2 or later
License URI: LICENSE
Text Domain: barebones-timber-plugin
Domain Path: /languages/
*/

// don't run if someone access this file directly
defined('ABSPATH') || exit;

// define some constants
if (!defined('BAREBONES_TIMBER_PATH')) define('BAREBONES_TIMBER_PATH', plugin_dir_path( __FILE__ ));
if (!defined('BAREBONES_TIMBER_VIEWS')) define('BAREBONES_TIMBER_VIEWS', plugin_dir_path( __FILE__ ).'views');

// require the composer autoloader
if (file_exists($composer_autoload = __DIR__.'/vendor/autoload.php')) require_once $composer_autoload;

// then require the main plugin class. this class extends Timber/Timber which is required via composer
new Rmcc\BarebonesTimber;