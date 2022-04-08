<h2>Мы зарегистрировали ваше объявление.</h2>

<div style="font-size: large">
    <p>
        После автоматической проверки оно будет добавлено на сайт.
    </p>
    <p>
        Смотрите, вот две <strong>секретные</strong> ссылки:
    </p>
    <p>
        Первая позволит вам удалить ваше сообщение: <br><br>
        {*<code>{$config.domain.site}/ticket:delete/{$guid}</code>*}
        <a href="{$config.domain.site}/ticket:delete/{$guid}">
            {$config.domain.site}/ticket:delete/{$guid}
        </a>
    </p>
    <p>
        Вторая позволит вам найти его в базе:<br><br>
        <a href="{$config.domain.site}/list?guid={$guid}">
            {$config.domain.site}/list?guid={$guid}
        </a>
        {*<code>{$config.domain.site}/list?guid={$guid}</code>*}
    </p>
    <p>
        Вы можете сохранить их куда-нибудь (например, в телеграм, вацап или ВК).
    </p>
    <p>
        Потом, когда вы найдете того, кого ищете - вы сможете воспользоваться
        первой ссылкой, чтобы удалить своё объявление.
    </p>
    <p>
        Кроме того, если вы создали свое объявление повторно, чтобы исправить в предыдущем какие-то данные -
        пожалуйста, удалите предыдущее, ошибочное.
    </p>
    <p>
        Это упростит работу волонтёрам!
    </p>
    <p>
        Спасибо!
    </p>
</div>

<div class="center">
    <button class="large" onclick="window.location.href='/list'">К списку объявлений</button>
</div>
