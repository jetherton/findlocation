//hijack the find button and use it for something better
$(document).ready(function() {
		
		//change the class/id of the input objects used to search for places
		$(".btn_find").attr("class","better_find_btn");
		$('#location_find').attr("id", "better_location_find");
		
		//now give those new names event handlers
		$('#better_location_find').bind('keypress', function(e) {
			var code = (e.keyCode ? e.keyCode : e.which);
			if(code == 13) { //Enter keycode
				better_geoCode();
				return false;
			}
		});
		
		$('.better_find_btn').live('click', function () {
			better_geoCode();
			return false;
		});
		
		//now make a place for the results
		$('.report-find-location').append('<div id="find_location_results"></div>');
		$('.incident-find-location').append('<div id="find_location_results"></div>');
	});


	/**
	 * Uses the Find Location plugin
	 */
	function better_geoCode()
	{
		var baseUrl = $('#base_url').text();
		$('#find_loading').html('<img src="' + baseUrl + 'media/img/loading_g.gif">');
		address = $("#better_location_find").val();		
		$.get(baseUrl + 'findlocation/geocode/', { address: address },
			function(data){
			
				$('#find_location_results').html(data);
				$('#find_loading').html('');
				
			}); 
		return false;
	}
	
	/***************************************
	*Put things on the map based on a geolocation
	****************************************/
	function placeLocation(lat, lon, name)
	{
		// Clear the map first
		vlayer.removeFeatures(vlayer.features);
		$('input[name="geometry[]"]').remove();
		
		point = new OpenLayers.Geometry.Point(lon, lat);
		OpenLayers.Projection.transform(point, proj_4326,proj_900913);
		
		
		f = new OpenLayers.Feature.Vector(point);
		vlayer.addFeatures(f);
		
		// create a new lat/lon object
		myPoint = new OpenLayers.LonLat(lon, lat);
		myPoint.transform(proj_4326, map.getProjectionObject());

		// display the map centered on a latitude and longitude
		var default_zoom = $("#default_zoom").text();
		map.setCenter(myPoint, default_zoom);
		
		// Update form values
		$("#latitude").attr("value", lat);
		$("#longitude").attr("value", lon);
		$("#location_name").attr("value", name);

		return false;
	}

