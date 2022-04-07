<?php

namespace FindFolks;

use Arris\AppConfig;
use Arris\AppLogger;
use Arris\Toolkit\SphinxToolkit;
use DateTime;
use Exception;
use Foolz\SphinxQL\SphinxQL;
use Psr\Log\LoggerInterface;

class Search
{
    private ?LoggerInterface $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
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

        $sphinx_target_index = $CONFIG['search.indexes.folks']; // rt_findfolks

        if ($CONFIG['search.is_enabled'] && $CONFIG['search.index_type'] == 'rt' && !empty($sphinx_target_index)) {

            $status = SphinxToolkit::rt_ReplaceIndex($sphinx_target_index, [
                'city'          =>  $dataset['city'],
                'district'      =>  $dataset['district'],
                'street'        =>  $dataset['street'],
                'address'       =>  $dataset['address'],
                'fio'           =>  $dataset['fio'],
                'text'          =>  $dataset['ticket'],
                'date_added'    =>  (new DateTime())->format('U'),
            ]);

            AppLogger::scope('search.update_rt')->debug('RT-index updated: ', [ $sphinx_target_index, $id, $dataset['title'], $status->getAffectedRows() ]);

        } else {
            // search disabled or unavailable
        }
    }

    public function ajax_search()
    {
        $source_rt_index = getenv('SEARCH.RT_INDEX.PLACES');
        $search_query = trim($_REQUEST['query']);

        try {
            if (empty($search_query)) throw new Exception('А что ищем-то?');

            $c_data = SphinxToolkit::initConnection();
            $i_data = SphinxToolkit::getInstance($c_data);

            $i_data = $i_data
                ->select(['id', 'title', 'tags','lat', 'lon', 'address'])
                ->from($source_rt_index)
                ->limit(100)
                ->option('field_weights', $this->search_weights)
                ->orderBy("title", 'DESC')
                ->match(['title', 'tags', 'address'], SphinxQL::expr($search_query));

            $result_data = $i_data->execute();

            if (empty($result_data)) throw new Exception('Ничего не найдено');

            Template::assign("dataset", $result_data->fetchAllAssoc());
        } catch (Exception $e) {
            Template::assign("error_message", $e->getMessage() );
        }

        Template::setGlobalTemplate("frontpage/search/list.tpl"); // echo render делается после dispatch routing

        // echo Template::render("frontpage/search/list.tpl");
    }



}