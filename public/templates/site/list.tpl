{if $callback_message}
    <p class="warning">{$callback_message}</p>
{/if}
{include file="_parts/tickets_list.tpl" data=$dataset where="main"}