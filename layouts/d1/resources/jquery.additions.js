;(function($) {
	$.fn.disable = function() {
		this.attr('disabled', 'disabled');
	}
	$.fn.enable = function() {
		this.removeAttr('disabled');
	}
})(jQuery);

;(function ($) {
	$.fn.serializeFormData = function () {
		let form = $(this),
			values = form.serializeArray(),
			data = {};

		if (values) {
			$(values).each(function (k, v) {
				if (v.name in data && (typeof data[v.name] != 'object')) {
					let element = form.find('[name="' + v.name + '"]');
					//Only for muti select element we need to send array of values
					if (element.is('select') && element.attr('multiple') != undefined) {
						let prevValue = data[v.name];
						data[v.name] = [];
						data[v.name].push(prevValue)
					}
				}
				if (typeof data[v.name] == 'object') {
					data[v.name].push(v.value);
				} else {
					data[v.name] = v.value;
				}
			});
		}
		// If data-type="autocomplete", pickup data-value="..." set
		let autocompletes = $('[data-type="autocomplete"]', $(this));
		$(autocompletes).each(function (i) {
			let ac = $(autocompletes[i]);
			data[ac.attr('name')] = ac.data('value');
		});

		return data;
	}

})(jQuery);

;(function($) {
	// Case-insensitive :icontains expression
	$.expr[':'].icontains = function(obj, index, meta, stack){
		return (obj.textContent || obj.innerText || jQuery(obj).text() || '').toLowerCase().indexOf(meta[3].toLowerCase()) >= 0;
	}
})(jQuery);