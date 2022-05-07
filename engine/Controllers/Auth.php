<?php

namespace FindFolks\Controllers;

use Arris\AppConfig;
use Exception;
use FindFolks\TemplateSmarty as Template;

class Auth
{
    private $MAGIC_AUTH_VALUE;
    private \Arris\Core\Dot $config;

    public function __construct()
    {
        $this->config = AppConfig::get();
        $this->MAGIC_AUTH_VALUE = (string)$this->config['auth.magic'];
    }

    /**
     *
     * @throws Exception
     */
    public function view_admin_page()
    {
        $we_are_logged = self::isLogged();

        if (!$we_are_logged) {
            Template::setGlobalTemplate('auth/login.tpl');
        } else {
            Template::setRedirect('/admin/index', 401);
        }
    }

    /**
     *
     */
    public function callback_login()
    {
        if (!empty($_REQUEST)
            && $_REQUEST['auth:login'] === $this->config['auth.login']
            && $_REQUEST['auth:password'] === $this->config['auth.password']
        ) {
            $_SESSION[ $this->config['auth.key_session'] ] = $this->MAGIC_AUTH_VALUE;

            setcookie( $this->config['auth.key_cookie'], $this->MAGIC_AUTH_VALUE , time() + $this->config['auth.timeout'], '/');

            Template::setRedirect('/');
        } else {
            die('<button onclick="window.location.href=\'/admin\'">Bad credentials. Back to login form.</button> ');
        }

    }

    /**
     *
     */
    public function callback_logout()
    {
        setcookie( $this->config['auth.key_cookie'], FALSE, -1, '/');
        unset($_COOKIE[ $this->config['auth.key_cookie'] ]);
        unset($_SESSION[ $this->config['auth.key_session'] ]);

        Template::setRedirect('/');
    }

    /**
     * @return bool
     */
    public static function isLogged()
    {
        $CONFIG = AppConfig::get();

        $key_session = $CONFIG['auth.key_session'];
        $key_cookie  = $CONFIG['auth.key_cookie'];

        $we_are_logged = !empty($_SESSION);
        $we_are_logged = $we_are_logged && isset($_SESSION[ $key_session ]);
        $we_are_logged = $we_are_logged && $_SESSION[ $key_session ] !== -1;
        $we_are_logged = $we_are_logged && isset($_COOKIE[ $key_cookie ]);

        return $we_are_logged;
    }


}