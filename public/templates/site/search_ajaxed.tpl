{if $dataset_count}
    {include file="_parts/tickets_list.tpl" data=$dataset where="search"}
{else}
    По вашему запросу ничего не найдено
{/if}

