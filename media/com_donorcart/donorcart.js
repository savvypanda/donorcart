var dcart_target = '#dcart_target';
//var dcart_alert_text = "You just added a gift to your cart.\n\nWant to make this a monthly recurring gift? Just select \"Make this an automatic monthly  gift\" during checkout.  Or set up an automatic monthly gift from your checking account (EFT).\n\n\nDo you want to checkout now?";
var dcart_alert_text = "<p>You just added a gift to your cart.<br /><br />Want to make this a monthly recurring gift? Just select &quot;Make this an automatic monthly  gift&quot; during checkout.";

function dcartLoader(target, postdata, checkout_alert) {
	jQuery(dcart_target).html('<img src="media/com_donorcart/images/ajax_loading.gif" alt="Loading.." height="16" width="16" align="left" border="0" /> &nbsp; &nbsp; Loading...');
	if(typeof postdata !== 'undefined' && postdata != false) {
		settings = {url: target, type: 'POST', data: postdata};
		jQuery.ajax(settings).done(function(data){
			jQuery(dcart_target).html(data);
			if(typeof checkout_alert !== 'undefined' && checkout_alert != false) {
				if(checkout_alert === 'skip') {
					jQuery('form[name=dcart_cart]').submit();
				} else {
					jQuery('<div>'+dcart_alert_text+'</div>').appendTo('body')
						.dialog({
							modal: true, title: 'Proceed to Checkout?', zIndex: 10000, autoOpen: true,
							width: 'auto', resizable: false,
							buttons: {
								'Check Out': function () {
									jQuery('form[name=dcart_cart]').submit();
									jQuery(this).dialog("close");
								},
								'Add Another Donation': function () {
									jQuery(this).dialog("close");
								}
							},
							close: function (event, ui) {
								jQuery(this).remove();
							}
						});
				}
			}
		});
	} else {
		jQuery.get(target, function(data){jQuery(dcart_target).html(data)});
	}
}

(function($) {
	$(document).ready(function() {
		$('#donorcart_login_div').hide().prop('checked',false);
		$('#donorcart_no_account_div').hide().prop('checked',false);
		$('#donorcart_create_acct_div').hide().prop('checked',false);

		windowhash = window.location.hash;
		if(windowhash == '#createacct') {
			$('#donorcart_create_acct_div').show().prop('checked',true);
		} else if(windowhash == '#login') {
			$('#donorcart_login_div').show().prop('checked',true);
		} else if(windowhash == '#noacct') {
			$('#donorcart_no_account_div').show().prop('checked',true);
		}

		$('#donorcart_create_acct_option').click(function() {
			$('#donorcart_login_div').slideUp("medium");
			$('#donorcart_no_account_div').slideUp("medium");
			$('#donorcart_create_acct_div').slideDown("medium");
		});
		
		$('#donorcart_no_login_option').click(function() {
			$('#donorcart_login_div').slideUp("medium");
			$('#donorcart_create_acct_div').slideUp("medium");
			$('#donorcart_no_account_div').slideDown("medium");
		});
		$('#donorcart_login_option').click(function() {
			$('#donorcart_no_account_div').slideUp("medium");
			$('#donorcart_create_acct_div').slideUp("medium");
			$('#donorcart_login_div').slideDown("medium");
		});

		function togglesameshipbill() {
			if($(this).is(':checked')) {
				$('.billingaddress').hide();
			} else {
				$('.billingaddress').show();
			}
		}
		$('input[name=use_same_address_for_billto]').change(togglesameshipbill).each(togglesameshipbill);

		function togglenewaddress() {
			$(this).parent().find('.optiondrawer').slideDown();
			$('[name='+this.name+']').not(':checked').parent().find('.optiondrawer').slideUp();
			/* if($(this).is(':checked')) {
				$(this).parent().find('.optiondrawer').slideDown();
			} else {
				$(this).parent().find('.optiondrawer').slideUp();
			} */
		}
		$('input[type=radio][name=shipto_id],input[type=radio][name=billto_id]').change(togglenewaddress).not(':checked').parent().find('.optiondrawer').slideUp();

		$('#is_recurring').click(function() {
				$('#recurring').toggle("medium");						
		});
	
		$(document).delegate('a.dcart-link', 'click', function () {
			dcartLoader($(this).attr('href'));
			return false;
		});

		$('form.dcartadd').submit(function() {
			dcartLoader($(this).attr('href'), $(this).serialize(), $(this).hasClass('dnoprompt')?'skip':true);
			return false;
		})
	});
})(jQuery);
