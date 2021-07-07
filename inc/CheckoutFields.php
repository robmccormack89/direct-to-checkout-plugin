<?php
namespace Rmcc;

class CheckoutFields {

  public function __construct() {
    // after plugins are loaded, do this...
    add_action('plugins_loaded', array($this, 'checkout_fields_plugins_loaded'));
  }
  
  public function checkout_fields_plugins_loaded() {
    add_filter('woocommerce_checkout_fields', array($this, 'unset_checkout_fields'));
    add_filter('woocommerce_checkout_get_value', array($this, 'checkout_fields_pre_values'), 10, 2);
  }
  
  public function unset_checkout_fields($fields) {
    
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
  public function checkout_fields_pre_values($input, $key) {
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
  
}