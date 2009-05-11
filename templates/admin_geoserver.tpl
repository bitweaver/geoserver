{strip}
{jstabs}
	{jstab title="Tilelayers"}
		{legend legend="Import/Update Tile Layers"}
			<a href="{$smarty.const.GEOSERVER_PKG_URL}admin/csv_tilelayers_import.php">Import CSV Tile Layers to CMS</a>
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
	{jstab title="Data Uploader"}
		{legend legend="CSV Data Uploader an Styler"}
			<a href="{$smarty.const.GEOSERVER_PKG_URL}admin/csvstyler/index.php">Import CSV Data to Geoserver</a>
		<p>
			The CSV Data uploader is for uploading California Zip Code based data sets to Geoserver. Geoserver creates map tiles from the dataset. The import tool also allows one to colorize the layers. You should be versed in how the CSV Data Styling tool works, familiar with Geoserver Web Cache, and Geoserver in general before attempting to use this tool. 
		</p>
		{/legend}
	{/jstab}
{/jstabs}
{/strip}
