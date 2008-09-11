{strip}
<div id="tilelayers_menu">
{form action="javascript:;" enctype="multipart/form-data" id=geoserver}
{formlabel label="Data Layers"}
	{forminput}
		<select name="tilelayer" onchange="BitMap.MapData[0].Map.setTilelayer( parseInt(this.options[this.selectedIndex].value) );">
			<option value="-1" selected="selected">None</option>
			{foreach from=$geoserverTilelayers key=id item=layer}
			<option value="{$layer.tilelayer_id}" {* selected="selected" *}>{$layer.tiles_name}</option>
			{/foreach}
		</select>
	{/forminput}
{formhelp note=""}
{/form}
</div>

<div style="display:none">
{foreach from=$geoserverTilelayers key=id item=layer}
	{$layer.datakey}
{/foreach}
</div>
{/strip}
