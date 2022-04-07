{if $callback_message}
    <div class="nb">{$callback_message}</div>
{/if}
Последние {$dataset_count} {$dataset_count|pluralForm:['объявление', 'объявления', 'объявлений']}: <br />
{foreach $dataset as $row}
    <fieldset>
        {$row.city}, {$row.district}, {$row.street}, {$row.address} <br/><br/>
        {$row.fio} <br/>
        {$row.ticket}
    </fieldset>
{/foreach}