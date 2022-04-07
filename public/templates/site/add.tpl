<h1>Укажите пожалуйста данные для вашего объявления</h1>
{if $is_production}<h3>Обязательные поля выделены красным</h3>{/if}
<form action="/add" method="post">
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
                <input type="text" name="street" value="" placeholder="Улица, проспект, переулок, проезд..." {if $is_production}required{/if} tabindex="3">
            </td>
        </tr>
        <tr>
            <td class="hint">
                Адрес
            </td>
            <td>
                <input type="text" name="address" value="" placeholder="Номер дома, корпуса, квартиры" tabindex="4">
            </td>
        </tr>
        <tr>
            <td class="hint">
                ФИО
            </td>
            <td>
                <input type="text" name="fio" value="" placeholder="Фамилия, имя, желательно отчество" tabindex="5" {if $is_production}required{/if}>
            </td>
        </tr>
        <tr>
            <td class="hint">
                Объявление
            </td>
            <td>
                <textarea name="ticket" placeholder="Ваше объявление" rows="5" cols="60" tabindex="6" {if $is_production}required{/if}></textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="center">
                <br/>
                <input type="submit" value="Подать объявление">
            </td>
        </tr>
    </table>
</form>
<hr>