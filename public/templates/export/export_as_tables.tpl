<style>
    .search-results-first-row {
        width: 5rem;
    }
    table.inner-table {
        border: 1px solid black;
        margin-bottom: 10px;
    }
</style>
{if $dataset_count}
    <table>
        {foreach $dataset as $row}
            <tr>
                <td width="100%">
                    <table width="100%" class="inner-table">
                       {* <tr>
                            <td class="search-results-first-row"></td>
                            <td style="text-align: right">{$row.cdate_date}, {$row.cdate_time}</td>
                        </tr>*}
                        {if $row.city}
                            <tr>
                                <td class="search-results-first-row"><strong>Город: </strong>&nbsp;&nbsp;&nbsp;</td>
                                <td>{$row.city}</td>
                                <td style="text-align: right">{$row.cdate_date}, {$row.cdate_time}</td>
                            </tr>
                        {/if}
                        {if $row.district}
                            <tr>
                                <td><strong>Район: </strong></td>
                                <td colspan="2">{$row.district}</td>
                            </tr>
                        {/if}
                        {if $row.street or $row.address}
                            <tr>
                                <td><strong>Адрес: </strong></td>
                                <td colspan="2">{$row.street} {if !empty($row.address)}, {$row.address}{/if}</td>
                            </tr>
                        {/if}
                        {if $row.fio}
                            <tr>
                                <td><strong>ФИО: </strong></td>
                                <td colspan="2">{$row.fio}</td>
                            </tr>
                        {/if}
                        {if $row.ticket}
                            <tr>
                                <td></td>
                                <td colspan="2">{$row.ticket}</td>
                            </tr>
                        {/if}
                    </table>
                </td>
            </tr>
        {/foreach}
    </table>
{/if}
