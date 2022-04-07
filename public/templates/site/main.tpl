{if $callback_message}
    <div class="nb">{$callback_message}</div>
{/if}
{if $dataset_count > 0}
Последние {$dataset_count} {$dataset_count|pluralForm:['объявление', 'объявления', 'объявлений']}: <br /><br />
{else}
    Объявлений пока нет
{/if}
{foreach $dataset as $row}
    <fieldset>
        <legend>{$row.cdate}</legend>
        <table>
            <tr>
                <td width="*"><strong>Город: </strong>&nbsp;&nbsp;&nbsp;</td>
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
                <td colspan="2">{$row.ticket}</td>
            </tr>
        </table>

    </fieldset>
{/foreach}