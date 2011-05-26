<div id="tabs">
	<ul>
		<li class="tab" id="tab0">
			<a href="#">{$LANG.SearchFilter}</a>
		</li>
	</ul>
</div>

<div class="tabbox1" id="tab0box" style="display: none;">
	<div id="tab_content">
		<!-- Filter -->
		<form method="post" action="">
			<input type="hidden" value="main" name="filter">
			<table cellspacing="2" cellpadding="3" border="0" width="100%" class="form">
				<tr>
					<td width="15%" class="fieldlabel">{$LANG.ID}</td>
					<td class="fieldarea">
						<input type="text" value="{$smarty.post.userid}" size="25" name="userid">
					</td>
				</tr>
				<tr>
					<td width="15%" class="fieldlabel">{$LANG.FirstName}</td>
					<td class="fieldarea">
						<input type="text" value="{$smarty.post.firstname}" size="25" name="firstname">
					</td>
				</tr>
				<tr>
					<td width="15%" class="fieldlabel">{$LANG.LastName}</td>
					<td class="fieldarea">
						<input type="text" value="{$smarty.post.lastname}" size="25" name="lastname">
					</td>
				</tr>
				<tr>
					<td width="15%" class="fieldlabel">{$LANG.Email}</td>
					<td class="fieldarea">
						<input type="text" value="{$smarty.post.email}" size="25" name="email">
					</td>
				</tr>
				<tr>
					<td colspan="2" class="fieldarea">
						<input type="checkbox" name="filtermapped" id="map-filter"{if $smarty.post.filtermapped} checked="checked"{/if} /> {$LANG.MappedFilter}
					</td>
				</tr>
			</table>

			<img height="5" width="1" src="images/spacer.gif"><br>

			<div align="center">
				<input type="submit" class="button" value="{$LANG.Filter}">
				<input id="resetfilter" type="reset" class="button" value="{$LANG.Reset} {$LANG.Filter}" />
			</div>
		</form>
	</div>
</div>
<table cellspacing="2" cellpadding="3" border="0" width="100%" class="form">
	<tr>
		<td class="fieldlabel">
			{$LANG.Server}
			<select name="server" class="mapserver">
				{foreach from=$onapp_servers key=id item=server}
					<option value="{$id}">{$server.name} | {$server.ipaddress}</option>
				{/foreach}
			</select>
		</td>
	</tr>
</table>

<br/>
{include file='topnav.tpl'}

<div class="tablebg">
	<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
		<tr>
			<th>{$LANG.ID}</th>
			<th>{$LANG.FirstName}</th>
			<th>{$LANG.LastName}</th>
			<th>{$LANG.Email}</th>
			<th>{$LANG.Status}</th>
			<th>{$LANG.Actions}</th>
		</tr>
	{foreach from=$whmcs_users item=user}
		{if $user.not_exist}
			{assign var='bg' value=' style="background-color: #f4cbcb;"'}
		{elseif $user.deleted}
			{assign var='bg' value=' style="background-color: #f7f7bb;"'}
		{elseif $user.mapped}
			{assign var='bg' value=' style="background-color: #ebfee2;"'}
		{else}
			{assign var='bg' value=''}
		{/if}
		<tr>
			<td{$bg}>{$user.id}</td>
			<td{$bg}>{$user.firstname}</td>
			<td{$bg}>{$user.lastname}</td>
			<td{$bg}>{$user.email}</td>
			<td{$bg}>{$user.status}</td>
			<td{$bg}>
				{if $user.mapped}
					<a href="{$BASE}&whmcs_user_id={$user.client_id}&onapp_user_id={$user.onapp_user_id}&server_id={$server_id}&action=info">{$LANG.View}</a>
				{else}
					<a href="{$BASE}&whmcs_user_id={$user.id}&server_id={$server_id}&action=info">{$LANG.Map}</a>
				{/if}
			</td>
		</tr>
	{/foreach}
		<tr>
			<td colspan="6">
				<a href="{$BASE}&server_id={$server_id}&action=syncauthall" target="_blank">
					<input type="button" value="Sync auth for all mapped users" />
				</a>
			</td>
		</tr>
	</table>
</div>

<p align="center">
	{if $prev}
		<a href="{$BASE}&page={$prev}">« {$LANG.Previous} {$LANG.Page}</a>
	{/if}
		&nbsp;
	{if $next}
		<a href="{$BASE}&page={$next}">{$LANG.Next} {$LANG.Page} »</a>
	{/if}
</p>