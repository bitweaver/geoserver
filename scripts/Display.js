MochiKit.Base.update(BitMap.Map.prototype, {
	"geoserverTilelayerPref":null,

	"geoserverTilelayer":null,

	"geoserverTilelayerId":-1,

	"geoserverGetTilelayer": function(tid,callback){
		doSimpleXMLHttpRequest(BitSystem.urls.geoserver+"view_tilelayers_inc.php", {tilelayer_id:tid}).addCallback( bind(this.geoserverTilelayerCallback, this), tid, callback );
	},

	"geoserverTilelayerCallback": function(tid,callback,rslt){
	    var xml = rslt.responseXML.documentElement;
		var t = BitMap.Geoserver.tilelayers[tid] = {};
		this.parseTilelayerXML(t, xml);

		if( callback != undefined ){
			callback( tid );
		}
	},
	
	// based on func in gmap/scripts/Edit.js
	"parseTilelayerXML": function(tl, xml){
		// convenience
		var $s = partial( bind(this.getXMLTagValue, this), xml );
		var $i = function( s ){ return parseInt( $s( s ) )};
		var $f = function( s ){ return parseFloat( $s( s ) )};

		// assign maptype values to data array	
		tl.tilelayer_id = $i('tilelayer_id');
		tl.tiles_name = $s('tiles_name');
		tl.tiles_minzoom = $i('tiles_minzoom');
		tl.tiles_maxzoom = $i('tiles_maxzoom');		
		tl.ispng = $s('ispng');
		tl.tilesurl = $s('tilesurl');
		tl.opacity = $f('opacity');

		// get the datakey info
		$('gmap-sidepanel-tilelayers-datakeys').innerHTML += $s('datakey');
	},

	"geoserverSetTilelayer": function(tid){
		// change
		if( tid != this.geoserverTilelayerId ){
			// remove
			if ( this.geoserverTilelayer != null ){
				this.map.removeOverlay( this.geoserverTilelayer );
				this.map.removeControl( BitMap.Geoserver.tilelayers[ this.geoserverTilelayerId ].keycontrol );
			}

			// add the tilelayer, tid == -1 is no tilelayer do nothing
			if( tid > -1 ){
				var myCopyright = new GCopyrightCollection("");
				myCopyright.addCopyright(new GCopyright("",
					new GLatLngBounds(new GLatLng(-90,-180), new GLatLng(90,180)),
					0,""));

				// Create the tile layer overlay and 
				// implement the three abstract methods   		
				var t = BitMap.Geoserver.tilelayers[tid];


				if( t == undefined ){
					this.geoserverGetTilelayer(tid, bind(this.geoserverSetTilelayer, this));
					return;
				}

				var opts = {
					'isPng':( t.ispng == true || t.ispng == 'true' )?true:false,
					'opacity':t.opacity
				}

				var tilelayer = new GTileLayer(myCopyright,  t.tiles_minzoom, t.tiles_maxzoom, opts)
				tilelayer.getTileUrl = this.makeGetTileUrl( t.tilesurl );

				this.geoserverTilelayer = new GTileLayerOverlay(tilelayer);

				if( t.keycontrol == undefined ){
					t.keycontrol = this.buildTilelayerKey( removeElement('geoserver_tilelayer_'+tid) );
				}

				this.map.addControl( t.keycontrol );

				this.map.addOverlay( this.geoserverTilelayer );
			}else{
				this.geoserverTilelayer = null;
			}
			this.geoserverTilelayerId = tid;
		}
	},

	"buildTilelayerKey":function( div ){
		ref = this.map;
		var control = function(){};
		control.prototype = new GControl();
		control.prototype.initialize = function(){
			var container = DIV( null, div );
			ref.getContainer().appendChild( container );
			return container;
		};

		control.prototype.getDefaultPosition = function() {
			return new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(8,40));
		};

		mycontrol = new control();
		return mycontrol;
	}

});
