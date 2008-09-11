MochiKit.Base.update(BitMap.Map.prototype, {
	"geoserverTilelayer":null,

	"geoserverTilelayerId":-1,

	"setTilelayer": function(tid){
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
