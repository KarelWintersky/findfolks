<h1>Укажите пожалуйста параметры поиска</h1>
<table class="add">
    <tr>
        <td class="hint">Город</td>
        <td>
            <input type="text" name="city" value="" placeholder="Город, населенный пункт..." {if $is_production}required{/if} tabindex="1" autofocus>
        </td>
    </tr>
    <tr>
        <td class="hint">
            Район
        </td>
        <td>
            <input type="text" name="district" value="" placeholder="Район..." tabindex="2">
        </td>
    </tr>
    <tr>
        <td class="hint">Улица</td>
        <td>
            <input type="text" name="street" value="" placeholder="Улица, проспект, переулок, проезд..." tabindex="3">
        </td>
    </tr>
    <tr>
        <td class="hint">
            ФИО
        </td>
        <td>
            <input type="text" name="fio" value="" placeholder="Фамилия, имя или отчество" tabindex="5">
        </td>
    </tr>
    <tr>
        <td colspan="2" class="center">
            <br/>
            <button type="button" class="search-button" id="actor-search" tabindex="6">Искать</button>
        </td>
    </tr>
</table>
<hr>
<div id="search_results">

</div>