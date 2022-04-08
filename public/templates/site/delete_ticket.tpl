<style>
    button.large {
        width: 20%;
        height: 70px;
        overflow: hidden;
        font-size: large;
    }
</style>
<h1>Внимание</h1>
<p>
    Запрошено удаление объявления {$id}
    <a href="{$config.domain.site}/list?guid={$guid}">
        по секретной ссылке
    </a>
</p>
<p>
<h3>Ваше объявление:</h3>
<table border="0">
    <tr>
        <td>
            <strong>Создано:&nbsp;&nbsp;&nbsp;</strong>
        </td>
        <td>
            {$row.cdate}
        </td>
    </tr>
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
        <td colspan="2">
            <strong>Текст объявления:</strong>
        </td>
    </tr>
    <tr>
        <td colspan="2">{$row.ticket}</td>
    </tr>
</table>
</p>
<p>
    <button class="large" onclick="document.location.href='/ticket:force_delete/{$guid}'">Я подтверждаю удаление моего сообщения</button>
</p>
