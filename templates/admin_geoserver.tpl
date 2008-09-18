{strip}
{jstabs}
	{jstab title="General Settings"}
		{form}
			<input type="hidden" name="page" value="{$page}" />
			{legend legend="Geoserver Settings"}
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
	{/jstab}
	{jstab title="Tilelayers"}
		{legend legend="Import/Update Tile Layers"}
			<a href="{$smarty.const.GEOSERVER_PKG_URL}admin/csv_tilelayers_import.php">Import CSV Tile Layers</a>
		{/legend}
		{form}
			<input type="hidden" name="page" value="{$page}" />
			{legend legend="Tilelayers"}
				<table class="data">
					<caption>{tr}Tilelayers Listing{/tr}</caption>
					<tr>
						<th>{tr}Name{/tr}</th>
						<th>{tr}Actions{/tr}</th>
					</tr>
					{cycle values="even,odd" print=false}
					{foreach from=$geoserverTilelayers key=tlid item=layer}
					<tr class="{cycle}">
						<td>{$layer.tiles_name}</td>
						<td>
							<select name="tilelayers[{$tlid}]" >
							{foreach from=$geoserverTilelayerThemes key=id item=theme}
								<option value="{$id}" {if $layer.theme_id == $id}selected="selected"{/if}>{$theme}</option>
							{foreachelse}
								{tr}No themes found{/tr}
							{/foreach}
							</select>
						</td>
					</tr>
					{foreachelse}
					<tr class="norecords"><td colspan="{$cols}">
						{tr}No records found{/tr}
					</td></tr>
					{/foreach}
				</table>
				<div class="row submit">
					<input type="submit" name="batch_submit" value="{tr}Submit{/tr}" />
				</div>
			{/legend}
		{/form}
	{/jstab}
	{jstab title="Tilelayer Themes"}
		{form}
			<input type="hidden" name="page" value="{$page}" />
			<div class="row">
				{formlabel label="Add Themes" for="themes"}
				{forminput}
					<input type="text" name="themes" id="themes" value="" />
					{formhelp note="Enter key words to describe tilelayers. Separate each theme with a comma: theme one,theme two."}
				{/forminput}
			</div>

		{if count($geoserverTilelayerThemes) > 0 }
			<div class="row themes">
				<strong>{tr}Existing Themes:{/tr}</strong>&nbsp;
				{foreach from=$geoserverTilelayerThemes key=id item=theme}
					{$theme}<br />
				{foreachelse}
					{tr}No themes found{/tr}
				{/foreach}
			</div>
		{/if}

		<div class="row submit">
			<input type="submit" name="themes_submit" value="{tr}Submit{/tr}" />
		</div>
		{/form}
	{/jstab}
{/jstabs}
{/strip}
