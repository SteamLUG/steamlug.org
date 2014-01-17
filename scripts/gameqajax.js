(function($) {
	$.fn.gameqajax = function(options) {
		var self = this;
		var settings = $.extend({}, $.fn.gameqajax.defaults, options);
		if (settings.url !== null && settings.host !== null && settings.port !== null && settings.type !== null) {
			var cols = $(self).children();
			$.ajax({
				url: settings.url,
				type: "post",
				data: { host: settings.host, port: settings.port, type: settings.type },
				timeout: 5000
			}).done(function(json) {
				var result = JSON && JSON.parse(json) || $.parseJSON(json);
				$.each(result, function(key, value) {
					cols[1].innerHTML = (value["secure"]) ? "<img src='/images/vac.png' alt='VAC Enabled'>" : "";
					cols[2].innerHTML = (value["gq_password"] === "1") ? "<img src='/images/padlock.png' alt='Password Protected'>" : "";
					cols[5].innerHTML = ((value["gq_numplayers"]) ? value["gq_numplayers"] : "0") + " / " + value["gq_maxplayers"];
					cols[6].innerHTML = value["gq_mapname"];
					cols[7].innerHTML = "<span class='online'>Online</span>";
				});
			}).fail(function() {
				cols[5].innerHTML = "N/A";
				cols[6].innerHTML = "N/A";
				cols[7].innerHTML = "<span class='offline'>Offline</span>";
			});
		} else {
			return;
		}
	};
	$.fn.gameqajax.defaults = {
		url: null,
		host: null,
		port: null,
		type: null
	};
}(jQuery));
