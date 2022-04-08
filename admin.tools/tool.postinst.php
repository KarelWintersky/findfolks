#!/usr/bin/php
<?php
/**
 * postinst.php
 *
 * Postinst script, генерирует /robots.txt
 */
use Arris\App;
use Arris\CLIConsole;
use Arris\Path;
use Dotenv\Dotenv;

define('PATH_ROOT', dirname(__DIR__, 1));
define('PATH_ENV', PATH_ROOT . '/config/');

$cli_options = getopt('h', ['make:robots', 'make:nginx_cache', 'link:ads', 'export:ads', 'export:adfbh', 'clear:redis', 'clear:smarty', 'clear:nginx', 'help']);
$options = [
    'help'          =>  array_key_exists('help', $cli_options),
    'starttime'     =>  microtime(true),
    
    'make:robots'   =>  array_key_exists('make:robots', $cli_options),

    'clear:smarty'   =>  array_key_exists('clear:smarty', $cli_options),
];

require_once PATH_ROOT . '/vendor/autoload.php';

try {
    Dotenv::create( PATH_ENV, 'common.conf' )->load();
    
    if (empty($cli_options) || $options['help']) {
        CLIConsole::say(<<<HOWTOUSE
Possible settings:
  <font color='yellow'>--make:robots</font>      - create robots.txt
  <font color='yellow'>--clear:smarty</font>     - clear Smarty Cache
  <font color='yellow'>--help</font>             - this help
HOWTOUSE
        );
        die (2);
    }
    

    /**
     * @var PDO $PDO
     */
    $PDO = App::factory()->get('pdo');

    /**
     * @var Path $_path_install
     */
    $_path_install = Path::create( getenv('PATH.INSTALL'));
    
    /*if ($options['clear:smarty'] && getenv('PATH.SMARTY_CACHE')) {
        $smarty_cache_directory = Path::create( getenv('PATH.SMARTY_CACHE') )->toString();
        
        if (!is_dir($smarty_cache_directory)) {
            throw new RuntimeException("[{$smarty_cache_directory}] <font color='red'> is NOT a valid cache directory/</font>");
        }
        
        $files = glob("{$smarty_cache_directory}/*");
        foreach ($files as $filename) {
            if (is_file($filename)) {
                unlink( $filename );
            }
        }
    
        CLIConsole::say("SMARTY cache cleared.");
    }*/
    
    if ($options['make:robots']) {
        $host = getenv('DOMAIN');
        $fqdn = getenv('DOMAIN.FQDN');

        $source = $_path_install->join('public/templates')->joinName('_robots.tpl')->toString();
        $target = $_path_install->join('public')->joinName('robots.txt')->toString();

        $template = file_get_contents($source);

        if (empty($template)) {
            CLIConsole::say(" <font color='red'>Error:</font> template file `{$source}` NOT FOUND. ");
            die(129);
        }

        $template = str_replace(['%%fqdn%%', '%%host%%'], [$fqdn, $host], $template);

        $f = fopen($target, 'w+');
        fwrite($f, $template);
        fclose($f);
        CLIConsole::say(" <font color='green'>{$target} file generated</font>");
    }

    /*if ($options['make:ads']) {
        $target = $_path_install->join('www')->joinName('ads.txt')->toString();
        
        if (is_file($target) || is_link($target)) {
            CLIConsole::say(" <font color='yellow'>Removing old ads.txt ...</font>  ", false);
            CLIConsole::say( @unlink($target) ? "<font color='green'>OK</font>" : "<font color='red'>ERROR</font>" );
        }
    
        CLIConsole::say(" <font color='yellow'>Exporting new {$target} file...</font>  ", false);
        
        $sth = $PDO->query("SELECT `content` FROM advert_adstxt ORDER BY id DESC LIMIT 1");
        $content = $sth->fetchColumn();
        $export_state = file_put_contents($target, (string)$content);
        
        CLIConsole::say( $export_state ? "<font color='green'>OK</font>" : "<font color='red'>ERROR</font>");
    }*/


} catch (Exception $e) {
    CLIConsole::say(" <font color='red'>{$e->getMessage()}</font>");
    die(3);
}








