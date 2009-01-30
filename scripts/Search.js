MochiKit.Base.update(BitMap.Map.prototype, {
	"wfsGetFeaturePath":BitSystem.urls.geoserver + "wfs?request=GetFeature",
	
	"getFeatureByTypeName": function(pHash){
		var url = this.wfsGetFeaturePath + "&typename=" + pHash.typename + "&outputFormat=json";
		if( pHash.bbox != undefined ){
			var b = pHash.bbox;
			url += "&bbox="+b.left+","+b.lower+","+b.right+","+b.upper;
		}else{
			alert( 'no bounds specified we wont let you do this' );
		}

		// doSimpleXMLHttpRequest( url ).addCallback( bind( this.getFeatureByNameCallback ), this ) );
		loadJSONDoc( url ).addCallback( bind( this.getFeatureByTypeNameCallback, this ) );
	},

	"getFeatureByTypeNameCallback": function(rslt){
		var shapes = BitMap.Geoserver.shapes;
		var hash = null;
		//	var xml = rslt.responseXML.documentElement;
		var shapesdata = rslt.features;
		var count = rslt.features.length;

		for( var n=0; n<count; n++ ){
			var data = shapesdata[n];
			if( shapes[data.id] == undefined ){ 
				var shape = {"name":data.properties.name,
							"id":data.id,
							"coordinates":data.geometry.coordinates[0][0]
						};
				hash = shapes[data.id] = shape;
			}else{
				hash = shapes[data.id];
			}

			// add the shape to the map
			this.mapFeatureShape( hash );
		}
	},

	"mapFeatureShape": function(shape){
		if( shape.polygon == undefined ){
			this.defineFeaturePolygon( shape );
		}
		if( shape.polygon.plotted == false ){
			this.map.addOverlay(shape.polygon.overgon);
			shape.polygon.overgon.hide();
			this.map.addOverlay(shape.polygon);
			shape.polygon.plotted = true;
		}
	},

	"defineFeaturePolygon": function( shape ){
		var ref = this;
		var pointlist = [];
		var coords = shape.coordinates;
		var count = shape.coordinates.length;
		for ( var n=0; n<count; n++ ){
			var coord = coords[n];
			var point = new GLatLng(
				parseFloat(coord[1]),
				parseFloat(coord[0])
			);
			pointlist.push(point);
		}
		shape.polygon = new GPolygon(pointlist,"#0000cc", 1, 1, "#0000cc", 0.2);
		shape.polygon.overgon = new GPolygon( pointlist, "#ff3300", 1, 1, "#ff3300", 0.2);
		shape.polygon.overgon.mytype = "shape";

		var tooltip = DIV( {'class':'gmap-tooltip'}, DIV( {'class':'tip-content'}, shape.name ) );
		shape.label = new GPlusLabel( this, shape.polygon, tooltip );

		shape.polygon.myref = shape.polygon.overgon.myref = shape;

		GEvent.addListener(shape.polygon, 'mouseover', function(){ 
			// handling last highlight this way avoids listener delays
			if( ref.lastgon != undefined ){
				ref.lastgon.show();
				ref.lastgon.overgon.hide();
				GEvent.removeListener( ref.lastgon.myref.click );
				ref.map.removeOverlay(ref.lastgon.myref.label);
			}
			shape.polygon.hide();
			shape.polygon.overgon.show();
			shape.click = GEvent.addListener(shape.polygon.overgon, "click", function(overlay){
				ref.setZipCodeInput( shape );
			});
			ref.map.addOverlay(shape.label);
			ref.lastgon = shape.polygon;

			// shape.polygon.setFillStyle( {'color':'#ff3300'} );
		});


		shape.polygon.plotted = false;
	},
		
	"getShapesInBounds": function(typename){
		var limit = 11;
		if( typename == 'ca_zip_5' && this.map.getZoom() < limit ){
			alert( "Sorry, but you must zoom in "+( limit - this.map.getZoom() )+ " more level(s) to make this request. Please zoom in and then try again." );
		}else{
			var req = {};
			req.typename = typename;

			var bounds = this.map.getBounds();
			var ll = bounds.getSouthWest();
			var ur = bounds.getNorthEast(); 
			req.bbox = {
			"left":ll.lng(),
			"right":ur.lng(),
			"lower":ll.lat(),
			"upper":ur.lat()
			}

			this.getFeatureByTypeName( req );
		}
	},

	"setZipCodeInput": function( shape ){
		var f = $('list-query-form');
		f.zipcode.value = shape.name;
	}
});

function GPlusLabel( Map, overlay, content ){
	this.overlay_ = overlay;
	this.Map_ = Map;
	this.overlayBounds_ = this.Map_.getPolyBounds( this.overlay_ );
	this.latlng_ = this.overlayBounds_.getCenter();
	this.content_ = content;
}

GPlusLabel.prototype = new GOverlay();

GPlusLabel.prototype.initialize = function( map ){
	var div = DIV( {'style':'position:absolute'}, this.content_ );
	map.getPane(G_MAP_MARKER_SHADOW_PANE).appendChild(div);
	this.map_ = map;
	this.div_ = div;
}

GPlusLabel.prototype.remove = function(){
	this.div_.parentNode.removeChild(this.div_);
}

GPlusLabel.prototype.copy = function(){
	return new GPlusLabel( this.latlng_, this.content_ );
}

GPlusLabel.prototype.getPoint = function(){
	return this.latlng_;
}

GPlusLabel.prototype.redraw = function(force) {
	if (!force) return;
	if( this.map_.getZoom() < 11 ){
		this.div_.style.visibility = 'hidden';
	}else{
		var latlng = this.latlng_;	
		var mapBounds = this.map_.getBounds();
		if( mapBounds.intersects( this.overlayBounds_ ) && !mapBounds.containsLatLng( this.latlng_ ) ){ 
			var b1 = new GBounds([this.map_.fromLatLngToContainerPixel(mapBounds.getSouthWest()), this.map_.fromLatLngToContainerPixel(mapBounds.getNorthEast())]);
			var b2 = new GBounds([this.map_.fromLatLngToContainerPixel(this.overlayBounds_.getSouthWest()), this.map_.fromLatLngToContainerPixel(this.overlayBounds_.getNorthEast())]);
			var b = GBounds.intersection(b1,b2); 
			var p = b.mid();
			latlng = this.map_.fromContainerPixelToLatLng(p);
		}
		var pos = this.map_.fromLatLngToDivPixel( latlng );
		var xPos = Math.round(pos.x - this.div_.clientWidth / 2);
		var yPos = Math.round(pos.y - this.div_.clientHeight / 2);
		this.div_.style.top = yPos + "px";
		this.div_.style.left = xPos + "px";
		var z = GOverlay.getZIndex(this.latlng_.lat());
		this.div_.style.zIndex = z; 
		this.div_.style.visibility = 'visible';
	}
}

