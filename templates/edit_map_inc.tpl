{strip}
<div class="row">
	{formlabel label="Data Layer" for="geoserver_tilelayer"}
	{forminput}
	<input type="text" name="geoserver_tilelayer" id="geoserver_tilelayer" value="{if $gContent}{$tilelayerPref}{/if}" />
	{/forminput}
</div>
{*
{foreach from=$geoserverTilelayers key=id item=layer}
	{$layer.tilelayer_id}
{/foreach}
*}
{/strip}
