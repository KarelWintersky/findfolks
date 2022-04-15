{*
шаблон вывода списка объявлений
{include file="_parts/tickets_list" where="<str>" data=<data>}

Аргументы:
$data - массив данных
$where - откуда вызвано
*}
{foreach $data as $row}
    <fieldset>
        <legend>{$row.cdate}</legend>
        <table width="100%">
            <tr>
                <td  class="search-results-first-row"><strong>Город: </strong>&nbsp;&nbsp;&nbsp;</td>
                <td>{$row.city}</td>
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
