{if $smarty.const.ACTIVE_PACKAGE eq 'gmap' || $smarty.request.display_mode eq 'map'}
<div class="row">
	{formlabel label="Zip Code:" for="zipcode"}
	{forminput}
		<input type="text" name="zipcode" value="" onchange="BitMap.MapData[0].Map.getShape('ca_zip_5','zcta',this.value);"/>
		{formhelp note="Note: Currenty only California is supported"}
		{formhelp note="<a href=\"javascript:void(0);\" onclick=\"BitMap.MapData[0].Map.getShapesInBounds('ca_zip_5');\">Load Zip Codes in View</a>"}
	{/forminput}
</div>
{/if}
