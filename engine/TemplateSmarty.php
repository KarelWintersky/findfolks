<?php

/*
Как использовать?

== Работаем с глобальным шаблоном ==

А) через инклюд подшаблона

`{include file=$inner_template}`

// пробрасываем переменные, которые используются в banners_left.tpl
Template::assign("inner_template", "frontpage/banners_left.tpl");

Б) через ассайн переменной в подшаблоне

Template:assing("inner_content", Template::render("frontpage/banners_left.tpl");

== Работаем с локальным шаблоном ==

В) через прямой вывод шаблона в stdout

Задаем Global Template как NULL (не устанавливаем его)

echo Template::render("banners.tpl");

после этого, после диспетчера делаем Template::render() - оно не печатает НИЧЕГО

Г) Через глобальный шаблон, устанавливаем его

Template::setGlobalTemplate("banners.tpl")

после диспетчера

Template::render()

 */
namespace FindFolks;

use Arris\Core\Dot;
use Exception;
use Monolog\Logger;
use Psr\Log\NullLogger;
use Smarty;
use function Arris\setOption;

interface TemplateSmartyInterface {
    const VERSION = "3.1";

    /**
     * @param $smarty_instance
     * @param array $options
     * @param null $logger
     * @return mixed
     */
    public static function init(Smarty $smarty_instance, array $options = [], $logger = null);

}

class TemplateException extends \RuntimeException { }

class TemplateSmarty implements TemplateSmartyInterface
{
    /**
     * @var Logger
     */
    private static $logger;

    /**
     * @var
     */
    private static $JSON = null;

    /**
     * @var Smarty
     */
    private static $smarty = null;

    /**
     * @var string
     */
    private static $response = null;

    /**
     * @var string
     */
    private static $template_global_file = null;

    /**
     * @var mixed|null
     */
    private static $template_inner_file;

    /**
     * Разделитель для элементов заголовка (UNUSED)
     * @var string
     */
    private static $title_delimeter;

    /**
     * MIME-тип по умолчанию
     *
     * @var string
     */
    private static $mime_type = null;

    /**
     * @var string|null Указание на редирект
     */
    public static $redirect = null;

    /**
     * @var array key=>[ value, cached ]
     */
    public static $assigns = [];

    /**
     * @var int
     */
    private static $redirect_code;


    public static function init(Smarty $smarty_instance, array $options = [], $logger = null)
    {
        if (is_null($smarty_instance) || !($smarty_instance instanceof Smarty))
            throw new Exception("Can't initialize template with null renderer");

        self::$smarty = $smarty_instance;

        self::$template_global_file = $options[0] ?? null;

        self::$title_delimeter = " " . setOption($options, 'title_delimeter', '&#8250;') . " ";

        self::$logger
            = !is_null($logger)
            ? $logger
            : new NullLogger();
    }

    /**
     * Устанавливает имя файла главного шаблона
     *
     * @param null $template_file
     * @throws Exception
     */
    public static function setGlobalTemplate($template_file = null)
    {
        if (is_null(self::$smarty)) {
            throw new TemplateException("Template is not initialized");
        }

        if (is_null( $template_file )) {
            throw new TemplateException("Can't set empty global file template");
        }

        self::$template_global_file = $template_file;
    }

    public static function setInnerTemplate($template_file = null)
    {
        if (is_null(self::$smarty)) {
            throw new TemplateException("Template is not initialized");
        }

        self::$template_inner_file = $template_file;
        self::$assigns["inner_template"] = $template_file;
        self::$smarty->assign("inner_template", $template_file);
    }


    /**
     * @param $keys
     * @param null $value
     * @param bool $nocache
     */
    public static function assign($key, $value = null, $nocache = false)
    {
        self::$assigns[ $key ] = [ 'value' => $value, 'nocache' => $nocache ];
        self::$smarty->assign($key, $value, $nocache); //@todo: lazy assign
    }

    /**
     * Рендерит локальный или глобальный шаблон. Всегда возвращает результат, который нужно печатать.
     *
     * @param null $template_file
     * @param bool $clean
     * @return string
     * @throws Exception
     */
    public static function render($template_file = null, $clean = false)
    {
        if (is_null(self::$smarty)) {
            throw new Exception("Template is not initialized");
        }

        if (self::$redirect) {
            header("Location: " . self::$redirect, true, self::$redirect_code);
            return '';
        }

        if (!is_null(self::$JSON)) {
            header("Content-Type: application/json");
            return json_encode(self::$JSON, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        }

        // если сделаем LAZY ASSIGN - вот тут нужно будет сделать реальный assign в цикле
        if (!is_null($template_file)) {
            $return = self::$smarty->fetch( $template_file );
        } else {
            // позволяет не делать ничего если файл глобального шаблона NULL
            $return = is_null( self::$template_global_file) ? '' : self::$smarty->fetch( self::$template_global_file);
        }

        if ($clean) {
            self::$smarty->clear_all_assign();
        }

        if (self::$mime_type) {
            header("Content-Type: " . self::$mime_type);
        }

        // если заполнен массив JSON - возвращаем header JSON и json_encode этого массива
        // если нет, но стоит тип возвращаемого значения JSON - возвращаем json_encode массива ASSIGNS

        return $return;
    }


    /**
     * @TODO: DEPRECATED
     *
     * Присваивает JSON-датасет по сложным правилам
     * @todo: ПЕРЕПИСАТЬ
     *
     * @todo: если передан 1 аргумент и это асс.массив - надо передать его в конструктор Adbar\Dot
     * Если передано одно значение - его нужно передать в конструктор
     * Если передано два значения (@todo обратная несовместимость) - данные -> для ключа
     *
     * Assign JSON dataset
     * @param $key
     * @param null $value
     */
    public static function assignJSON($key = null, $value = null)
    {
        if (is_null(self::$JSON)) self::$JSON = new Dot();

        self::$JSON[ $key ] = $value;
    }

    /**
     * * @TODO: DEPRECATED
     *
     * Устанавливает в self::JSON переменную с указанным значением
     * Используется для простой (или последовательной) установки значений в JSON Dataset
     *
     * @param $key
     * @param null $value
     */
    public static function setJSON($key, $value = null)
    {
        if (is_null(self::$JSON)) self::$JSON = new Dot();
        self::$JSON[ $key ] = $value;
    }

    /**
     * * @TODO: DEPRECATED
     *
     * Возвращает JSON-датасет
     *
     * @return false|string
     */
    public static function renderJSON()
    {
        return json_encode(self::$JSON);
    }

    /**
     * * @TODO: DEPRECATED
     *
     * Устанавливает MIME-тип, который выводится в хедере Content-Type
     *
     * text/html по умолчанию, его переопределять не надо
     *
     * @param string $mime_type
     */
    public static function setContentType($mime_type = null)
    {
        self::$mime_type = $mime_type;
    }

    /**
     * Устанавливает параметры редиректа. Шаблонизатор ничего не вернет, но установит нужные заголовки для редиректа
     * Таким образом, отработают post-template-render процедуры (сбор статистики итд)
     *
     * @param $target
     * @param int $redirect_code
     */
    public static function setRedirect($target, $redirect_code = 302)
    {
        self::$redirect = $target;
        self::$redirect_code = (int)$redirect_code;
    }

}