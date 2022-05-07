<h1>Укажите пожалуйста параметры поиска</h1>
<table class="add">
    <tr>
        <td class="hint">Город</td>
        <td>
            <input type="text" name="city" value="" placeholder="Город, населенный пункт..." {if $is_production}required{/if} tabindex="1" autofocus class="action-save-hidden-data" data-target-field="city">
        </td>
    </tr>
    <tr>
        <td class="hint">
            Район
        </td>
        <td>
            <input type="text" name="district" value="" placeholder="Район..." tabindex="2" class="action-save-hidden-data" data-target-field="district">
        </td>
    </tr>
    <tr>
        <td class="hint">Улица</td>
        <td>
            <input type="text" name="street" value="" placeholder="Улица, проспект, переулок, проезд..." tabindex="3" class="action-save-hidden-data" data-target-field="street">
        </td>
    </tr>
    <tr>
        <td class="hint">
            ФИО
        </td>
        <td>
            <input type="text" name="fio" value="" placeholder="Фамилия, имя или отчество" tabindex="5" class="action-save-hidden-data" data-target-field="fio">
        </td>
    </tr>
    {if $is_logged}
        <tr>
            <td class="hint">Дата:</td>
            <td>
                <select name="day"  class="action-save-hidden-data" data-target-field="day">
                    <option value="*">Все даты</option>
                {foreach $days_available as $dk => $dv}
                    <option value="{$dk}">{$dv}</option>
                {/foreach}
                </select>
            </td>
        </tr>
    {/if}
    <tr>
        {if $is_logged}
            <td colspan="2" class="center">
                <button type="button" class="search-button button--width--25" id="actor-search" tabindex="6">Найти</button>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" class="search-button button--width--25" id="actor-export-pdf" tabindex="7">Экспортировать PDF</button>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" class="search-button button--width--25" id="actor-export-xls" tabindex="7">Экспортировать XLS</button>
            </td>
        {else}
            <td colspan="2" class="center">
                <br/>
                <button type="button" class="search-button button--width--50" id="actor-search" tabindex="6">Найти</button>
            </td>
        {/if}
    </tr>
    <input type="hidden" name="hidden_search_city" value="">
    <input type="hidden" name="hidden_search_district" value="">
    <input type="hidden" name="hidden_search_street" value="">
    <input type="hidden" name="hidden_search_fio" value="">
    <input type="hidden" name="hidden_search_day" value="">

</table>
<hr>
<div id="search_results">
</div>