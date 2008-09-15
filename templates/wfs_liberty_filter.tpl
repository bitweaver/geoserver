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
