<div id="tabs">
	<ul>
		<li class="tab" id="tab0">
			<a id="filter_whmcs_users" {if $filterisset eq true}class="openit"{/if} href="#">{$LANG.SearchFilter}</a>
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
						<input type="text" value="{$filter.userid}" size="25" name="userid">
					</td>
				</tr>
				<tr>
					<td width="15%" class="fieldlabel">{$LANG.FirstName}</td>
					<td class="fieldarea">
						<input type="text" value="{$filter.firstname}" size="25" name="firstname">
					</td>
				</tr>
				<tr>
					<td width="15%" class="fieldlabel">{$LANG.LastName}</td>
					<td class="fieldarea">
						<input type="text" value="{$filter.lastname}" size="25" name="lastname">
					</td>
				</tr>
				<tr>
					<td width="15%" class="fieldlabel">{$LANG.Email}</td>
					<td class="fieldarea">
						<input type="text" value="{$filter.email}" size="25" name="email">
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
				<input id="resetfilter" type="button" class="button" value="{$LANG.Reset} {$LANG.Filter}" />
				<input type="hidden" name="server_id" value="{$server_id}" />
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

<form action="" method="post" id="blockops">
	<div class="tablebg">
		<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
			<tr>
				<th></th>
				<th>{$LANG.WHMCSClientID}</th>
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
				<td{$bg}>
					{if $user.mapped}
						<input type="checkbox" name="selection[]" value="{$user.id}"/>
					{else}
						<input type="checkbox" disabled="disabled"/>
					{/if}
				</td>
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
		</table>

		<div class="blockops">
			With Selected:
			<button value="activate">{$LANG.Activate}</button>
			<button value="suspend">{$LANG.Suspend}</button>
			<button value="unmap" class="unmap">{$LANG.Unmap}</button>
			<button value="syncdata">{$LANG.Sync} {$LANG.Data}</button>
			<button value="syncauth">{$LANG.Sync} {$LANG.LoginPassword}</button>
			<input type="hidden" name="blockops" value=""/>
		</div>
	</div>
</form>

<p align="center">
	{if $prev}
		<a href="{$BASE}&server_id={$server_id}&page={$prev}">« {$LANG.Previous} {$LANG.Page}</a>
	{/if}
		&nbsp;
	{if $next}
		<a href="{$BASE}&server_id={$server_id}&page={$next}">{$LANG.Next} {$LANG.Page} »</a>
	{/if}
</p>