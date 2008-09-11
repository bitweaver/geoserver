{ldelim}literal{rdelim}
<script type="text/javascript">
{literal}
BitMap.Geoserver = {'tilelayers':{
{/literal}
{foreach from=$geoserverTilelayers key=id item=layer}
	'{$layer.tilelayer_id}':
	{ldelim}
        tilelayer_id:{$layer.tilelayer_id},
        tiles_name:"{$layer.tiles_name}",
        tiles_minzoom:{$layer.tiles_minzoom},
        tiles_maxzoom:{$layer.tiles_maxzoom},
        ispng:{$layer.ispng},
        tilesurl:"{$layer.tilesurl}&format=image/gif",
        opacity:{$layer.opacity}
	{rdelim},
{/foreach}
{literal}
}};
{/literal}
</script>
{ldelim}/literal{rdelim}
