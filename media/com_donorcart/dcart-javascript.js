(function($){
	$(document).ready(function() {
		$('#my-item-price').blur(function () {
			var amount = this.value;
			if (isNaN(amount) || amount < 0) {
				$("#donate-error").css('display', 'block');
			} else {
				$("#donate-error").css('display', 'none');
			}
		});
		$('#my-item-price').focus(function () {
			if (!this.disabled) {
				this.select();
			}
		});

		$('form.dcart').submit(function () {
			$.post('index2.php?option=com_donorcart&view=cart&task=updateCart',
				$('form.dcart').serialize(),
				function(data){$('#dcart').html(data)});
			return false;
		});

		$('#dcart').keydown(function (e) {
			if (e.which == 13) {
				return false
			}
		});

		$('#dcart a.dcart_link').live('click', function () {
			$.get($(this).attr('href'), function(data){$('#dcart').html(data)});
			return false;
		});

		$('#dcart input.dcart-qty').live('keyup', function () {
			var d = setTimeout(function () {
				$.post('index2.php?option=com_donorcart&view=cart&task=updateCart',
					$('form.dcart').serialize(),
					function(data){$('#dcart').html(data)});
			}, 1000);
			$(this).keydown(function () {
				window.clearTimeout(d)
			})
		})
	})
})(jQuery);
