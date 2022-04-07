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
use Monolog\Logger;

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

    $CONFIG['domain'] = [
        'site'          =>  getenv('DOMAIN'),
        'fqdn'          =>  getenv('DOMAIN.FQDN')
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

    /* === AppLogger === */
    AppLogger::init('FindFolks', bin2hex(random_bytes(8)), [
        'default_logfile_path'      => dirname(__DIR__, 1) . '/logs/',
        'default_logfile_prefix'    => '/' . date_format(date_create(), 'Y-m-d') . '__'
    ] );
    AppLogger::addScope('main', [
        [ 'notices.log', Logger::NOTICE ],
        [ 'not_found.log', Logger::WARNING ],
        [ 'error.log', Logger::ERROR ]
    ]);
    AppLogger::addScope('search', [
        [ 'search.log', Logger::NOTICE ],
    ]);
    AppLogger::addScope('search.requests', [
        [ 'search_requests.log', Logger::NOTICE ],
    ]);
    AppLogger::addScope('site_usage', [
        [ 'visits.log', Logger::INFO ]
    ]);
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

    AppRouter::get('/', 'Site@view_main', 'root');
    AppRouter::get('/about', 'Site@view_about', 'about');

    AppRouter::get('/add', 'Site@view_add', 'add');
    AppRouter::post('/add', 'Site@callback_add', 'store');

    AppRouter::get('/search', 'Site@view_search', 'search');
    AppRouter::post('/ajax:search', 'Site@callback_ajax_search', 'ajax_search');

    AppRouter::get('/list', 'Site@view_list', 'list');

    /**
     * Админка / аутентификация
     */
    AppRouter::get('/admin[/]', 'Auth@view_admin_page');
    AppRouter::post('/admin', 'Auth@callback_login');
    AppRouter::get('/admin/auth:logout', 'Auth@callback_logout');
    AppRouter::post('/admin/auth:logout', 'Auth@callback_logout');

    /**
     * Админка / работа с элементами
     */
    AppRouter::get('/admin/index', 'Admin@view_index'); // главная (и единственная) страница админки - расширенный поиск по объектам
    AppRouter::get('/admin/item.add', 'Admin@form_item_add'); // форма добавления организации в админке
    AppRouter::post('/admin/item.insert', 'Admin@callback_item_insert'); // коллбэк добавления организации в админке
    AppRouter::get('/admin/item.edit/{id:\d+}', 'Admin@form_item_edit'); // форма редактирования
    AppRouter::post('/admin/item.update', 'Admin@callback_item_update'); // коллбэк обновления
    AppRouter::get('/admin/item.delete/{id:\d+}', 'Admin@callback_item_delete'); // удаление организации по ID (фото итд)
    AppRouter::get('/admin/item.toggle/{id:\d+}', 'Admin@ajax_item_toggle'); // toggle visibility


    AppRouter::dispatch();

    echo Template::render();

} catch (AppRouterException $e) {
    // (new \FindFolks\Controllers\Site())->error($e->getMessage());
    $exception_code = $e->getCode();
    $exception_message = $e->getMessage();

    if (AppLogger::scope('main') instanceof Psr\Log\LoggerInterface) {
        switch ($exception_code) {
            case 404: {
                AppLogger::scope('main')->warning('[404] Error', [ $exception_message, $exception_code ]);
                break;
            }
            case 500: {
                AppLogger::scope('main')->error('[500] Error', [ $exception_message, $exception_code ]);
                AppLogger::scope('main')->error('[500] Stacktrace', [ $e->getTrace() ]);

                http_response_code(500);
                Server::redirect('/templates/500.html', 500, true);

                break;
            }
            default: {
                AppLogger::scope('main')->alert("[{$e->getCode()}] Undefined error", [ $exception_message, $exception_code ]);
            }
        }
    }
}

logSiteUsage(AppLogger::scope('site_usage'));
