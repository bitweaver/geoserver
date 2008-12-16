<Filter>
	{if $bbox}
	<BBOX xmlns:gml="http://www.opengis.net/gml" >
	<PropertyName>geom</PropertyName>
        <gml:Box>
          <gml:coordinates>{$bbox}</gml:coordinates>	
        </gml:Box> 
	</BBOX>
	{/if}
	{if $filter}
		{$filter}
	{/if}
	{if $gBitSystem->isFeatureActive("liberty_display_status")}
		<Or>
			<PropertyIsGreaterThan>
				<PropertyName>content_status_id</PropertyName>
				<Literal>{if $gBitUser->isAdmin()}-1000{else}0{/if}</Literal>
			</PropertyIsGreaterThan>
		        <PropertyIsEqualTo>
				<PropertyName>user_id</PropertyName>
				<Literal>{$gBitUser->mUserId|default:-1}</Literal>
			</PropertyIsEqualTo>
		</Or>
	{/if}
        <PropertyIsEqualTo>
		<PropertyName>requesting_users_id</PropertyName>
		<Literal>{$gBitUser->mUserId|default:-1}</Literal>
	</PropertyIsEqualTo>
        <PropertyIsEqualTo>
		<PropertyName>requesting_users_groups</PropertyName>
		<Literal>{strip}
		{foreach name=group_ids from=$gBitUser->mGroups key=id item=group}
			{$id}{if not $smarty.foreach.group_ids.last},{/if}
		{foreachelse}
			-1
		{/foreach}
		{/strip}</Literal>
	</PropertyIsEqualTo>
</Filter>
