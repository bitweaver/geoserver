{strip}
{if $geoserverTilelayers}
<div id="tilelayers_menu">
{form action="javascript:;" enctype="multipart/form-data" id=geoserver}
	<ul id="nav" class="menu hor">
		<li class="m-home">
			<a class="head" href="#">Data Layers:</a>
		</li>
		{assign var=layerTheme value="NULL"}
		{foreach from=$geoserverTilelayers key=tlid item=layer name=tilelayers_menu}
			{if $layer.theme_title != $layerTheme} 
				{if ($layerTheme && $layerTheme != "NULL") || !$layerTheme}
					</ul>
				</li>
				{/if}
				{assign var="layerTheme" value=$layer.theme_title}
				<li>
					<a class="head" href="#">{$layer.theme_title}</a>
					<ul>
			{/if}
						<li>
							<a class="item" href="javascript:void(0);" onclick="BitMap.MapData[0].Map.geoserverSetTilelayer( parseInt( {$tlid}) );">
								{$layer.tiles_name}
							</a>
						</li>
			{if $smarty.foreach.tilelayers_menu.last}
					</ul>
				</li>
			{/if}
		{/foreach}
		<li style="float:right; border:none; line-height:1em; padding:.4em .25em 0em .25em; background:white !important;">
			<a id="tilelayers_hide_btn" style="{if !$tilelayerPref}display:none;{/if}line-height:1em; padding:0; background:white !important;" href="javascript:void(0)" onclick='BitMap.MapData[0].Map.geoserverSetTilelayer(-1)' title="Hide Data Layer"/><img style="border:none;" src="{$smarty.const.THEMES_STYLE_URL}images/close-grey.gif" /></a>
		</li>
	</ul>
{/form}

{*
{form action="javascript:;" enctype="multipart/form-data" id=geoserver}
{formlabel label="Data Layers"}
	{forminput}
		<select name="tilelayer" onchange="BitMap.MapData[0].Map.geoserverSetTilelayer( parseInt(this.options[this.selectedIndex].value) );">
			<option value="-1" {if !$tilelayerPref}selected="selected"{/if}>None</option>
			{foreach from=$geoserverTilelayers key=tlid item=layer}
				<option value="{$tlid}" {if $tilelayerPref == $layer.tilelayer_id}selected="selected"{/if}>{$layer.theme_title}{$layer.tiles_name}</option>
			{/foreach}
		</select>
	{/forminput}
{formhelp note=""}
{/form}
*}
</div>
{/if}
{/strip}
