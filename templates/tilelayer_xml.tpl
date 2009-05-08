<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
{if $tilelayerInfo}
	<tilelayer>
		<tilelayer_id>{$tilelayerInfo.tilelayer_id}</tilelayer_id>
		<tiles_name>{$tilelayerInfo.tiles_name|escape}</tiles_name>
		<tiles_minzoom>{$tilelayerInfo.tiles_minzoom}</tiles_minzoom>
		<tiles_maxzoom>{$tilelayerInfo.tiles_maxzoom}</tiles_maxzoom>
		<ispng>{$tilelayerInfo.ispng}</ispng>
		<tilesurl>{$tilelayerInfo.tilesurl}</tilesurl>
		<opacity>{$tilelayerInfo.opacity}</opacity>
		<pos>{$tilelayerInfo.pos}</pos>
		<datakey><![CDATA[{$tilelayerInfo.datakey}]]></datakey>
	</tilelayer>
{else}
	{include file="bitpackage:gmap/edit_status_xml_inc.tpl"}
{/if}
