<?php

namespace FindFolks\Controllers;

use Apfelbox\FileDownload\FileDownload;
use Arris\App;
use DateTime;
use FindFolks\TemplateSmarty as Template;
use XLSXWriter;

class Export
{
    public function __construct()
    {
        $this->app = App::factory();
        $this->pdo = $this->app->get('pdo');
        $this->is_logged = Auth::isLogged();

        Template::assign("app_version", $this->app->get('app.version'));
        Template::assign("is_logged", $this->is_logged);
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

    public function callback_advanced_export()
    {
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML('Hello World');
        $content = $mpdf->Output('', 'S');

        $fileName = "export.pdf";
        $fileDownload = FileDownload::createFromString($content);
        $fileDownload->sendDownload($fileName);
    }


}