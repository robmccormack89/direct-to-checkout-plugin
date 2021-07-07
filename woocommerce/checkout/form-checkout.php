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
**/

if (!defined('ABSPATH')) exit;

/**
 * this is a custom checkout form template rendered with timber/twig. it overwrites woocommerce/templates/checkout/form-checkout.php
 *
 * woocommerce_checkout_payment has been unhooked from woocommerce_checkout_order_review via DirectToCheckout
 * the new markup for woocommerce_checkout_payment is in payment.twig, & is based off payment.php
 *
 * woocommerce_checkout_billing is disabled in _details-billing.twig
 * used custom markup instead which is based off form-billing.php
 *
*/

$context = Timber::context();

// the checkout variable, stores the checkout object
$context['checkout'] = $checkout;

// the cart object
$context['cart'] = WC()->cart;

// get enabled gateways
$gateways = WC()->payment_gateways->get_available_payment_gateways();
$enabled_gateways = [];
if($gateways) {
  foreach( $gateways as $gateway ) {
    if( $gateway->enabled == 'yes' ) {
      $enabled_gateways[] = $gateway;
    }
  }
}
$context['available_gateways'] = $enabled_gateways;

Timber::render('form-checkout.twig', $context);