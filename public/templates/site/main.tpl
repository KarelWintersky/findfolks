{if $callback_message}
    <p class="warning">{$callback_message}</p>
{/if}
{if $dataset_count > 0}
Найдено {$dataset_count} {$dataset_count|pluralForm:['объявление', 'объявления', 'объявлений']}: <br /><br />
{else}
    Объявлений пока нет
{/if}
{include file="_parts/tickets_list.tpl" data=$dataset where="main"}