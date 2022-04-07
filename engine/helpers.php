<?php

use Arris\DB;
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
    $logger->notice('Metrics:', [
        'memory.usage'      =>  memory_get_usage(true),
        'memory.peak'       =>  memory_get_peak_usage(true),
        'mysql.query_count' =>  DB::$_db_requests_count,
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
