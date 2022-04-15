<!DOCTYPE html>
<html lang="ru-RU" itemscope prefix="og: https://ogp.me/ns#">
<head>
    <title>ИщуРодных.рф</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

{include file="_meta_favicons.tpl"}


{include file="_meta_opengraph.tpl"}

    <script src="/frontend/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="/frontend/scripts.js?v={$app_version}" type="text/javascript"></script>
    <link href="/frontend/styles.css?v={$app_version}" rel="stylesheet">
</head>
<body>
    <header>
        {include file="_header.tpl"}
    </header>
    <main>
        {include file=$inner_template}
    </main>
    <footer>
        {include file="_footer.tpl"}
    </footer>
</body>
</html>
