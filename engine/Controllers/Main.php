<?php

namespace FindFolks\Controllers;

use Arris\AppLogger;
use PDO;
use Arris\App;
use Arris\AppConfig;
use Arris\Helpers\DB;
use Arris\Helpers\Server;
use Nette\Utils\Validators;

use FindFolks\Search;
use FindFolks\TemplateSmarty as Template;

class Main
{
    /**
     * @var App
     */
    private $app;

    /**
     * @var PDO
     */
    private $pdo;

    public function __construct()
    {
        $this->app = App::factory();
        $this->sheets = $this->app->get('sheets');
        $this->pdo = $this->app->get('pdo');

        Template::assign("app_version", $this->app->get('app.version'));
        Template::setGlobalTemplate("index.tpl");
    }

    /**
     * VIEW главная страница, топ-10 добавленных строк
     */
    public function view_main()
    {
        if ($_SESSION['callback_add_message']) {
            Template::assign("callback_message", $_SESSION['callback_add_message']);
            unset($_SESSION['callback_add_message']);
        }

        $sth = $this->pdo->query("SELECT * FROM tickets ORDER BY dt_create DESC LIMIT 10");
        $list = $sth->fetchAll();

        Template::assign("dataset", $list);
        Template::assign("dataset_count", count($list));

        // показать top-10 добавленных записей
        Template::assign("inner_template", "front/main.tpl");
    }

    /**
     * VIEW: полный список
     */
    public function view_list()
    {
        $sth = $this->pdo->query("SELECT *, DATE_FORMAT(dt_create, '%H:%i / %d.%m.%Y') AS cdate FROM tickets ORDER BY dt_create DESC");

        $list = $sth->fetchAll();

        Template::assign("dataset", $list);
        Template::assign("dataset_count", count($list));

        Template::assign("inner_template", "front/main.tpl");
    }

    /**
     * VIEW: Форма добавления
     */
    public function view_add()
    {
        Template::assign("is_production", AppConfig::get()['flags.is_production']);
        Template::assign("inner_template", "front/add.tpl");
    }

    /**
     * CALLBACK: Добавление данных
     */
    public function callback_add()
    {
        $dataset = $this->prepareDataset($_REQUEST);

        $query = DB::makeInsertQuery('tickets', $dataset);
        $sth = $this->pdo->prepare($query);
        $sth->execute($dataset);

        // добавить в поисковый индекс
        (new Search())->updateRTIndex($dataset, $this->pdo->lastInsertId());

        // скомандовать редирект
        $_SESSION['callback_add_message'] = "Ваше объявление добавлено. Оно будет рассмотрено автоматической системой модерации и добавлено в нашу базу";
        Template::setRedirect("/");
    }

    /**
     * VIEW: страница "About"
     */
    public function view_about()
    {
        Template::assign("inner_template", "front/about.tpl");
    }

    /**
     * VIEW: страница поиска
     */
    public function view_search()
    {
        Template::assign("is_production", AppConfig::get()['flags.is_production']);
        Template::assign("inner_template", "front/search.tpl");
    }

    public function callback_ajax_search()
    {
        $this->logger = AppLogger::scope('search.desktop');

        $search_fields = [
            'city'      =>  $_REQUEST['city'] ?? '',
            'district'  =>  $_REQUEST['district'] ?? '',
            'street'    =>  $_REQUEST['street'] ?? '',
            'fio'       =>  $_REQUEST['fio'] ?? ''
        ];

        // $search_query = Search::escapeSearchQuery($_REQUEST['query']);

        $dataset = (new Search())->search($search_fields);

        Template::assign("dataset", $dataset);
        Template::setGlobalTemplate("front/search_ajaxed.tpl");
    }



    /**
     * @endpoint: ERROR
     *
     * @param string $message
     * @return void
     */
    public function error($message = '')
    {
        $this->response['state'] = 'Error';
        if (!empty($message)) {
            $this->response['message'] = $message;
        }

        say($this->response);
    }

    private function prepareDataset($REQUEST)
    {
        array_walk($REQUEST, static function (&$v, $k) {
            $v = cleanString($v);
        });

        // валидация данных насчет мата и прочего. Если хоть в одном из полей присутствует - возвращаем []
        // $REQUEST = $this->validateDataset($REQUEST);

        //@todo: Nette Validation

        // если все поля пусты - возвращаем []
        return [
            'city'      =>  $REQUEST['city'] ?? '',
            'district'  =>  $REQUEST['district'] ?? '',
            'street'    =>  $REQUEST['street'] ?? '',
            'address'   =>  $REQUEST['address'] ?? '',
            'fio'       =>  $REQUEST['fio'] ?? '',
            'ticket'    =>  $REQUEST['ticket'] ?? '',
            'ipv4'      =>  Server::getIP()
        ];
    }

    /**
     * @param $REQUEST
     * @return mixed
     */
    private function validateDataset($REQUEST)
    {
        return $REQUEST;
    }

}
