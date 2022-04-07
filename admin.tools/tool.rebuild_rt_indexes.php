#!/usr/bin/php
<?php

use Arris\App;
use Arris\DB;
use Arris\AppLogger;
use Arris\CLIConsole;
use Arris\Toolkit\SphinxToolkit;
use Monolog\Logger;
use Dotenv\Dotenv;

define('PATH_ROOT', dirname(__DIR__, 1));
define('PATH_ENV', PATH_ROOT . '/config/');

require_once PATH_ROOT . '/vendor/autoload.php';

ini_set('memory_limit','128M');

$cli_options = getopt('', ['limit::', 'help::', 'sleep::']);
if (key_exists('help', $cli_options)) {
    die( PHP_EOL . 'Use ' . basename(__FILE__) . ' [--limit=N] [--sleep=1]' . PHP_EOL . PHP_EOL
        . " `limit=N` key for change one chunk limits" . PHP_EOL
        . " `sleep=N` for pause (in seconds) after each chunk" . PHP_EOL . PHP_EOL);
}

$options = [
    'sql_limit'             =>  (key_exists('limit', $cli_options)) ? $cli_options['limit'] : 1000,
    'sleeptime'             =>  (key_exists('sleep', $cli_options)) ? (int)$cli_options['sleep'] : 1,
    'is_sleep'              => key_exists('sleep', $cli_options),

    'starttime'             =>  microtime(true),

    'log_file'              =>  getenv('PATH.LOGS') . 'log_rt-rebuild_' . date('Y-m-d-H-i-s') . '.log',
];

Dotenv::create(PATH_ENV, 'common.conf')->load();
$app = App::factory();
$CONFIG = new \Arris\Core\Dot();
$CONFIG['db'] = [
    'hostname'      =>  getenv('DB.HOST'),
    'database'      =>  getenv('DB.NAME'),
    'username'      =>  getenv('DB.USERNAME'),
    'password'      =>  getenv('DB.PASSWORD'),
    'port'          =>  getenv('DB.PORT'),
    'charset'           =>  'utf8mb4',
    'charset_collate'   =>  'utf8mb4_general_ci',
];
$CONFIG['search'] = [
    'hostname'      =>  getenv('SEARCH.HOST'),
    'port'          =>  getenv('SEARCH.PORT'),
    'is_enabled'    =>  getenv('SEARCH.ENABLED'),
    'enabled'       =>  getenv('SEARCH.ENABLED'),
    'index_type'    =>  getenv('SEARCH.INDEX_TYPE'),
    'indexes'       =>  [
        'folks'        =>  getenv('SEARCH.RT_INDEX_FOLKS')
    ]
];

try {
    $DATETIME_YMD = (new \DateTime())->format('Y-m-d');

    AppLogger::init('TOOL', basename(__FILE__));
    AppLogger::addScope('main', [
        [ $options['log_file'], Logger::DEBUG ]
    ]);
    AppLogger::addScope('mysql', [
        [ getenv('PATH.LOGS') . $DATETIME_YMD . 'mysql.slow.log', Logger::INFO ],
        [ getenv('PATH.LOGS') . $DATETIME_YMD . 'mysql.warning.log', Logger::WARNING ],
        [ getenv('PATH.LOGS') . $DATETIME_YMD . 'mysql.error.log', Logger::ERROR ]
    ]);

    DB::init(NULL, [
        'hostname'  =>  $CONFIG['db.hostname'],
        'database'  =>  $CONFIG['db.database'],
        'username'  =>  $CONFIG['db.username'],
        'password'  =>  $CONFIG['db.password'],
        'charset'   =>  'utf8mb4',
        'charset_collate'   =>  'utf8mb4_general_ci'
    ]);
    
    DB::init('SPHINX', [
        'hostname'  =>  $CONFIG['search.hostname'],
        'port'      =>  $CONFIG['search.port']
    ]);

    $mysql_connection = DB::getConnection();
    $sphinx_connection = DB::getConnection('SPHINX');

    $toolkit = new SphinxToolkit($mysql_connection, $sphinx_connection);
    $toolkit->setRebuildIndexOptions([
        'log_rows_inside_chunk' =>  false,
        'log_after_chunk'       =>  false,
        'sleep_after_chunk'     =>  $options['is_sleep'],
        'sleep_time'            =>  $options['sleeptime'],
        'chunk_length'          =>  $options['sql_limit']
    ]);

    /**
     *
     * Перестраиваем индексы, передавая замыкание, описывающее данные, вставляемые в индекс.
     *
     * Первый параметр: таблица с исходными данными
     * Второй параметр: название RT-индекса
     * Третий параметр: замыкание (анонимная функция)
     * Четвертый параметр: условие выборки из таблицы исходных данных
     *
     */
    $count_rebuilt['tickets'] =
    $toolkit->rebuildAbstractIndexMVA('tickets', getenv('SEARCH.RT_INDEX_FOLKS'), static function ($dataset){
        return [
            'id'            =>  $dataset['id'],
            'city'          =>  $dataset['city'],
            'district'      =>  $dataset['district'],
            'street'        =>  $dataset['street'],
            'address'       =>  $dataset['address'],
            'fio'           =>  $dataset['fio'],
            'ticket'        =>  $dataset['ticket'],
            'guid'          =>  $dataset['guid'],
            'date_added'    =>  (new DateTime())->format('U'),
        ];
    }, "");

}  catch (\Exception $e) {
    CLIConsole::say($e->getMessage());
    die;
}

echo PHP_EOL;

foreach ($count_rebuilt as $key => $cnt) {
    CLIConsole::say("Index <font color='green'>`{$key}`</font> have {$cnt} rows");
}

$report = PHP_EOL;
$report .= "Memory consumed: " . memory_get_usage() . " bytes. ". PHP_EOL;
$report .= "Time consumed: " . round(microtime(true) - $options['starttime'], 1) . " seconds. ".PHP_EOL;

echo $report;
