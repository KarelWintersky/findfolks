<?php

namespace FindFolks\Controllers;

use FindFolks\TemplateSmarty as Template;

class Admin
{
    public function __construct()
    {
        Template::setGlobalTemplate('admin.tpl');
    }

    public function view_index()
    {
        Template::assign("inner_template", "admin/admin_index.tpl");
    }

}