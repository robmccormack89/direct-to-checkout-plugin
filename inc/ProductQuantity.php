<?php
namespace Rmcc;
use Timber\Timber;

/*

* Quantity stuff

*/

class ProductQuantity extends Timber {

  public function __construct() {
    // after plugins are loaded, do this...
    add_action('plugins_loaded', array($this, 'product_quantity_plugins_loaded'));
  }
  
  public function product_quantity_plugins_loaded() {
    add_filter('woocommerce_checkout_cart_item_quantity', array($this, 'remove_default_quantity_count'), 10, 2);
    add_filter('woocommerce_cart_item_name', array($this, 'add_order_item_quantity_select'), 20, 3);
    add_action('init', array($this, 'update_order_review_after_quantity_ajax'));
  }

  public function remove_default_quantity_count($cart_item, $cart_item_key) {
    if (!is_checkout()) return;
    $product_quantity= null;
    return $product_quantity;
  }
  
  public function add_order_item_quantity_select($product_name, $cart_item, $cart_item_key) {
    if (!is_checkout()) return;
    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
    if ( $_product->is_sold_individually() ) {
        $product_name .= sprintf('<input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
      } else {
        $product_name .= woocommerce_quantity_input( array(
          'input_name'  => "cart[{$cart_item_key}][qty]",
          'input_value' => $cart_item['quantity'],
          'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
          'min_value'   => '1'
        ), $_product, true );
      }
  	return $product_name;
  }
  
  public function update_order_review_after_quantity_ajax() {
    if (!is_user_logged_in()){
      add_action( 'wp_ajax_nopriv_update_order_review', array($this, 'update_order_review_after_quantity') );
    } else{
      add_action( 'wp_ajax_update_order_review', array($this, 'update_order_review_after_quantity') );
    }
  }
  
  public function update_order_review_after_quantity() {
  
    $values = array();
    parse_str($_POST['post_data'], $values);
    $cart = $values['cart'];
    
    foreach ( $cart as $cart_key => $cart_value ){
      WC()->cart->set_quantity( $cart_key, $cart_value['qty'], false );
      WC()->cart->calculate_totals();
      woocommerce_cart_totals();
    }
    
    wp_die();
  }
  
}