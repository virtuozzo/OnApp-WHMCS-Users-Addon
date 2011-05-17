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
        <input type="hidden" value="true" name="mapfilter">
        <table cellspacing="2" cellpadding="3" border="0" width="100%" class="form">
            <tr>
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

        <div align="center">
            <input type="submit" class="button" value="{$LANG.Filter}">
            <input id="resetfilter" type="button" class="button" value="{$LANG.Reset} {$LANG.Filter}">
        </div>
    </form>
</div>

<br/>
{include file='topnav.tpl'}

<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
    <tr>
        <th>{$LANG.ID}</th>
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
                <a href="{$smarty.server.REQUEST_URI}&onapp_user_id={$user->_id}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&domap">{$LANG.Map}</a>
            </td>
        </tr>
    {/foreach}
</table>

<p align="center">
    {if $prev}
        <a href="{$smarty.server.REQUEST_URI}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&map&page={$prev}">« {$LANG.Previous} {$LANG.Page}</a>
    {/if}
        &nbsp;
    {if $next}
        <a href="{$smarty.server.REQUEST_URI}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&map&page={$next}">{$LANG.Next} {$LANG.Page} »</a>
    {/if}
</p>