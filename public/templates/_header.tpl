{if $is_logged}
<div class="copy copy-is-logged">
    [<a href="/admin/auth:logout">Logout</a>]
</div>
{else}
    {if $is_production}<img src="/images/photo_2020-05-27_02-36-25.jpg" alt="" width="100%">{/if}
{/if}
    <h2>Ищем родных</h2>
    <div style="display: flex;align-items: center;justify-content: space-between;">
        <button class="large button--header" onclick="window.location.href='/add'">Добавить объявление</button>
        <button class="large button--header" onclick="window.location.href='/search'">Искать объявления</button>
        <button class="large button--header" onclick="window.location.href='/list'">Список объявлений</button>
        {*<button class="large" onclick="window.location.href='/about'" style="width: 2em">?</button>*}
    </div>
    {*<p>
        <br/>
        Общественная приемная Главы ДНР: <a href="tel:+380622845045">+38 062 28 45 0 45</a> <br/><br/>
        Короткий номер для Phoenix <a href="tel:45045">45045</a>
    </p>*}
    <hr>