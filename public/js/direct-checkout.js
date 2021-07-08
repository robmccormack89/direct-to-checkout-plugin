// checkout customizations
jQuery(function($) {

  // this function checks which of a given set of inputs are emtpy & for the empty ones, adds a 'required' notification beside the input's label & focuses it
  // empty events also cause a stopPropagation
  function stopWhenRequiredsExist(fields) {
    $.each(fields, function(i, field) {

      // set the required notification text that will appear
      var req = "<span id=\"Req_" + field.name + "\" class=\"req uk-text-danger uk-text-small\">This is a required field</span>";

      // if its an emtpy field
      if (!field.value) {

        // stop the event! (proceeding to payment)
        event.stopPropagation();

        // if the required notification text doesnt already exist
        if ($("#Req_" + field.name).length == 0) {
          // add the text after the label
          $('label[for="' + field.name + '"]').after(req);
        }

        // & add focus to the input to emphasize
        $('label[for="' + field.name + '"]').focus();
      }

      // if the field has a value but may already have a required notifcation label, remove it 
      if (field.value) {
        $("#Req_" + field.name).remove();
      }

    });
  }

  // the checkout customizations; mainly adding uikit classes to unstyled woocommerce elements & making things look nice
  function checkoutStyling() {

    // form inputs
    $(".billing .input-text, .shipping .input-text, .woocommerce-form-login .input-text, input#coupon_code").addClass("uk-input uk-form-small"); // text inputs
    $("label").addClass("uk-form-label"); // form labels (all)
    $("textarea").addClass("uk-textarea"); // textarea inputs (all)
    $("select").addClass("uk-select"); // select elements (all)
    $(".input-checkbox, .woocommerce-form__input-checkbox").addClass("uk-checkbox"); // checkboxes
    $(".input-radio").addClass("uk-radio"); // radio buttons

    // buttons
    $(".checkout_coupon button, .woocommerce-form-login__submit").addClass("uk-button uk-button-default uk-button-small");

    // billing fields grid
    $(".woocommerce-billing-fields__field-wrapper").attr("uk-grid", true).addClass("uk-child-width-1-1 uk-grid-small");
    $(".woocommerce-billing-fields .form-row-first, .woocommerce-billing-fields .form-row-last").addClass("uk-width-1-2");

    // order review
    $(".woocommerce-checkout-review-order-table").addClass("uk-table uk-table-divider uk-table-small uk-table-justify uk-table-middle");
    $(".woocommerce-checkout-review-order-table .product-name").addClass("uk-flex uk-flex-middle");
    $(".woocommerce-checkout-review-order-table .quantity").addClass("uk-inline uk-flex-last uk-margin-small-left");
    $(".woocommerce-checkout-review-order-table #shipping_method").addClass("uk-list uk-list-collapse");

    // additional info tab
    $('#order_comments').attr('rows', 5);

    // payments tab
    $(".wc_payment_methods").addClass("uk-list uk-list-divider");

  }

  // a new sexy quantity selecter
  function quantitySelectButtons() {
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
        var newVal = oldValue + 1;
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
  }

  // validate inputs before proceeding from billing tab
  $('.proceed-from-billing').click(function(event) {

    // set the validate wrapper class & fields to check against. billing fields in this case
    var billing_fields = $(".billing .validate-required").find("select, textarea, input").serializeArray();

    // validate the fields!!! see above function
    stopWhenRequiredsExist(billing_fields);

  });

  // validate inputs before proceeding from shipping tab
  $('.proceed-from-shipping').click(function(event) {

    // set the validate wrapper class & fields to check against. billing fields in this case
    var shipping_fields = $(".shipping .validate-required").find("select, textarea, input").serializeArray();

    // validate the fields!!! see above function
    stopWhenRequiredsExist(shipping_fields);

  });

  // validate inputs before proceeding from additional tab
  $('.proceed-from-additional').click(function(event) {

    // set the validate wrapper class & fields to check against. billing fields in this case
    var additional_fields = $(".additional .validate-required").find("select, textarea, input").serializeArray();

    // validate the fields!!! see above function
    stopWhenRequiredsExist(additional_fields);

  });

  // remove item from review_order with ajax
  $(document).on('click', 'tr.cart_item a.remove', function(e) {

    the_items = $('.shop_table tbody tr').length;

    // if more than one items in cart, do the ajax remove. else do a normal remove (dedirects to competitions)
    if (the_items > 1) {
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

  // order item quantity select: update via ajax
  $('form.checkout').on('click', '.quantity-button', function(e) {

    var data = {
      action: 'update_order_review',
      security: wc_checkout_params.update_order_review_nonce,
      post_data: $('form.checkout').serialize()
    };

    jQuery.post(add_quantity.ajax_url, data, function(response) {
      $('body').trigger('update_checkout');
    });

  });

  $('form.checkout').load(checkoutStyling()); // apply the checkout style on initial load of checkout form
  $('body').on('updated_checkout', function(event) { // when the updated_checkout event is triggered (when something has changed)
    checkoutStyling(); // apply the checkout styling again
    quantitySelectButtons(); // & apply the quantity select update. this one needs to be done after the 'updated_checkout' event to work
  });

});