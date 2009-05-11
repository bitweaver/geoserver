MochiKit.Base.update(BitMap.Map.prototype, {
	"zipcodes":{},
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

			// if we only have one zoom in on it
			if( count == 1 ){
				shape = shapes[data.id];
				this.centerMapOnPoly( shape.polygon );
				shape.polygon.hide();
				shape.polygon.overgon.show();
				this.map.addOverlay(shape.label);
				// what a mess - repeated in poly listner
				this.clearLastPolygon();
				this.lastgon = shape.polygon;
			}
		}
		if( count == 0 ){
			// hack
			alert( "Sorry, the zipcode you requested could not be found" );
		}
		this.zipCheckPending = false;
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

	"clearLastPolygon": function(){
		if( this.lastgon != undefined ){
			this.lastgon.show();
			this.lastgon.overgon.hide();
			if( this.lastgon.myref.click != undefined ){
				GEvent.removeListener( this.lastgon.myref.click );
			}
			this.map.removeOverlay(this.lastgon.myref.label);
		}
	},

	"defineFeaturePolygon": function( shape ){
		var ref = this;
		var pointlist = [];
		var pointsstring = "";
		var coords = shape.coordinates;
		var count = shape.coordinates.length;
		for ( var n=0; n<count; n++ ){
			var coord = coords[n];
			var point = new GLatLng(
				parseFloat(coord[1]),
				parseFloat(coord[0])
			);
			pointlist.push(point);
			pointsstring += coord[1] + "," + coord[0] + " ";
		}
		this.zipcodes[shape.name] = pointsstring;
		shape.polygon = new GPolygon(pointlist,"#0000cc", 1, 1, "#0000cc", 0.2);
		shape.polygon.overgon = new GPolygon( pointlist, "#ff3300", 1, 1, "#ff3300", 0.2);
		shape.polygon.overgon.mytype = "shape";

		var tooltip = DIV( {'class':'gmap-tooltip'}, DIV( {'class':'tip-content'}, shape.name ) );
		shape.label = new GPlusLabel( this, shape.polygon, tooltip );

		shape.polygon.myref = shape.polygon.overgon.myref = shape;

		GEvent.addListener(shape.polygon, 'mouseover', function(){ 
			// handling last highlight this way avoids listener delays
			ref.clearLastPolygon();
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

	"getShape": function(typename, property, value){
		this.zipCheckPending = true;
		if( value != '' ){
			var url = this.wfsGetFeaturePath + "&outputFormat=json&typename=" + typename + "&Filter=<Filter><PropertyIsEqualTo><PropertyName>"+property+"</PropertyName><Literal>"+value+"</Literal></PropertyIsEqualTo></Filter>";
			loadJSONDoc( url ).addCallback( bind( this.getFeatureByTypeNameCallback, this ) );
		}
	},

	"setZipCodeInput": function( shape ){
		var f = $('list-query-form');
		f.zipcode.value = shape.name;
	},

	"checkZipcode": function( zipcode, f ){
		if( this.zipCheckPending ){
			setTimeout( bind( this.checkZipcode, this ), 1000, zipcode, f ); 
		}else{
			if( this.zipcodes[zipcode] != undefined ){ 
				this.getContent( f );
			}
		}
	},

	"search": function( f, page ){
		if( f.zipcode.value != '' ){
			this.checkZipcode( f.zipcode.value, f );
		}else{
			this.RequestContent( f, page );
		}
	},

	"getContent": function( f, offset ){
		var params = [];
		var rest = function( key,val,hash ){
			if( typeof( hash ) == "undefined" ){
				hash = params;
			}
			hash.push( "<PropertyIsEqualTo><PropertyName>"+key+"</PropertyName><Literal>"+val+"</Literal></PropertyIsEqualTo>" );
		}

		// group
		if( typeof( f.search_group_content_id ) != 'undefined' ){
			rest("search_group_content_id",f.search_group_content_id.value); 
		}

		// zip
		if( f.zipcode.value != "" ){
			var coords = this.zipcodes[f.zipcode.value];
			params.push( '<Within><PropertyName>geom</PropertyName><gml:Polygon><gml:outerBoundaryIs><gml:LinearRing><gml:coordinates cs="," decimal="." ts=" ">'+coords+'</gml:coordinates></gml:LinearRing></gml:outerBoundaryIs></gml:Polygon></Within>' );
		}

		// find
		var findrest = [];
		if( f.highlight.value != "" ){
			var finds = f.highlight.value.split(",");
			for( var i in finds ){
				// trim leading and trailing white space
				var find = finds[i].replace(/^[\s]+|[\s]+$/g,"");
				if( find != '' ) rest("find",find,findrest); 
			}
			if( findrest.length > 1 ){
				var andor = (f.join[0].checked)?"And":"Or";
				findrest.unshift( "<"+andor+">" );
				findrest.push( "</"+andor+">" );
			}
			if( findrest.length > 0 ) params.push( findrest.join("") );
		}

		// tags
		var tagrest = [];
		if( f.tags.value != "" ){
			var tags = f.tags.value.split(",");
			for( var i in tags ){
				// trim leading and trailing white space
				var tag = tags[i].replace(/^[\s]+|[\s]+$/g,"");
				if( tag != '' ) rest("tags",tag,tagrest); 
			}
			if( tagrest.length > 1 ){
				tagrest.unshift( "<Or>" );
				tagrest.push( "</Or>" );
			}
			if( tagrest.length > 0 ) params.push( tagrest.join("") );
		}

		// content types
		var ctypes = [];
		if( f.content_type_guid[0].selected ){
			var allctypes = true;
		}
		for (var i=1; i<f.content_type_guid.length; i++){ 
			if ( allctypes || f.content_type_guid[i].selected ){
				rest( "content_type_guid", f.content_type_guid[i].value, ctypes );
			}
		}
		if( ctypes.length > 1 ){
			ctypes.unshift( "<Or>" );
			ctypes.push( "</Or>" );
		}
		if( ctypes.length > 0 ) params.push( ctypes.join("") );

		if( params.length > 1 ){
			params.unshift( "<And>" );
			params.push( "</And>" );
		}

		var url = this.wfsGetFeaturePath + "&outputFormat=json&maxfeatures=10&typename=liberty&FILTER=<Filter>" + params.join("") + "</Filter>";

		// offset
		if( offset != undefined ){
			url+='&offset='+offset;
		}else{
			offset = 0;
		}

		loadJSONDoc( url ).addCallback( bind( this.getContentCallback, this ), offset );
	},

	"getContentCallback": function( last_offset, rslt ){
		var count = rslt.features.length;
		var hash = { "Status": { "code": 200, "request": "datasearch" }, "Content":[] };
		if( count > 0 ){
			var data = rslt.features; 
			for( var n=0; n<count; n++ ){
				var item = data[n].properties;
				var geom = data[n].geometry.coordinates;
				item.lng = geom[1]; 
				item.lat = geom[0];
				item.display_url = '/index.php?content_id=' + item.content_id;
				hash.Content.push( item ); 
			}
		}else{
			hash.Status.code = 204;
		}
		this.ReceiveContent( "No permalink available for this request.", hash );
		var listInfo = {};
		listInfo.items_count = hash.Content.length;
		listInfo.last_offset = last_offset;
		// hide the list view link since we cant
		$('gmap-block-viewaslist').style.display = "none";
		this.attachZipPagination( listInfo );
	},

	"attachZipPagination": function(ListInfo){
		//forced some day could allow this to be variable
		var max_count = 10;

		var ic = ListInfo.items_count;
		var lo = ListInfo.last_offset;

		if ( ic == max_count || lo > 0 ){
			var prevLink = (lo > 0)?A ( {'href':'javascript:void(0);', 'onclick':'javascript:BitMap.MapData[0].Map.getContent(document["list-query-form"],'+(lo-max_count)+');'}, "« Prev "+max_count ):null;
			var nextLink = (ic == max_count)?A ( {'href':'javascript:void(0);', 'onclick':'javascript:BitMap.MapData[0].Map.getContent(document["list-query-form"],'+(lo+max_count)+');'}, "Next "+max_count+" »" ):null;
			
			var d =DIV( {'class':'pagination'}, 
				prevLink,
				(( prevLink != null && nextLink != null )?SPAN( null, " - " ):null),
				nextLink
			);
			$('gmap-sidepanel-table').appendChild( d );
		}
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
		/*
		if( mapBounds.intersects( this.overlayBounds_ ) && !mapBounds.containsLatLng( this.latlng_ ) ){ 
			var b1 = new GBounds([this.map_.fromLatLngToContainerPixel(mapBounds.getSouthWest()), this.map_.fromLatLngToContainerPixel(mapBounds.getNorthEast())]);
			var b2 = new GBounds([this.map_.fromLatLngToContainerPixel(this.overlayBounds_.getSouthWest()), this.map_.fromLatLngToContainerPixel(this.overlayBounds_.getNorthEast())]);
			var b = GBounds.intersection(b1,b2); 
			var p = b.mid();
			latlng = this.map_.fromContainerPixelToLatLng(p);
		}
		*/
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

