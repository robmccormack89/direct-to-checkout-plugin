<?php
namespace Rmcc;

class CheckoutRedirects {

  public function __construct() {
    add_filter('woocommerce_login_redirect', array($this, 'login_redirect_to_checkout_with_cart'));
    add_filter('add_to_cart_redirect', array($this, 'redirect_to_checkout_from_single'));
    add_action('template_redirect', array($this, 'cart_empty_redirect_to_shop'));
  }
  
  // after logging in via woo login form, if cart has contents, go to checkout, else go to shop
  public function login_redirect_to_checkout_with_cart() {
  
    global $woocommerce;
  
    if(!(WC()->cart->get_cart_contents_count() == 0)){
      return $woocommerce->cart->get_checkout_url();
    } else {
      return wc_get_page_permalink('shop');
    }
  
  }
  
  // redirect to checkout from single product's add to cart; without the messages
  public function redirect_to_checkout_from_single() {
  
    global $woocommerce;

    // Remove the default `Added to cart` message
    wc_clear_notices();

    return $woocommerce->cart->get_checkout_url();
  
  }
  
  // if cart is empty, redirect to shop page
  public function cart_empty_redirect_to_shop() {
    if (!is_checkout()) return;
    if (0 == WC()->cart->get_cart_contents_count()) {
      wp_safe_redirect( get_permalink(woocommerce_get_page_id('shop')) );
      exit;
    }
  }

}