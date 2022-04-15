<?php

namespace FindFolks;

use Arris\Helpers\Misc;
use Arris\Helpers\Server;
use Nette\Utils\Validators;

class Core
{
    /**
     * @param $REQUEST
     * @return array
     */
    public static function prepareDataset($REQUEST)
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
            'ipv4'      =>  Server::getIP(),
            'guid'      =>  Misc::GUID()
        ];
    }

    /**
     * @param $REQUEST
     * @return mixed
     */
    public static function validateDataset($REQUEST)
    {
        return $REQUEST;
    }

}