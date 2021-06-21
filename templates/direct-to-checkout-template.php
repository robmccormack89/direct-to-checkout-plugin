<?php
/**
* This template is an override of a standard page template. See DirectToCheckout->direct_to_checkout_template() 
* Timber::$locations is being modified on a per-template basis this way.
* we check to see if locations already exists from another plugin (one that maybe extends the theme class, like theme functionality plugin)
* we then add new locations to that
*
* @package DirectToCheckout
**/

// if not on checkout page, return
if (!is_checkout()) return;
 
// if timber::locations is empty (another plugin hasn't already added to it), make it an array
if(!Timber::$locations) Timber::$locations = array();

// add a new views path to the locations array
array_push(
  Timber::$locations, 
  DIRECT_TO_CHECKOUT_PATH . 'views'
);

// set the usual timber context
$context = Timber::context();
// set the usual post context
$context['post'] = Timber::query_post();
// render the template via twig
Timber::render('direct-to-checkout.twig', $context);