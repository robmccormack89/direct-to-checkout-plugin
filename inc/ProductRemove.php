<?php
namespace Rmcc;

class ProductRemove {

  public function __construct() {
    // after plugins are loaded, do this...
    add_action('plugins_loaded', array($this, 'product_remove_plugins_loaded'));
  }
  
  public function product_remove_plugins_loaded() {
    add_filter('woocommerce_cart_item_name', array($this, 'woo_checkout_order_item_remove'), 10, 3);
    add_action('wp_ajax_product_remove', array($this, 'custom_ajax_product_remove'));
    add_action('wp_ajax_nopriv_product_remove', array($this, 'custom_ajax_product_remove'));
  }
  
  // allows removal of products in checkout
  public function woo_checkout_order_item_remove($product_name, $cart_item, $cart_item_key) {
    if (!is_checkout()) return;
    
    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);      
    $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
    $remove_link = apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
      '<a href="%s" class="remove remove_from_cart_button_off" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><i class="fas fa-times"></i></a>',
      esc_url( wc_get_cart_remove_url($cart_item_key)),
      esc_attr_x('Remove this item', 'Checkout', 'direct-to-checkout'),
      esc_attr($product_id),
      esc_attr($cart_item_key),
      esc_attr($_product->get_sku())
    ), $cart_item_key);
    $product_name = '<span>' . $remove_link . '</span><span class="uk-margin-small-left item-name">' . $_product->get_name() .'</span>';

    return $product_name;
  }
  
  // ajax for removal
  public function custom_ajax_product_remove() {
    // Get order review fragment
    ob_start();
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
      if($cart_item['product_id'] == $_POST['product_id'] && $cart_item_key == $_POST['cart_item_key'] ) {
        WC()->cart->remove_cart_item($cart_item_key);
      }
    }
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies();
    woocommerce_order_review();
    $woocommerce_order_review = ob_get_clean();
  }
  
}