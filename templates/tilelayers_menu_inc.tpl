{strip}
<div id="tilelayers_menu">
{form action="javascript:;" enctype="multipart/form-data" id=geoserver}
{formlabel label="Data Layers"}
	{forminput}
		<select name="tilelayer" onchange="BitMap.MapData[0].Map.geoserverSetTilelayer( parseInt(this.options[this.selectedIndex].value) );">
			<option value="-1" {if !$tilelayerPref}selected="selected"{/if}>None</option>
			{foreach from=$geoserverTilelayers key=tlid item=layer}
				<option value="{$tlid}" {if $tilelayerPref == $layer.tilelayer_id}selected="selected"{/if}>{$layer.tiles_name}</option>
			{/foreach}
		</select>
	{/forminput}
{formhelp note=""}
{/form}
</div>
{/strip}
