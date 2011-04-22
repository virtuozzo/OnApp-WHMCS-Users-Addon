<link href="../modules/admin/onapp_users/templates/styles.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../modules/admin/onapp_users/js/handlers.js"></script>

{if $msg}
    {if $msg_ok}
        {assign var='class' value='infobox'}
    {else}
        {assign var='class' value='errorbox'}
    {/if}
    <div style="font-size: 18px;" class="{$class}">{$msg_text}</div>
{/if}

{if $map}
	{include file='map.tpl'}
{elseif $info}
	{include file='info.tpl'}
{else}
	{include file='main.tpl'}
{/if}

<p align="center">
    {if $prev}
        <a href="{$smarty.server.REQUEST_URI}&page={$prev}">« {$LANG.Previous} {$LANG.Page}</a>
    {/if}
        &nbsp;
    {if $next}
        <a href="{$smarty.server.REQUEST_URI}&page={$next}">{$LANG.Next} {$LANG.Page} »</a>
    {/if}
</p>