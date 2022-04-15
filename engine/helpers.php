<?php

use Arris\DB;
use Arris\Path;
use Dotenv\Dotenv;

/**
 * @param $data
 */
function say($data) {
    (\Arris\App::factory())->set('json', $data);
}

/**
 * Возвращает текущую метку времени строкой (если не передан таймштамп)
 *
 * @param null $ts
 * @return string
 */
function dtNow($ts = null):string {
    $format = 'Y-m-d H:i:s';
    return is_null($ts) ? date($format) : date($format, $ts);
}

/**
 * Очищает строку от спецсимволов, кавычек и прочего и приводит строку к верхнему регистру
 *
 * @param string $input
 * @return string
 */
function stringToKey(string $input):string
{
    $s = cleanString($input);
    $s = mb_strtoupper($s);
    return $s;
}

/**
 * Очищает строку от спецсимволов, кавычек и прочего.
 *
 * @param string $input
 * @return string
 */
function cleanString(string $input):string
{
    $quotes = array("&laquo;", "&raquo;", "&#187;", "&#171;", "«", "»", "'", '"', "&#039;");

    $s = trim($input);
    $s = strip_tags($s);
    $s = str_replace($quotes, '', $s);
    return $s;
}

function logSiteUsage(\Monolog\Logger $logger)
{
    $execute_time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    $logger->info('Metrics:', [
        'memory.usage'      =>  memory_get_usage(true),
        'memory.peak'       =>  memory_get_peak_usage(true),
        'time.total'        =>  number_format($execute_time, 6, '.', ''),
        'site.url'          =>  "{$_SERVER['REQUEST_METHOD']}: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"
    ]);

    return true;
}

/**
 * @param $number
 * @param $forms
 * @param string $glue
 * @return mixed|null
 */
function pluralForm($number, $forms, string $glue = '|')
{
    if (is_string($forms)) {
        $forms = explode($glue, $forms);
    } elseif (!is_array($forms)) {
        return null;
    }

    if (count($forms) != 3) {
        return null;
    }

    return $number % 10 == 1 && $number % 100 != 11 ? $forms[0] : ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20) ? $forms[1] : $forms[2]);
}

/**
 * @todo: Updated version, move to Arris\helpers
 *
 * Для команд:
 * 0 : git rev-parse --short HEAD > $(PATH_PROJECT)/_version
 * 1 : git log --oneline --format=%B -n 1 HEAD | head -n 1 >> $(PATH_PROJECT)/_version
 * 2 : git log --oneline --format="%at" -n 1 HEAD | xargs -I{} date -d @{} +%Y-%m-%d >> $(PATH_PROJECT)/_version
 *
 * @return array
 */
function getEngineVersion():array
{
    $version_file = Path::create( getenv('PATH.INSTALL') )->joinName( getenv('VERSION.FILE') )->toString();
    $version_file_present = is_readable($version_file) && is_file($version_file);

    // 0 - hash commit
    // 1 - version
    // 2 - date
    $version = [
        'date'      =>  date_format( date_create(), 'r'),
        'version'   =>  'latest',
        'hash'      =>  'local'
    ];

    if ($version_file_present) {
        $array = file($version_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $version = [
            'date'      => $array[2],
            'version'   => $array[1],
            'hash'      => $array[0]
        ];
    } elseif (getenv('VERSION')) {
        $version['summary'] = getenv('VERSION');
    }

    return $version;
}

