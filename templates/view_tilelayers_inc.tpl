{strip}
<div id="view_tilelayers">
	<div class="row">
		<a href="javascript:void(0);" onclick="BitMap.hide('geoserver_tilelayer_themes');" style="float:right">{biticon iname=window-close iexplain="Close"}</a>
		<a href="{$smarty.const.GMAP_PKG_URL}view_tilelayers_inc.php?update_tilelayer_list=1" style="float:right">{biticon iname=view-refresh iexplain="Close"}</a>
		{formlabel label="Tilelayer Theme" for="tilelayer_theme"}
		{forminput}
			<select name="theme_id" id="tilelayer_theme_id" onchange="BitMap.EditSession.getTilelayers(this,'');">
				<option>{tr}All Tilelayers{/tr}</option>
				{foreach from=$themes key=theme_id item=theme}
					<option value="{$theme_id}" {if $theme_id == $smarty.request.theme_id}selected="selected"{/if}>{$theme|replace:"_":" "|capitalize}</option>
				{/foreach}
			</select>
		{/forminput}
	</div>
	<ul>
		<li>
			<a href="javascript:void(0);" onclick="BitMap.EditSession.setTilelayerPref(null,'none');">
				none
			</a>
		</li>
		{foreach from=$geoserverTilelayers key=tlid item=tilelayer}
		<li>
			<a href="javascript:void(0);" onclick="BitMap.EditSession.setTilelayerPref({$tlid},'{$tilelayer.tiles_name}');">
				{$tilelayer.tiles_name}
			</a>
		</li>
		{/foreach}
	</ul>
	{include file="bitpackage:gmap/jspagination.tpl" ajaxHandler="BitMap.EditSession.getTilelayers" ajaxParams="'tilelayer_theme_id'"}
</div>
{/strip}
