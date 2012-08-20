<div id="tab_content">
	<table width="100%" cellspacing="2" cellpadding="3" border="0" class="form">
		<tr>
			<td class="fieldlabel">{$LANG.FirstName}</td>
			<td class="fieldarea">{$whmcs_user.firstname}</td>
			<td width="15%" class="fieldlabel">Address</td>
			<td class="fieldarea">
				{$whmcs_user.address1}
				{$whmcs_user.address2}
			</td>
		</tr>
		<tr>
			<td width="15%" class="fieldlabel">{$LANG.LastName}</td>
			<td class="fieldarea">{$whmcs_user.lastname}</td>
			<td class="fieldlabel">City</td>
			<td class="fieldarea"></td>
		</tr>
		<tr>
			<td valign="top" class="fieldlabel">Company Name</td>
			<td valign="top" class="fieldarea">{$whmcs_user.companyname}</td>
			<td class="fieldlabel">State /Region</td>
			<td class="fieldarea">{$whmcs_user.state}</td>
		</tr>
		<tr>
			<td class="fieldlabel">Phone Number</td>
			<td class="fieldarea">{$whmcs_user.phonenumber}</td>
			<td class="fieldlabel">Postcode</td>
			<td class="fieldarea">{$whmcs_user.postcode}</td>
		</tr>
		<tr>
			<td class="fieldlabel">Email Address</td>
			<td class="fieldarea">{$whmcs_user.email}</td>
			<td class="fieldlabel">Country</td>
			<td class="fieldarea">{$whmcs_user.country}</td>
		</tr>
	</table>
	<br/>

	<!-- Filter -->
	<form method="post" action="">
		<input type="hidden" value="map" name="filter">
		<table cellspacing="2" cellpadding="3" border="0" width="100%" class="form">
			<tr>
				<td class="fieldlabel">{$LANG.Server}</td>
				<td class="fieldarea">
					<b>{$onapp_servers[$smarty.get.server_id].name} | {$onapp_servers[$smarty.get.server_id].ipaddress}</b>
				</td>
			</tr>
			<tr>
				<td width="15%" class="fieldlabel">{$LANG.SearchOnAppUser}</td>
				<td class="fieldarea">
					<input type="text" value="{$search}" size="25" name="search">
				</td>
			</tr>
		</table>

		<img height="5" width="1" src="images/spacer.gif"><br>

		<div align="center">
			<input type="submit" class="button" value="{$LANG.Search}">
            <input onclick="$('input[name=\'search\']').val('')" type="submit" class="button" value="{$LANG.Reset} {$LANG.Filter}">
		</div>
	</form>
</div>

<br/>
{include file='topnav.tpl'}

<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
	<tr>
		<th>{$LANG.OnAppUserID}</th>
		<th>{$LANG.FirstName}</th>
		<th>{$LANG.LastName}</th>
		<th>{$LANG.Email}</th>
		<th>{$LANG.Actions}</th>
	</tr>
	{foreach from=$onapp_users item=user}
        {if $user->_mapped eq true}
            {assign var='bg' value=' style="background-color: #ebfee2;"'}
        {else}
            {assign var='bg' value=''}
        {/if}        
		<tr>
			<td{$bg}>{$user->_id}</td>
			<td{$bg}>{$user->_first_name}</td>
			<td{$bg}>{$user->_last_name}</td>
			<td{$bg}>{$user->_email}</td>
            {if $user->_mapped eq false}
			<td{$bg}>
				<a href="{$BASE}&onapp_user_id={$user->_id}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&action=domap">{$LANG.Map}</a>
			</td>
            {else}
            <td{$bg}>
				{$LANG.AlreadyMapped}
			</td>
            {/if}
		</tr>
	{/foreach}
</table>

<p align="center">
	{if $prev && $search eq false}
		<a href="{$BASE}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&action=info&page={$prev}">« {$LANG.Previous} {$LANG.Page}</a>
	{/if}
		&nbsp;
	{if $next && $search eq false}
		<a href="{$BASE}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&action=info&page={$next}">{$LANG.Next} {$LANG.Page} »</a>
	{/if}
</p>