(function($) {
	//First let's define the donorcart loader for submitting donation forms
	/*
	 * Function dcartLoader
	 *
	 * Loads the requested URL into the DonorCart cart position on the page.
	 *
	 * @param target = The url of the request to load
	 * @param postdata = The serialized form data to submit, or false for a GET request. Defaults to false
	 * @param checkout_alert = 'skip' to automatically proceed to checkout. False to continue shopping. True to ask the user what they want to do.
	 */
	function dcartLoader(target, postdata, checkout_alert) {
		var dcart_target = $('#dcart_target');
		if(typeof postdata==='undefined')postdata=false;
		if(typeof checkout_alert==='undefined')checkout_alert=false;
		if(!dcart_target.length)checkout_alert='skip';

		//Replace the cart with a loading message
		dcart_target.html('<img src="'+sp_website_root+'media/com_donorcart/images/ajax_loading.gif" alt="'+Joomla.JText._('COM_DONORCART_JS_ADD_TO_CART_LOADING','Loading...')+'" height="16" width="16" align="left" border="0" /> &nbsp; &nbsp; '+Joomla.JText._('COM_DONORCART_JS_ADD_TO_CART_LOADING','Loading...'));

		if(postdata != false) {
			//if we have anything to post, use a post request
			var settings = {url: target, type: 'POST', data: postdata};
			$.ajax(settings).done(function(data){
				//if the request was successful, replace the cart with the updated cart
				dcart_target.html(data);
				if(checkout_alert != false) {
					if(checkout_alert === 'skip') {
						//we are supposed to skip straight to checkout
						window.location=sp_checkout_page;
					} else {
						//ask the user if they would like to proceed to checkout
						var dialogbuttons = {};
						dialogbuttons[Joomla.JText._('COM_DONORCART_JS_PROCEED_TO_CHECKOUT','Check Out')] = function() {
							window.location=sp_checkout_page;
							$(this).dialog("close");
						};
						dialogbuttons[Joomla.JText._('COM_DONORCART_JS_CONTINUE_SHOPPING','Add Another Donation')] = function() {
							$(this).dialog("close");
						}
						$('<div>'+Joomla.JText._('COM_DONORCART_JS_ADD_TO_CART_SUCCESS','Item added to cart. Do you want to checkout now?')+'</div>').appendTo('body')
							.dialog({
								modal: true, title: Joomla.JText._('COM_DONORCART_JS_ADD_TO_CART_SUCCESS_TITLE','Proceed to checkout?'), zIndex: 10000, autoOpen: true,
								width: 'auto', resizable: false,
								buttons: dialogbuttons,
								close: function (event, ui) {
									$(this).remove();
								}
							});
					}
				}
				//if checkout_alert is false, don't do anything. They will continue using the page as normal.
			}).fail(function(data) {
				//if the request failed, replace the cart with an error message and show it to the user in a popup
				if(!data) data='Request Failed';
				dcart_target.html(data);
				$('<div>'+Joomla.JText._('COM_DONORCART_JS_ADD_TO_CART_FAILURE','Failed to add item to cart. Please review your selection and try again.')+'</div>').appendTo('body')
					.dialog({modal:true, title: Joomla.JText._('COM_DONORCART_JS_ADD_TO_CART_FAILURE_TITLE','Failed to add item to cart'), zIndex: 10000, autoOpen: true, width: 'auto', resizable:false});
			});
		} else {
			//if this is a get request, simply display the output in the cart
			$.get(target, function(data){dcart_target.html(data)});
		}
	}

	$(document).ready(function() {
		var account_options = $('#donorcart_checkout_container input[type=radio][name=account_option]');
		if(account_options.length) {
			function update_account_selection() {
				account_options.each(function(){
					$('#'+$(this).attr('data-target'))[$(this).is(':checked')?'slideDown':'slideUp']("medium");
				})
			}

			var windowhash = window.location.hash;
			account_options.each(function(){
				if(windowhash=='#'+$(this).val()) $(this).prop('checked',true);
			});
			update_account_selection();
			account_options.click(update_account_selection);
		}

		function togglesameshipbill() {
			if($(this).is(':checked')) {
				$('.billingaddress').hide();
			} else {
				$('.billingaddress').show();
			}
		}
		$('input[name=use_same_address_for_billto]').change(togglesameshipbill).each(togglesameshipbill);

		function togglenewaddress() {
			$(this).parents('.addressoption').find('.optiondrawer').slideDown();
			$('[name='+this.name+']').not(':checked').parents('.addressoption').find('.optiondrawer').slideUp();
		}
		$('input[type=radio][name=shipto_id],input[type=radio][name=billto_id]').change(togglenewaddress).not(':checked').parent().find('.optiondrawer').slideUp();

		function changepaymentmethod(newmethod) {
			$('#donorcart_checkout_form .method-dependent').hide().filter('.show-'+newmethod).show();
		}
		$('#donorcart_checkout_form input[name=payment_method]').change(function(){changepaymentmethod($(this).val())}).change();

		function togglerecurring() {
			$('#donorcart_checkout_form .show-recurring')[$('#dcart-checkbox-recurring').is(':checked')?'slideDown':'slideUp']("medium");
		}
		$("#dcart-checkbox-recurring").change(togglerecurring).change();

		function togglededication() {
			$('#donorcart_checkout_form .show-dedicate')[$('#dcart-checkbox-dedicate').is(':checked')?'slideDown':'slideUp']("medium");
		}
		$('#dcart-checkbox-dedicate').change(togglededication).change();

		$('#dcart-order-show-details-link').click(function(){$('#dcart-order-details-container').toggle("medium")});

		$(document).delegate('a.order-locked', 'click', function(e) {
			e.stopImmediatePropagation();
			var original_link = e.target;
			var dialogbuttons = {};
			dialogbuttons[Joomla.JText._('COM_DONORCART_JS_UNLOCK_ORDER','Unlock Order')] = function() {
				$.get(sp_website_root+'index.php?option=com_donorcart&task=resetOrder&format=raw',function(data){
					if(data=='success') {
						$(original_link).removeClass('order-locked').click();
					} else {
						$('<div>'+data+'</div>').appendTo('body').dialog({
							modal: true, title: Joomla.JText._('COM_DONORCART_JS_ORDER_UNLOCK_FAILED','Failed to unlock the order'), zIndex: 10000, autoOpen: true, width: 'auto', resizable: false,
							close: function(event, ui){$(this).remove()}
						});
					}
				}).fail(function(data){
						$('<div>'+data+'</div>').appendTo('body').dialog({
							modal:true, title: Joomla.JText._('COM_DONORCART_JS_ORDER_UNLOCK_FAILED','Failed to unlock the order'), zIndex: 10000, autoOpen: true, width: 'auto', resizable:false,
							close: function(event, ui){$(this).remove()}
						});
				});
				$(this).dialog("close");
			};
			dialogbuttons[Joomla.JText._('COM_DONORCART_JS_CANCEL','Cancel')] = function(){$(this).dialog("close")}
			$('<div>'+Joomla.JText._('COM_DONORCART_JS_ORDER_LOCKED_TEXT','This order has already been submitted. Are you sure you want to continue changing it?')+'</div>').appendTo('body').dialog({
				modal: true, title: Joomla.JText._('COM_DONORCART_JS_ORDER_LOCKED_TITLE','Order has already been submitted'), zIndex: 10000, autoOpen: true, width: 'auto', resizable: false, buttons: dialogbuttons,
				close: function(event, ui){$(this).remove();}
			});
			return false;
		});
		$(document).delegate('a.dcart-link', 'click', function () {
			dcartLoader($(this).attr('href'));
			return false;
		});

		$('form.dcartadd').submit(function(e) {
			dcartLoader($(this).attr('href'), $(this).serialize(), $(this).hasClass('dnoprompt')?'skip':true);
			return false;
		});
		$('form.dcartadd input[name=recurring-add-button]').click(function(){
			this.form['recurring'].value=$(this).attr('data-val');
			$(this.form).submit();
			return false;
		});

		$('#donorcart_checkout_container').delegate('form.donorcart_action_form', 'submit', function() {
			var settings = {url: this.action, type: 'POST', data: $(this).serialize()};
			$('#donorcart_checkout_container').append('<div class="dcart_overlay"><div class="dcart_overlay_inner">Loading...</div></div>');
			$.ajax(settings).done(function(data){
				var newcontainer = document.createElement('div');
				newcontainer.innerHTML=data;
				var newhtml = $(newcontainer).find('#donorcart_checkout_container')
				if(newhtml.length) {
					$('#donorcart_checkout_container').html(newhtml.html());
				} else {
					$('#donorcart_checkout_container').replaceWith(data);
				}
				delete newcontainer;
			}).fail(function(data){
				$('#donorcart_checkout_container').remove('.dcart_overlay');
				alert('There was an error processing your request. Please review the checkout form and try again. If the problem persists after refreshing the page, contact the website administrator for assistance.');
			});
			return false;
		});
	});
})(jQuery);
