<!DOCTYPE html>
<html lang="ru-RU" itemscope>
<head>
    <title>ИщуРодных.рф</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    {*{include file="_inner/meta_favicons.tpl"}*}
    {*{include file="_inner/meta_opengraph.tpl"}*}

    <script src="/frontend/jquery-3.6.0.min.js" type="text/javascript"></script>
    <link href="/frontend/styles.css" rel="stylesheet">
    <script src="/frontend/scripts.js" type="text/javascript"></script>
    <style>

    </style>
</head>
<body>
    <header>
        <button class="large" onclick="window.location.href='/add'">Add</button>
        <button class="large" onclick="window.location.href='/search'">Search</button>
        <button class="large" onclick="window.location.href='/list'">List</button>
        <button class="large" onclick="window.location.href='/about'">?</button>
    </header>
    <main>
        {include file=$inner_template}
    </main>
    <footer>

    </footer>
</body>
</html>
