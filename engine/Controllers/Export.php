<?php

namespace FindFolks\Controllers;

use Apfelbox\FileDownload\FileDownload;
use Arris\App;
use Arris\AppLogger;
use DateTime;
use FindFolks\Search;
use FindFolks\TemplateSmarty as Template;
use PDO;
use XLSXWriter;

class Export
{
    private Search $unit_search;
    private \Monolog\Logger $logger;
    private bool $is_logged;
    private App $app;

    /**
     * @var PDO
     */
    private $pdo;

    public function __construct()
    {
        $this->app = App::factory();
        $this->pdo = $this->app->get('pdo');
        $this->is_logged = Auth::isLogged();

        $this->logger = AppLogger::scope('search.desktop');

        $this->unit_search = (new Search());

        Template::assign("app_version", $this->app->get('app.version'));
        Template::assign("is_logged", $this->is_logged);

        ini_set('pcre.backtrack_limit', '10000000');
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
     * Допустить постранично
     *
     * @throws \Exception
     */
    private function prepareExportData()
    {
        $req_fields = [ 'city', 'district', 'street', 'fio', 'day' ];
        $search_fields = [];
        foreach ($req_fields as $field) {
            if (isset($_REQUEST[$field]) && !empty($_REQUEST[$field])) {
                $search_fields[ $field ] = $_REQUEST[$field];
            }
        }
        $limit = isset($_REQUEST['limit']) && !empty($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 50;

        // $count = $this->unit_search->search_count($search_fields);

        $dataset = $this->unit_search->search($search_fields, $limit, 1);

        Template::assign("dataset", $dataset);
        Template::assign("dataset_count", count($dataset));
        Template::setGlobalTemplate("export/export_as_tables.tpl");

        $html = Template::render(null, true);
        return $html;
    }

    public function callback_advanced_export()
    {
        $html = self::prepareExportData();

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($html);
        $content = $mpdf->Output('', 'S');

        $fileName = "export.pdf";
        $fileDownload = FileDownload::createFromString($content);
        $fileDownload->sendDownload($fileName);
    }

    public function callback_advanced_export_view()
    {
        $html = self::prepareExportData();

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }


}