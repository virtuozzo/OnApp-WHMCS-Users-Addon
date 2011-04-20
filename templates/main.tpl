<div id="tabs">
	<ul>
		<li class="tab" id="tab0">
			<a href="onapp_users.tpl#">{$LANG.SearchFilter}</a>
		</li>
	</ul>
</div>

<div class="tabbox1" id="tab0box" style="display: none;">
	<div id="tab_content">
		<!-- Filter -->
		<form method="post" action="">
			<input type="hidden" value="main" name="filter">
			<table cellspacing="2" cellpadding="3" border="0" width="100%" class="form">
				{*<tr>
					<td class="fieldlabel">{$LANG.Search}</td>
					<td class="fieldarea">
						<select name="client" id="clients">
							<option value="WHMCS">WHMCS</option>
							<option value="OnApp">OnApp</option>
						</select>
					</td>
				</tr>
				<tr id="servers">
					<td class="fieldlabel">{$LANG.Server}</td>
					<td class="fieldarea">
						<select name="server" class="mapserver">
							<option value="">Any</option>
							{foreach from=$onapp_servers key=id item=server}
								<option value="{$id}">{$server.name} | {$server.ipaddress}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				*}
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
			</table>

			<img height="5" width="1" src="images/spacer.gif"><br>

			<div align="center"><input type="submit" class="button" value="{$LANG.Filter}"></div>
		</form>
	</div>
</div>
<table cellspacing="2" cellpadding="3" border="0" width="100%" class="form">
	<tr>
		<td class="fieldlabel">{$LANG.Server}</td>
		<td class="fieldarea">
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

<form action="sendmessage.php?type=general&amp;multiple=true" method="post">
	<div class="tablebg">
		<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
			<tr>
				<th colspan="4">WHMCS</th>
				<th colspan="5">OnApp</th>
			</tr>
			<tr>
				<th>ID</th>
				<th>{$LANG.FirstName}</th>
				<th>{$LANG.LastName}</th>
				<th>{$LANG.Email}</th>
				<th>{$LANG.FirstName}</th>
				<th>{$LANG.LastName}</th>
				<th>{$LANG.Email}</th>
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
				<td{$bg}>{$user.onapp_user_firstname}</td>
				<td{$bg}>{$user.onapp_user_lastname}</td>
				<td{$bg}>{$user.onapp_user_email}</td>
				<td{$bg}>
					{if $user.mapped}
						<a class="unmap" href="{$smarty.server.REQUEST_URI}&whmcs_user_id={$user.client_id}&onapp_user_id={$user.onapp_user_id}&server_id={$user.server_id}&unmap">{$LANG.Unmap}</a>
					{else}
						<a href="{$smarty.server.REQUEST_URI}&whmcs_user_id={$user.id}&server_id={$server_id}&map">{$LANG.Map}</a>
					{/if}
				</td>
			</tr>
		{/foreach}
		</table>
	</div>
</form>