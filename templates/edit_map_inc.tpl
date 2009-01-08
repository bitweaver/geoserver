{strip}
<div class="row">
	{formlabel label="Data Layer" for="geoserver_tilelayer_id"}
	{forminput}
		<a href="javascript:void(0);" onclick="BitMap.EditSession.getTilelayers(null,'');" title="{tr}Click to select another data layer{/tr}">
			<span id="geoserver_tilelayer_name">{if $tilelayerPref}{$tilelayerPrefName}{else}none{/if}</span>
		</a>
		<div id="geoserver_tilelayer_themes" style="position:absolute; padding:10px; width:242px; overflow:auto; display:none; background:white; border:1px solid #999"></div>
		<input name="geoserver_tilelayer_id" id="geoserver_tilelayer_id" type="hidden" value="{$tilelayerPref}" />
	{/forminput}
</div>
{/strip}
