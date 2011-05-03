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
            <td width="25%" class="fieldarea">{$onapp_user._first_name}</td>
            <td width="25%" class="fieldlabel">{$LANG.Email}</td>
            <td width="25%" class="fieldarea">{$onapp_user._email}</td>
        </tr>
        <tr>
            <td class="fieldlabel">{$LANG.LastName}</td>
            <td class="fieldarea">{$onapp_user._last_name}</td>
            <td class="fieldlabel">{$LANG.Status}</td>
            <td class="fieldarea">{$onapp_user._status}</td>
        </tr>
    </table>
    <br/>

    <table cellspacing="2" cellpadding="3" border="0" width="100%" class="form">
        <tr>
            <td colspan="4" class="fieldlabel"><b>{$LANG.Resources} {$LANG.Information}</b></td>
        </tr>
        <tr>
            <td width="25%" class="fieldlabel">{$LANG.MemoryAvailable}</td>
            <td width="25%" class="fieldarea">{$onapp_user._memory_available}</td>
            <td width="25%" class="fieldlabel">{$LANG.UsedMemory}</td>
            <td width="25%" class="fieldarea">{$onapp_user._used_memory}</td>
        </tr>
        <tr>
            <td class="fieldlabel">{$LANG.DiskSpaceAvailable}</td>
            <td class="fieldarea">{$onapp_user._disk_space_available}</td>
            <td class="fieldlabel">{$LANG.UsedDiskSize}</td>
            <td class="fieldarea">{$onapp_user._used_disk_size}</td>
        </tr>
        <tr>
            <td class="fieldlabel">{$LANG.UsedCPUs}</td>
            <td class="fieldarea">{$onapp_user._used_cpus}</td>
            <td class="fieldlabel">{$LANG.UsedCPUShares}</td>
            <td class="fieldarea">{$onapp_user._used_cpu_shares}</td>
        </tr>
    </table>
    <br/>

    <table cellspacing="2" cellpadding="3" border="0" width="100%" class="form">
        <tr>
            <td colspan="6" class="fieldlabel"><b>{$LANG.Billing} {$LANG.Information}</b></td>
        </tr>
        <tr>
            <td width="16%" class="fieldlabel">{$LANG.TotalAmount}</td>
            <td width="16%" class="fieldarea">{$onapp_user._total_amount}</td>
            <td width="16%" class="fieldlabel">{$LANG.PaymentAmount}</td>
            <td width="16%" class="fieldarea">{$onapp_user._payment_amount}</td>
            <td width="16%" class="fieldlabel">{$LANG.OutstandingAmount}</td>
            <td width="16%" class="fieldarea">{$onapp_user._outstanding_amount}</td>
        </tr>
    </table>

    <span class="onapp_actions">
        <p style="text-align: center;">
            {if $onapp_user._status eq 'suspended'}
                <a href="{$smarty.server.REQUEST_URI}&onapp_user_id={$onapp_user._id}&server_id={$server_id}&whmcs_user_id={$whmcs_user.id}&activate">
                    <button>{$LANG.Activate}</button>
                </a>
            {elseif $onapp_user._status eq 'active'}
                <a href="{$smarty.server.REQUEST_URI}&onapp_user_id={$onapp_user._id}&server_id={$server_id}&whmcs_user_id={$whmcs_user.id}&suspend">
                    <button>{$LANG.Suspend}</button>
                </a>
            {/if}

            <a class="unmap"
               href="{$smarty.server.REQUEST_URI}&onapp_user_id={$onapp_user._id}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&unmap">
                <button>{$LANG.Unmap}</button>
            </a>
            <a href="{$smarty.server.REQUEST_URI}&onapp_user_id={$onapp_user._id}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&syncdata">
                <button>{$LANG.Sync} {$LANG.Data}</button>
            </a>
            <a href="{$smarty.server.REQUEST_URI}&onapp_user_id={$onapp_user._id}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&syncauth">
                <button>{$LANG.Sync} {$LANG.LoginPassword}</button>
            </a>
        </p>
    </span>
</div>