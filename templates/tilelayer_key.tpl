<div id="geoserver_tilelayer_{$tilelayer.tilelayer_id}" class='tilelayer_key'>
	<h4>{$tilelayer.tiles_name}</h4>
	{foreach from=$keyRows key=id item=row}
	<div class='tilelayer_key_row'>
		<div class='tilelayer_key_color' style='background-color:{$row.color};'>{* nothing here, this is just a color block expanded by css *}</div>
		&nbsp;{$row.range}
	</div>
	{/foreach}
</div>
