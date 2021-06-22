// remove item from review_order with ajax
jQuery( function($){
 if (typeof woocommerce_params === 'undefined') return false;
 $(document).on('click', 'tr.cart_item a.remove', function(e) {
   
   the_items = $('.shop_table tbody tr').length;
   
   // if more than one items in cart, do the ajax remove. else do a normal remove (dedirects to competitions)
   if(the_items > 1){
     e.preventDefault();

     var product_id = $(this).attr("data-product_id"),
       cart_item_key = $(this).attr("data-cart_item_key"),
       product_container = $(this).parents('.shop_table');

     // Add loader
     product_container.block({
       message: null,
       overlayCSS: {
         background: '#fff',
         opacity: 0.6
       }
     });

     $.ajax({
       type: 'POST',
       dataType: 'json',
       url: wc_checkout_params.ajax_url,
       data: {
         action: "product_remove",
         product_id: product_id,
         cart_item_key: cart_item_key
       },
       success: function(result) {
         $('body').trigger('update_checkout');
         // console.log(result);
       }
     });
   }
   
 });
});

// validate billing inputs before proceed to payment
jQuery(function($) {
  // on click the nav / continue to payment button
  $( ".proceed-to-payment" ).click(function(event) {

    // the the validate wrapper class & fields to check against
    var fields = $(".validate-required")
      .find("select, textarea, input")
      .serializeArray();

    // for each missing field
    $.each(fields, function (i, field) {
      
      // set the required notification text that will appear
      var req = "<span id=\"Req_"+ field.name +"\" class=\"req uk-text-danger uk-text-small\">This is a required field</span>";
      
      // if its an emtpy field
      if (!field.value) {
        
        // stop the event! (proceeding to payment)
        event.stopPropagation();
        
        // if the required notification text doesnt already exist
        if( $("#Req_" + field.name).length == 0) {
          // add the text after the label
          $('label[for="'+field.name+'"]').after(req);
        }
        
        // & add focus to the input to emphasize
        $('label[for="'+field.name+'"]').focus();
      } 
      
      // if the field has a value but may already have a required notifcation label, remove it 
      if (field.value) {
        $("#Req_" + field.name).remove();
      } 
      
    });
    
  });
});

// checkout customizations
jQuery(function($) {
  function theCheckout() {
    
    // billing fields wrap; make a grid
    $(".woocommerce-billing-fields__field-wrapper").attr("uk-grid", true).addClass("uk-child-width-1-1 uk-grid-small");
    $(".woocommerce-billing-fields h3").addClass("uk-h4");
    // first name & last name; side by side
    $(".woocommerce-billing-fields .form-row-first").addClass("uk-width-1-2");
    $(".woocommerce-billing-fields .form-row-last").addClass("uk-width-1-2");

    // order review table styles
    $(".woocommerce-checkout-review-order-table").addClass("uk-table uk-table-divider uk-table-small uk-table-justify");

    // payment methods
    $(".wc_payment_methods").addClass("uk-list uk-list-large uk-list-divider");

    // form inputs
    $(".input-radio").addClass("uk-radio");
    $(".input-text").addClass("uk-input uk-form-small");
    $(".input-checkbox").addClass("uk-checkbox");
    $("label").addClass("uk-form-label");
    $("select").addClass("uk-select");
    
    // buttons
    $(".checkout_coupon button").addClass("uk-button uk-button-default uk-button-small");
    
    // shipping
    $("#shipping_method").addClass("uk-list");
    
  }
  $("form.checkout").load(theCheckout());
  $("body").on('DOMSubtreeModified', "form.checkout", theCheckout);
});