<?php

namespace FindFolks;

use Arris\AppConfig;
use Arris\AppLogger;
use Arris\Helpers\Server;
use Arris\Toolkit\SphinxToolkit;
use DateTime;
use Exception;
use FindFolks\Controllers\Auth;
use Foolz\SphinxQL\Expression;
use Foolz\SphinxQL\SphinxQL;
use Psr\Log\LoggerInterface;
use FindFolks\TemplateSmarty as Template;
use Foolz\SphinxQL\Match;

class Search
{
    public const IGNORE_CHARS = array("_", "=", "!", "@", "#", '$', "%", "^", "&", "*", "(", ")", "/", "\\", "'", "\"", ">", "<", ",", ".", ":", ";", "`", "~");

    private $logger;

    private $config;

    private $is_logged = false;

    /**
     * @var string|bool
     */
    private $rt_index;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = AppLogger::scope('search');
        $this->config = AppConfig::get();

        $this->search_weights = [
            'city'     => 20,
            'district'     => 10,
            'street'      => 5,
        ];

        // rt_findfolks;
        $this->rt_index
            = $this->config['search.is_enabled'] && $this->config['search.index_type'] == 'rt' && !empty($this->config['search.indexes.folks'])
            ? $this->config['search.indexes.folks']
            : false;

        $this->is_logged = Auth::isLogged();
    }

    /**
     * @param array $dataset
     *
     * @param $id
     * @throws \Foolz\SphinxQL\Exception\ConnectionException
     * @throws \Foolz\SphinxQL\Exception\DatabaseException
     * @throws \Foolz\SphinxQL\Exception\SphinxQLException
     */
    public function updateRTIndex(array $dataset, $id = null)
    {
        $CONFIG = AppConfig::get();
        $logger = AppLogger::scope('search');

        $sphinx_target_index = $CONFIG['search.indexes.folks']; // rt_findfolks

        if ($CONFIG['search.is_enabled'] && $CONFIG['search.index_type'] == 'rt' && !empty($sphinx_target_index)) {

            $dt = (new DateTime());

            $status = SphinxToolkit::rt_ReplaceIndex($sphinx_target_index, [
                'id'            =>  $id,
                'city'          =>  $dataset['city'],
                'district'      =>  $dataset['district'],
                'street'        =>  $dataset['street'],
                'address'       =>  $dataset['address'],
                'fio'           =>  $dataset['fio'],
                'ticket'        =>  $dataset['ticket'],
                'guid'          =>  $dataset['guid'],
                'date_added'    =>  $dt->format('U')
            ]);

            $logger->info('RT-index updated: ', [ $sphinx_target_index, $id, $dataset['guid'], $status->getAffectedRows() ]);
        }
    }

    /**
     * Удаляет элемент из RT-индекса
     *
     * @param $guid
     * @param string $field
     * @return int
     *
     * @throws \Foolz\SphinxQL\Exception\ConnectionException
     * @throws \Foolz\SphinxQL\Exception\DatabaseException
     * @throws \Foolz\SphinxQL\Exception\SphinxQLException
     */
    public function deleteRT_byGUID(string $guid)
    {
        $affected_rows = 0;

        if ($this->rt_index) {
            $status = SphinxToolkit::rt_DeleteIndex($this->rt_index, 'guid', $guid);
            $affected_rows = $status->getAffectedRows();

            $this->logger->info('RT-index deleted: ', [ $this->rt_index, $guid, $affected_rows ]);
        }

        return $affected_rows;
    }

    /**
     * @throws \Foolz\SphinxQL\Exception\SphinxQLException
     * @throws \Foolz\SphinxQL\Exception\ConnectionException
     * @throws \Foolz\SphinxQL\Exception\DatabaseException
     */
    public function deleteRT_ByID($id)
    {
        $affected_rows = 0;
        if ($this->rt_index) {
            $status = SphinxToolkit::rt_DeleteIndex($this->rt_index, 'id', (int)$id);
            $affected_rows = $status->getAffectedRows();

            $this->logger->info('RT-index deleted: ', [ $this->rt_index, (int)$id, $affected_rows ]);
        }
        return $affected_rows;
    }

    /**
     * Если все поля search_fields пусты - не генерирует блок MATCH в запросе
     * Поле day интерпретируется только если мы залогинены
     *
     * @param array $search_fields <city, district, street, fio, [day] >, day в формате ГГГГММДД
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function search(array &$search_fields, int $limit = 50, int $page = 1)
    {
        $request_offset = ($limit * ($page - 1));

        try {
            if ($this->config['search.enabled'] == 0) {
                throw new Exception('Поиск временно отключён');
            }

            $query_expression = SphinxQL::expr(implode(', ', [
                'id',
                "date_added",
                "highlight({before_match='<em>', after_match='</em>', around=1}, 'city') AS city",
                "highlight({before_match='<em>', after_match='</em>', around=1}, 'district') AS district",
                "highlight({before_match='<em>', after_match='</em>', around=1}, 'street') AS street",
                "highlight({before_match='<em>', after_match='</em>', around=1}, 'fio') AS fio",
                "address",
                "ticket",
                "meta",
                "guid",
                // мантикора не умеет в функции в WHERE-секции, только чистые сравнения, то есть мы добавляем колонку `cdate_ymd` и с ней сравниваем ниже (если нужно)
                // используя этот приём, мы отказываемся от добавления лишнего поля в поисковый индекс (и кода его обработки в нескольких местах)

                "yearmonthday(date_added) AS cdate_ymd"
            ]));

            $query_dataset = SphinxToolkit::createInstance()
                ->select($query_expression)
                ->from($this->rt_index)
                ->offset($request_offset)
                ->limit($limit)
            ;

            if ($this->is_logged && !empty($search_fields['day']) && $search_fields['day'] != '*') {
                $query_dataset = $query_dataset->where('cdate_ymd', '=', (int)$search_fields['day']);
            }
            unset($search_fields['day']);

            // вызываем MATCH только если хотя бы одно из search_fields не пустое
            if (implode('', $search_fields) !== '') {

                $match_fields = ['city', 'district', 'street', 'fio'];
                // сложный билдер OR Match fields
                // см https://github.com/FoolCode/SphinxQL-Query-Builder/blob/6c1b1b44c941989e39034a7b6a3f987e938cca7a/tests/SphinxQL/MatchBuilderTest.php#L282
                // создаем экземпляр Match()
                // генерируем сложную структуру match fields
                // все это нужно для множественных HIGHLIGHT
                // см https://github.com/manticoresoftware/manticoresearch/issues/695 (см код)
                $match = (new Match($query_dataset));
                $or = false;
                if (!empty($search_fields['city'])) {
                    $match = $match->field('city')->phrase(self::escapeSearchQuery($search_fields['city']));
                    $or = true;
                }
                if (!empty($search_fields['district'])) {
                    if ($or) {
                        $match = $match->orMatch();
                    }
                    $match = $match->field('district')->phrase(self::escapeSearchQuery($search_fields['district']));
                    $or = true;
                }
                if (!empty($search_fields['street'])) {
                    if ($or) {
                        $match = $match->orMatch();
                    }
                    $match = $match->field('street')->phrase(self::escapeSearchQuery($search_fields['street']));
                    $or = true;
                }
                if (!empty($search_fields['fio'])) {
                    if ($or) {
                        $match = $match->orMatch();
                    }
                    $match = $match->field('fio')->phrase(self::escapeSearchQuery($search_fields['fio']));
                }

                $query_dataset = $query_dataset->match($match);
            }

            $result_data = $query_dataset->execute();

            // dd($query_dataset->getCompiled());

            $dataset = [];
            while ($row = $result_data->fetchAssoc()) {
                $row['cdate'] = date('H:i / d.m.Y', $row['date_added']);
                $dataset[] = $row;
            }

            $this->logger->notice("Requested search from IP, found... ", [ $search_fields, Server::getIP(), count($dataset) ]);

        } catch (Exception $e) {
            $error_message = $e->getMessage();
            $this->logger->debug('Error:', [ $error_message ]);
            $this->error_message = $error_message;

            if ((strpos($error_message, '[') !== false) && (preg_match('/(\[\d+\])/', $error_message, $range) === 1)) {
                $error_code = $range[1];
                Template::assign("error_message", "Ничего не найдено, что-то пошло не так. Код ошибки: {$error_code}");
            } else {
                Template::assign("error_message", $error_message);
            }
        }
        return $dataset;
    }

    private static function escapeSearchQuery($query)
    {
        if (empty($query)) {
            return '';
        }
        $query = urldecode($query);
        $query = addslashes($query);
        $query = trim($query);
        return str_replace(self::IGNORE_CHARS, "", $query);
    }



}