// remove item from review_order with ajax
jQuery(function($){

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
       }
     });
   }
   
 });
 
});

// validate billing inputs before proceeding to payment tab
// should also valiadte these inputs before proceeding from payment form, for cases where accidentally arrive to payment form before billing. can happen
jQuery(function($){
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
jQuery(function($){
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
    
    // quantity 
    $(".product-name").addClass("uk-flex uk-flex-middle");
    $(".quantity").addClass("uk-inline uk-flex-last uk-margin-small-left");
  }
  $("form.checkout").load(theCheckout());
  $("body").on('DOMSubtreeModified', "form.checkout", theCheckout);
});

// quantity ajax update
jQuery(function($) {
  $("form.checkout").on("click", "input.qty", function(e) {

    var data = {
      action: 'update_order_review',
      security: wc_checkout_params.update_order_review_nonce,
      post_data: $( 'form.checkout' ).serialize()
    };

    jQuery.post( add_quantity.ajax_url, data, function( response ) {
      $('body').trigger('update_checkout');
    });

  });
});

// a nicer quantity select. this needs to be fired somehow on page load, then again each time the order review is update, & on each quantity select, but without bloody repeating
jQuery(function($){
  $('<div class="quantity-nav"><div class="quantity-button quantity-up">+</div><div class="quantity-button quantity-down">-</div></div>').insertAfter('.quantity input');
  $('.quantity').each(function() {
    var spinner = jQuery(this),
      input = spinner.find('input[type="number"]'),
      btnUp = spinner.find('.quantity-up'),
      btnDown = spinner.find('.quantity-down'),
      min = input.attr('min'),
      max = input.attr('max');

    btnUp.click(function() {
      var oldValue = parseFloat(input.val());
      if (oldValue >= max) {
        var newVal = oldValue;
      } else {
        var newVal = oldValue + 1;
      }
      spinner.find("input").val(newVal);
      spinner.find("input").trigger("change");
    });

    btnDown.click(function() {
      var oldValue = parseFloat(input.val());
      if (oldValue <= min) {
        var newVal = oldValue;
      } else {
        var newVal = oldValue - 1;
      }
      spinner.find("input").val(newVal);
      spinner.find("input").trigger("change");
    });

  });
});