<!DOCTYPE html>
<html lang="ru-RU" itemscope>
<head>
    <title>ИщуРодных.рф</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    {include file="_meta_favicons.tpl"}
    {*{include file="_inner/meta_opengraph.tpl"}*}

    <script src="/frontend/jquery-3.6.0.min.js" type="text/javascript"></script>
    <link href="/frontend/styles.css?v={$app_version}" rel="stylesheet">
    <script src="/frontend/scripts.js?v={$app_version}" type="text/javascript"></script>
    <style>

    </style>
</head>
<body>
    <header>
        <img src="/images/photo_2020-05-27_02-36-25.jpg" alt="" width="100%">
        <h2>Ищем родных</h2>
        <div style="display: flex;align-items: center;justify-content: space-between;">
            <button class="large button--header" onclick="window.location.href='/add'">Добавить объявление</button>
            <button class="large button--header" onclick="window.location.href='/search'">Искать объявления</button>
            <button class="large button--header" onclick="window.location.href='/list'">Список объявлений</button>
            {*<button class="large" onclick="window.location.href='/about'" style="width: 2em">?</button>*}
        </div>
        <br/>
    </header>
    <main>
        <hr>
        {include file=$inner_template}
    </main>
    <footer>
        <div class="copy">&copy;ООО Психотроника</div>
    </footer>
</body>
</html>
