<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * custom checkout form template rendered with timber/twig. 
 * this template overwrites woocommerce/templates/checkout/form-checkout.php
 * this template also includes a custom rewrite/insert for woocommerce/templates/checkout/payment.php
 *
**/

$context = Timber::context();

// billing
$context['checkout_url'] = esc_url(wc_get_checkout_url());
$context['checkout'] = $checkout;
$context['checkout_fields'] = $checkout->get_checkout_fields();

// payments
function cart_needs_payment(){
	if(WC()->cart->needs_payment()){
		return true;
	}
}
$context['cart_needs_payment'] = cart_needs_payment();

// gateways
$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
$gateways = WC()->payment_gateways->get_available_payment_gateways();
$enabled_gateways = [];
if( $gateways ) {
	foreach( $gateways as $gateway ) {
	  if( $gateway->enabled == 'yes' ) {
      $enabled_gateways[] = $gateway;
	  }
	}
}
$context['available_gateways'] = $enabled_gateways;

Timber::render('form-checkout.twig', $context);