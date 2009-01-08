BitGeoserver = {
	"initTiletest": function(){
		if (GBrowserIsCompatible()) {
			var map = new GMap2(document.getElementById("map_canvas"));
			map.setCenter(new GLatLng(34.00183723449705, -118.2720069885255), 10);
			map.addControl(new GLargeMapControl());

			var tilelayer =  new GTileLayer(null, null, null, {
				tileUrlTemplate: location.search.substr(1), 
						isPng:true,
						opacity:0.7 }
			);
				   
			var myTileLayer = new GTileLayerOverlay(tilelayer);
			map.addOverlay(myTileLayer);
			
			document.getElementById("url").innerHTML = location.search.substr(1);
		}
	}
}
