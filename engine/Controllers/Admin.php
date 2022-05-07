<?php

namespace FindFolks\Controllers;

use Arris\App;
use FindFolks\Search;
use FindFolks\TemplateSmarty as Template;
use PDO;

class Admin
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
    private Search $search;

    public function __construct()
    {
        Template::setGlobalTemplate('admin.tpl');
        Template::assign("is_logged", Auth::isLogged());

        $this->app = App::factory();
        $this->pdo = $this->app->get('pdo');
        $this->search = new Search();

        Template::assign("app_version", $this->app->get('app.version'));
        Template::setGlobalTemplate("index.tpl");

        $this->is_logged = Auth::isLogged();

        Template::assign("is_logged", $this->is_logged);

        // SELECT DISTINCT(DATE(dt_create)) AS dates  FROM tickets
    }

    public function view_index()
    {
        Template::assign("inner_template", "admin/admin_index.tpl");
    }

    /**
     * @param $id
     *
     * @throws \Foolz\SphinxQL\Exception\ConnectionException
     * @throws \Foolz\SphinxQL\Exception\DatabaseException
     * @throws \Foolz\SphinxQL\Exception\SphinxQLException
     */
    public function callback_ticket_delete(int $id)
    {
        $sth = $this->pdo->prepare("DELETE FROM tickets WHERE id = :id");
        $sth->execute([
            'id'  =>  (int)$id
        ]);

        $cnt = $this->search->deleteRT_ByID((int)$id);

        Template::assignJSON("success", 1);
        Template::assignJSON("affected", $cnt);
    }

}