<div id="tab_content">
	<table width="100%" cellspacing="2" cellpadding="3" border="0" class="form">
        <tr>
            <td colspan="4" class="fieldlabel"><b>WHMCS {$LANG.User}</b></td>
        </tr>
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

    <table cellspacing="2" cellpadding="3" border="0" width="100%" class="form">
        <tr>
            <td colspan="4" class="fieldlabel"><b>OnApp {$LANG.User}</b></td>
        </tr>
        <tr>
            <td width="25%" class="fieldlabel">{$LANG.FirstName}</td>
            <td width="25%" class="fieldarea">{$onapp_user->_first_name}</td>
            <td width="25%" class="fieldlabel">{$LANG.Email}</td>
            <td width="25%" class="fieldarea">{$onapp_user->_email}</td>
        </tr>
        <tr>
            <td class="fieldlabel">{$LANG.LastName}</td>
            <td class="fieldarea">{$onapp_user->_last_name}</td>
            <td class="fieldlabel">{$LANG.Status}</td>
            <td class="fieldarea">{$onapp_user->_status}</td>
        </tr>
    </table>
</div>

<br/>
<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
	<tr>
		{*<th>{$LANG.ID}</th>*}
		<th>{$LANG.TotalAmount}</th>
		<th>{$LANG.PaymentAmount}</th>
		<th>{$LANG.OutstandingAmount}</th>
		<th>{$LANG.MemoryAvailable}</th>
		<th>{$LANG.UsedMemory}</th>
		<th>{$LANG.DiskSpaceAvailable}</th>
		<th>{$LANG.UsedDiskSize}</th>
		<th>{$LANG.UsedCPUs}</th>
		<th>{$LANG.UsedCPUShares}</th>
	</tr>
	<tr>
		{*<td>{$onapp_user->_id}</td>*}
		<td>{$onapp_user->_total_amount}</td>
		<td>{$onapp_user->_payment_amount}</td>
        <td>{$onapp_user->_outstanding_amount}</td>
        <td>{$onapp_user->_memory_available}</td>
        <td>{$onapp_user->_used_memory}</td>
        <td>{$onapp_user->_disk_space_available}</td>
        <td>{$onapp_user->_used_disk_size}</td>
        <td>{$onapp_user->_used_cpus}</td>
        <td>{$onapp_user->_used_cpu_shares}</td>
	</tr>
    <tr>
        <td colspan="9">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="9">
            <b>{$LANG.Actions}:</b>
            <a class="unmap" href="{$smarty.server.REQUEST_URI}&onapp_user_id={$onapp_user->_id}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&unmap">{$LANG.Unmap}</a>
        </td>
    </tr>
</table>