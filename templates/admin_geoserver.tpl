{strip}
{form}
	{legend legend="Geoserver Settings"}
		<input type="hidden" name="page" value="{$page}" />
		<div class="row">
			{formlabel label="Path to geoserver data directory" for=geoserver_data_path}
			{forminput}
				<input type="text" name="geoserver_data_path" size="50" value="{if $gBitSystem->getConfig('geoserver_data_path')}{$gBitSystem->getConfig('geoserver_data_path')}{/if}" />
			{/forminput}
		</div>
	{/legend}
	<div class="row submit">
		<input type="submit" name="geoserver_preferences" value="{tr}Change Preferences{/tr}" />
	</div>
{/form}
{legend legend="Import/Update Tile Layers"}
	<a href="{$smarty.const.GEOSERVER_PKG_URL}admin/csv_tilelayers_import.php">Import CSV Tile Layers</a>
{/legend}
{/strip}
