{* мастер-шаблон для админки *}
<!DOCTYPE html>
<html lang="ru-RU" itemscope prefix="og: https://ogp.me/ns#">
<head>
    <title>ИщуРодных.рф -- АДМИНКА</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <script src="/frontend/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="/frontend/scripts.js?v={$app_version}" type="text/javascript"></script>
    <link href="/frontend/styles.css?v={$app_version}" rel="stylesheet">
</head>
<body>
    <header>
        <h2>Админка</h2>
        <div style="display: flex;align-items: center;justify-content: space-between;">
            <button class="large button--header" onclick="window.location.href='/add'">Добавить объявление</button>
            <button class="large button--header" onclick="window.location.href='/search'">Искать объявления</button>
            <button class="large button--header" onclick="window.location.href='/list'">Список объявлений</button>
        </div>
        <br/>
    </header>
    <main>
        <hr>
        {include file=$inner_template}
    </main>
    <footer>
        <div class="copy">&copy; 2022, ООО Психотроника</div>
        {if $is_logged}
            Залогинены
        {/if}
    </footer>
</body>
</html>
