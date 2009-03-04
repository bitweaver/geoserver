{strip}
<Filter xmlns:gml="http://www.opengis.net/gml">
	{if $bbox}
	<BBOX>
	<PropertyName>geom</PropertyName>
        <gml:Box>
          <gml:coordinates>{$bbox}</gml:coordinates>	
        </gml:Box> 
	</BBOX>
	{/if}
	<And>
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
	{if $offset}
	        <PropertyIsEqualTo>
			<PropertyName>offset</PropertyName>
			<Literal>{$offset}</Literal>
		</PropertyIsEqualTo>
	{/if}
        <PropertyIsEqualTo>
		<PropertyName>requesting_user_is_admin</PropertyName>
		<Literal>{if $gBitUser->isAdmin()}1{else}0{/if}</Literal>
	</PropertyIsEqualTo>
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
	</And>
</Filter>
{/strip}