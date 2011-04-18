<div id="tab_content">
	<table width="100%" cellspacing="2" cellpadding="3" border="0" class="form">
		<tr>
			<td class="fieldlabel">{$LANG.FirstName}</td>
			<td class="fieldarea">{$whmcs_users[$smarty.get.whmcs_user_id].firstname}</td>
			<td width="15%" class="fieldlabel">Address</td>
			<td class="fieldarea">
			{$whmcs_users[$smarty.get.whmcs_user_id].address1}
				{$whmcs_users[$smarty.get.whmcs_user_id].address2}
			</td>
		</tr>
		<tr>
			<td width="15%" class="fieldlabel">{$LANG.LastName}</td>
			<td class="fieldarea">{$whmcs_users[$smarty.get.whmcs_user_id].lastname}</td>
			<td class="fieldlabel">City</td>
			<td class="fieldarea"></td>
		</tr>
		<tr>
			<td valign="top" class="fieldlabel">Company Name</td>
			<td valign="top" class="fieldarea">{$whmcs_users[$smarty.get.whmcs_user_id].companyname}</td>
			<td class="fieldlabel">State /Region</td>
			<td class="fieldarea">{$whmcs_users[$smarty.get.whmcs_user_id].state}</td>
		</tr>
		<tr>
			<td class="fieldlabel">Phone Number</td>
			<td class="fieldarea">{$whmcs_users[$smarty.get.whmcs_user_id].phonenumber}</td>
			<td class="fieldlabel">Postcode</td>
			<td class="fieldarea">{$whmcs_users[$smarty.get.whmcs_user_id].postcode}</td>
		</tr>
		<tr>
			<td class="fieldlabel">Email Address</td>
			<td class="fieldarea">{$whmcs_users[$smarty.get.whmcs_user_id].email}</td>
			<td class="fieldlabel">Country</td>
			<td class="fieldarea">{$whmcs_users[$smarty.get.whmcs_user_id].country}</td>
		</tr>
	</table>
	<br/>

	<!-- Filter -->
	<form method="post" action="">
		<input type="hidden" value="true" name="mapfilter">
		<table cellspacing="2" cellpadding="3" border="0" width="100%" class="form">
            <tr id="servers2">
				<td class="fieldlabel">{$LANG.Server}</td>
				<td class="fieldarea">
                    {$onapp_servers[$smarty.get.server_id].name} | {$onapp_servers[$smarty.get.server_id].ipaddress}
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
		</table>

		<img height="5" width="1" src="images/spacer.gif"><br>

		<div align="center"><input type="submit" class="button" value="{$LANG.Filter}"></div>
	</form>
</div>

<br/>
{include file='topnav.tpl'}

<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
	<tr>
		<th colspan="6">OnApp</th>
	</tr>
	<tr>
		<th>ID</th>
		<th>{$LANG.FirstName}</th>
		<th>{$LANG.LastName}</th>
		<th>{$LANG.Email}</th>
		<th>{$LANG.Actions}</th>
	</tr>
{foreach from=$onapp_users item=user}
	<tr>
		<td{$bg}>{$user->_id}</td>
		<td{$bg}>{$user->_first_name}</td>
		<td{$bg}>{$user->_last_name}</td>
		<td{$bg}>{$user->_email}</td>
		<td{$bg}>
			<a href="{$smarty.server.REQUEST_URI}&whmcs_user_id={$smarty.get.whmcs_user_id}&onapp_user_id={$user->_id}&server_id={$server_id}&domap">{$LANG.Map}</a>
		</td>
	</tr>
{/foreach}
</table>