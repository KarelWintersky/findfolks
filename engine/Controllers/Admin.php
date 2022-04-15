<?php

namespace FindFolks\Controllers;

use FindFolks\TemplateSmarty as Template;

class Admin
{
    public function __construct()
    {
        Template::setGlobalTemplate('admin.tpl');
        Template::assign("is_logged", Auth::isLogged());

        // SELECT DISTINCT(DATE(dt_create)) AS dates  FROM tickets
    }

    public function view_index()
    {
        Template::assign("inner_template", "admin/admin_index.tpl");
    }

}