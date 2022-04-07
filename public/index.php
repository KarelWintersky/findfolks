<?php

$SID = session_id();
if(empty($SID)) { session_start(); }

use Arris\App;
use Arris\AppConfig;
use Arris\AppLogger;
use Arris\AppRouter;
use Arris\DB;
use Arris\Exceptions\AppRouterException;
use Arris\Helpers\Server;
use Arris\Path;
use Arris\Toolkit\SphinxToolkit;
use Dotenv\Dotenv;
use FindFolks\TemplateSmarty as Template;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

try {
    define('PATH_CONFIG', dirname(__DIR__, 1) . '/config/');
    Dotenv::create( PATH_CONFIG, 'common.conf' )->load();

    $app = App::factory();
    $app->add('config', []);
    $app->add('app.version', sha1(getEngineVersion()['date']));

    $CONFIG = new \Arris\Core\Dot();
    AppConfig::init($CONFIG);

    $_path_install = Path::create(getenv('PATH.INSTALL'));

    $CONFIG['path'] = [
        'install'   =>  $_path_install->toString(true),
    ];
    $CONFIG['path.web'] = $_path_install->join('public')->toString(true);

    $CONFIG['flags'] = [
        'is_production' =>  (bool)getenv('IS.PRODUCTION')
    ];

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

    $CONFIG['smarty'] = [
        '_'         =>  null,
        'settings'  =>  [
            'path_template'     =>  $_path_install->join('public')->join('templates')->toString(true),
            'path_cache'        =>  getenv('PATH.SMARTY_CACHE'),
            'force_compile'     =>  getenv('DEBUG.SMARTY_FORCE_COMPILE')
        ]
    ];

    AppLogger::init('FindFolks', bin2hex(random_bytes(8)), [
        'default_logfile_path'      => dirname(__DIR__, 1) . '/logs/',
        'default_logfile_prefix'    => '/' . date_format(date_create(), 'Y-m-d') . '__'
    ] );
    AppLogger::addScope('main', []);
    AppLogger::addScope('router', []);

    DB::init(NULL, $CONFIG['db'], AppLogger::scope('pdo'));
    $app->set('pdo', DB::getConnection());

    SphinxToolkit::init($CONFIG['search']['hostname'], $CONFIG['search']['port'], []);

    /* ================== init smarty and wrapper ================== */
    $SMARTY = new Smarty();
    $SMARTY->setTemplateDir( $CONFIG['smarty.settings.path_template']);
    $SMARTY->setCompileDir( $CONFIG['smarty.settings.path_cache']);
    $SMARTY->setForceCompile($CONFIG['smarty.settings.force_compile']);

    $CONFIG['smarty._'] = $SMARTY;

    Template::init($SMARTY, [ NULL ], AppLogger::scope('smarty'));

    Template::assign("config", $CONFIG->all());
    Template::assign("_request", $_REQUEST);

    $app->set(Smarty::class, $SMARTY);
    $app->set(DB::class, DB::C());

    AppRouter::init(AppLogger::addScope('router'));
    AppRouter::setDefaultNamespace('\FindFolks\Controllers');

    AppRouter::get('/', 'Main@view_main', 'root');
    AppRouter::get('/about', 'Main@view_about', 'about');

    AppRouter::get('/add', 'Main@view_add', 'add');
    AppRouter::post('/add', 'Main@callback_add', 'store');

    AppRouter::get('/search', 'Main@view_search', 'search');
    AppRouter::post('/ajax:search', 'Main@callback_ajax_search', 'ajax_search');

    AppRouter::get('/list', 'Main@view_list', 'list');

    // AppRouter::get('/admin', 'API@getStats');

    AppRouter::dispatch();

    echo Template::render();

} catch (AppRouterException $e) {
    (new \FindFolks\Controllers\Main())->error($e->getMessage());

    if (AppLogger::scope('main') instanceof \Monolog\Logger) {
        AppLogger::scope('main')->emergency('[500] Error', [ $e->getMessage(), $e->getCode() ]);
        AppLogger::scope('main')->emergency('[500] Stacktrace', [ $e->getTrace() ]);
    }

    http_response_code(500);
    Server::redirect('/templates/500.html', 500, true);
}

logSiteUsage(AppLogger::scope('site_usage'));
