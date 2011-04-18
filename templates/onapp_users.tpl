<script type="text/javascript" src="/modules/admin/onapp_users/js/handlers.js"></script>
<link href="/modules/admin/onapp_users/templates/styles.css" rel="stylesheet" type="text/css" />

<!--<div style="font-size: 18px;" class="contentbox"></div>
<br/>-->

{if $map}
	{include file='map.tpl'}
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