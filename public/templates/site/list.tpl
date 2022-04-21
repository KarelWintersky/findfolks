{if $callback_message}
    <p class="warning">{$callback_message}</p>
{/if}
{if $dataset_count > 0}
    {if $is_all_tickets_displayed}
        Найдено <strong class="color--red">{$dataset_count}</strong> {$dataset_count|pluralForm:['объявление', 'объявления', 'объявлений']}: <br /><br />
    {else}
        Всего <strong class="color--red">{$dataset_count}</strong> {$dataset_count|pluralForm:['объявление', 'объявления', 'объявлений']}, показано <strong class="color--red">{$dataset|count}</strong>: <br /><br />
    {/if}


{else}
    Объявлений пока нет
{/if}
{include file="_parts/tickets_list.tpl" data=$dataset where="main"}