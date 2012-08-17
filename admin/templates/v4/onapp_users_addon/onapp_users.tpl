<link href="{$BASE_CSS}/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$BASE_JS}/handler.js"></script>

<script type="text/javascript">
	var LANG = {$LANG.JSMessages};
</script>

{if $msg_success}
    <div class="successbox">{$msg_success}</div>
{/if}    
{if $msg_info}
    <div class="infobox">{$msg_info}</div>
{/if}
{if $msg_error}
    <div class="errorbox">{$msg_error}</div>
{/if}
	
{if $map}
	{include file='map.tpl'}
{elseif $info}
	{include file='info.tpl'}
{else}
	{include file='main.tpl'}
{/if}
