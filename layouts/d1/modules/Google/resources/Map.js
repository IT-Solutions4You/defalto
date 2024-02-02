/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/
Vtiger.Class("Google_Map_Js", {}, {

	showMap: function (container) {
		app.helper.showProgress();
		container = jQuery(container);

		let self = this,
			params = {
				'module': 'Google',
				'action': 'MapAjax',
				'mode': 'getLocation',
				'recordid': container.find('#record').val(),
				'source_module': container.find('#source_module').val()
			}

		app.request.post({data: params}).then(function (error, response) {
			app.helper.hideProgress();

			let result = JSON.parse(response),
				address = result.address,
				location = jQuery.trim((address).replace(/\,/g, " "));

			container.find('#record_label').val(result.label);

			if (location) {
				container.find("#address").html(location);
				container.find('#address').removeClass('hide');
			} else {
				app.helper.hidePopup();
				app.helper.showAlertNotification({message: app.vtranslate('Please add address information to view on map')});

				return false;
			}

			container.find("#mapLink").on('click', function () {
				window.open(self.getQueryString(location));
			});
			self.loadMapScript(location);
		});
	},

	loadMapScript: function (location) {
		let self = this,
			API_KEY = 'YOUR_MAP_API_KEY'; // CONFIGURE THIS

		if ('YOUR_MAP_API_KEY' === API_KEY) {
			if (typeof console) {
				console.error("Google Map API Key not configured.");
			}

			jQuery('#map_canvas').html('<iframe style="height: 400px; width: 100%" src="' + self.getQueryString(location) + '&height=400&output=embed"></iframe>')
		} else {
			jQuery.getScript("https://maps.google.com/maps/api/js?key=" + API_KEY + "&sensor=true&async=2&callback=initialize", function () {
			});
		}
	},

	getQueryString : function (address) {
		address = address.replace(/,/g,' ');
		address = address.replace(/ /g,'+');
		return "https://maps.google.com/maps?q=" + address + "&zoom=14&size=512x512&maptype=roadmap&sensor=false";
	}
});

function initialize(){
	geocoder = new google.maps.Geocoder();
	var mapOptions = {
		zoom : 15,
		mapTypeId : google.maps.MapTypeId.ROADMAP,
	};
	map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
	var address = jQuery(document.getElementById('address')).text();
	var label = jQuery(document.getElementById('record_label')).val();
	if(geocoder) {
		geocoder.geocode({'address': address}, function(results, status) {
			if(status == google.maps.GeocoderStatus.OK) {
				if(status != google.maps.GeocoderStatus.ZERO_RESULTS) {
					map.setCenter(results[0].geometry.location);
					var infowindow = new google.maps.InfoWindow({
							content : '<b>'+label+'</b><br><br>'+address,
							size : new google.maps.Size(150,50)
						});
					var marker = new google.maps.Marker({
						position : results[0].geometry.location,
						map : map, 
						title : address
					}); 
					google.maps.event.addListener(marker, 'click', function() {
						infowindow.open(map,marker);
					});
				}
			}
		});
  }
}
