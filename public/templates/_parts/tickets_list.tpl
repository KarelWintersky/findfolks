{*
шаблон вывода списка объявлений
{include file="_parts/tickets_list" where="<str>" data=<data>}

Аргументы:
$data - массив данных
$where - откуда вызвано
*}
{if $dataset_count > 0}
    {if $is_all_tickets_displayed}
        Найдено <strong class="color--red">{$dataset_count}</strong> {$dataset_count|pluralForm:['объявление', 'объявления', 'объявлений']}: <br /><br />
    {else}
        Всего <strong class="color--red">{$dataset_count}</strong> {$dataset_count|pluralForm:['объявление', 'объявления', 'объявлений']}, показано <strong class="color--red">{$dataset|count}</strong>: <br /><br />
    {/if}
{else}
    Объявлений пока нет
{/if}
{foreach $data as $row}
    <fieldset>
        <legend>{$row.cdate}</legend>
        <table width="100%">
            <tr>
                <td class="search-results-first-row"><strong>Город: </strong>&nbsp;&nbsp;&nbsp;</td>
                <td>{$row.city}</td>
                {if $is_logged}
                    <td rowspan="5" class="search-results-last-row">
                        <button class="action-delete-ticket" data-id="{$row.id}"><img alt="X" src="/images/icon-delete-ticket.svg" width="64" height="64"></button>
                    </td>
                {/if}
            </tr>
            <tr>
                <td><strong>Район: </strong></td>
                <td>{$row.district}</td>
            </tr>
            <tr>
                <td><strong>Адрес: </strong></td>
                <td>{$row.street} {if !empty($row.address)}, {$row.address}{/if}</td>
            </tr>
            <tr>
                <td><strong>ФИО: </strong></td>
                <td>{$row.fio}</td>
            </tr>
            <tr>
                <td></td>
                <td>{$row.ticket}</td>
            </tr>
        </table>
    </fieldset>
{/foreach}
