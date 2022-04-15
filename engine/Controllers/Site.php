<?php

namespace FindFolks\Controllers;

use Apfelbox\FileDownload\FileDownload;
use Arris\AppLogger;
use Arris\Helpers\Misc;
use DateTime;
use FindFolks\Core;
use PDO;
use Arris\App;
use Arris\AppConfig;
use Arris\Helpers\DB;

use FindFolks\Search;
use FindFolks\TemplateSmarty as Template;
use Psr\Log\LoggerInterface;
use XLSXWriter;

class Site
{
    /**
     * @var App
     */
    private $app;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var Search
     */
    private $search;

    private $is_logged = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->app = App::factory();
        $this->pdo = $this->app->get('pdo');
        $this->search = new Search();

        Template::assign("app_version", $this->app->get('app.version'));
        Template::setGlobalTemplate("index.tpl");

        $this->is_logged = Auth::isLogged();

        Template::assign("is_logged", $this->is_logged);
    }

    /**
     * VIEW главная страница, топ-10 добавленных строк
     */
    public function view_main()
    {
        if (isset($_SESSION['callback_add_message'])) {
            Template::assign("callback_message", $_SESSION['callback_add_message']);
            unset($_SESSION['callback_add_message']);
        }

        $sth = $this->pdo->query("SELECT *, DATE_FORMAT(dt_create, '%H:%i / %d.%m.%Y') AS cdate FROM tickets ORDER BY dt_create DESC LIMIT 10");
        $list = $sth->fetchAll();

        Template::assign("dataset", $list);
        Template::assign("dataset_count", count($list));

        // показать top-10 добавленных записей
        Template::assign("inner_template", "site/main.tpl");
    }

    /**
     * VIEW: полный список (возможно, с уточнением)
     */
    public function view_list()
    {
        if (isset($_REQUEST['guid']) && strlen($_REQUEST['guid']) === 36) {
            $sth = $this->pdo->prepare("SELECT *, DATE_FORMAT(dt_create, '%H:%i / %d.%m.%Y') AS cdate FROM tickets WHERE UPPER(guid) = :guid");
            $sth->execute([
                'guid'  =>  mb_strtoupper($_REQUEST['guid'])
            ]);
        } else {
            $sth = $this->pdo->query("SELECT *, DATE_FORMAT(dt_create, '%H:%i / %d.%m.%Y') AS cdate FROM tickets ORDER BY dt_create DESC");
        }

        $list = $sth->fetchAll();

        Template::assign("dataset", $list);
        Template::assign("dataset_count", count($list));

        Template::assign("inner_template", "site/main.tpl");
    }

    /**
     * VIEW: Форма добавления
     */
    public function view_add()
    {
        Template::assign("is_production", AppConfig::get()['flags.is_production']);
        Template::assign("inner_template", "site/add.tpl");
    }

    /**
     * CALLBACK: Добавление данных
     */
    public function callback_add()
    {
        $dataset = Core::prepareDataset($_REQUEST);

        $query = DB::makeInsertQuery('tickets', $dataset);
        $sth = $this->pdo->prepare($query);
        $sth->execute($dataset);

        $lid = $this->pdo->lastInsertId();

        // добавить в поисковый индекс
        $this->search->updateRTIndex($dataset, $lid);

        Template::assign("guid", $dataset['guid']);
        Template::assign("ticket_id", $lid);
        Template::assign("inner_template", "site/add_callback.tpl");

        // скомандовать редирект
        // $_SESSION['callback_add_message'] = "Ваше объявление добавлено. Оно будет рассмотрено автоматической системой модерации и добавлено в нашу базу";
        // Template::setRedirect("/");
    }

    /**
     * VIEW: страница "About"
     */
    public function view_about()
    {
        Template::assign("inner_template", "site/about.tpl");
    }

    /**
     * VIEW: страница поиска
     */
    public function view_search()
    {
        if ($this->is_logged) {
            $sth = $this->pdo->query("SELECT DISTINCT( DATE(dt_create) ) AS days FROM tickets ORDER BY days DESC");
            while ($day = $sth->fetchColumn()) {
                $days[ str_replace('-', '', $day) ] = date_format(date_create_from_format('Y-m-d', $day), 'd-m-Y');
            }
            Template::assign("days_available", $days);
        }

        Template::assign("is_production", AppConfig::get()['flags.is_production']);
        Template::assign("inner_template", "site/search.tpl");
    }

    /**
     * @throws \Exception
     */
    public function callback_ajax_search()
    {
        $this->logger = AppLogger::scope('search.desktop');

        $search_fields = [
            'city'      =>  $_REQUEST['city'] ?? '',
            'district'  =>  $_REQUEST['district'] ?? '',
            'street'    =>  $_REQUEST['street'] ?? '',
            'fio'       =>  $_REQUEST['fio'] ?? ''
        ];
        if ($this->is_logged) {
            $search_fields['day'] = $_REQUEST['day'];
        }

        $dataset = $this->search->search($search_fields);

        Template::assign("dataset", $dataset);
        Template::assign("dataset_count", count($dataset));
        Template::setGlobalTemplate("site/search_ajaxed.tpl");
    }

    /**
     * @param $guid
     * @throws \Exception
     */
    public function view_delete_ticket($guid)
    {
        Template::assign("guid", $guid);

        $sth = $this->pdo->prepare("SELECT *, DATE_FORMAT(dt_create, '%H:%i / %d.%m.%Y') AS cdate FROM tickets WHERE UPPER(guid) = :guid");
        $sth->execute([
            'guid'  =>  mb_strtoupper($guid)
        ]);
        $row = $sth->fetch();

        Template::assign("row", $row);
        Template::setGlobalTemplate("site/delete_ticket.tpl");
    }

    /**
     * @param $guid
     */
    public function callback_delete_ticket($guid)
    {
        $sth = $this->pdo->prepare("DELETE FROM tickets WHERE UPPER(guid) = :guid");
        $sth->execute([
            'guid'  =>  mb_strtoupper($guid)
        ]);

        $this->search->deleteRTIndex($guid);

        $_SESSION['callback_add_message'] = "Ваше объявление было удалено";
        Template::setRedirect("/");
    }

    /**
     * Обработчик скачивания всего файла
     */
    public function download()
    {
        $list_name = (new DateTime())->format('d-m-Y');

        $sql = "SELECT id, dt_create, DATE_FORMAT(dt_create, '%d.%m.%Y %H:%i') AS cdate, city, district, street, address, fio, ticket FROM tickets ORDER BY id ASC";
        $sth = $this->pdo->query($sql);

        $dataset = $sth->fetchAll();

        $writer = new XLSXWriter();
        if (!empty($dataset)) {
            $header = [
                'id'                    =>  'integer',
                'Дата публикации'       =>  'date',
                'Дата (польз.)'         =>  'string',
                'Город'                 =>  'string',
                'Район'                 =>  'string',
                'Улица'                 =>  'string',
                'Адрес'                 =>  'string',
                'ФИО'                   =>  'string',
                'Объявление'            =>  'string',
            ];

            $writer->writeSheetHeader($list_name, $header, $col_options = [ 'halign'=>'center', 'widths' => [ 8, 15, 15, 15, 15, 20, 15, 15, 100] ] );
            foreach ($dataset as $row) {
                $row['city'] = html_entity_decode($row['city']);
                $row['district'] = html_entity_decode($row['district']);
                $row['street'] = html_entity_decode($row['street']);
                $row['address'] = html_entity_decode($row['address']);
                $row['fio'] = html_entity_decode($row['fio']);
                $row['ticket'] = html_entity_decode($row['ticket']);
                $writer->writeSheetRow($list_name, $row, [
                    ['halign' => 'center'],
                    ['halign' => 'center'],
                    ['halign' => 'center']
                ]);
            }
        } else {
            $writer->writeSheetRow($list_name, ['Нет данных']);
        }
        $content = $writer->writeToString();


        // $fileName = "сводный_список_объявлений_[{$_REQUEST['sdate']}-{$_REQUEST['edate']}].xlsx";
        $fileName = "сводный_список_объявлений_[{$list_name}].xlsx";
        $fileDownload = FileDownload::createFromString($content);
        $fileDownload->sendDownload($fileName);
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

}
