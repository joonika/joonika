<?php
namespace Joonika;
use Includes\Views;
use function Joonika\Idate\tr_num;
//require "RouteConfig.php";
if(!defined('jk')) die('Access Not Allowed !');
class Route
{
    public $protocol = []; //http or https
    public $domainFull; //$protocol + $domain + '/' : example http://ipinbar.net/
    public $domain; // store website domain name from database
    public $uri;   //uri without '/' start
    public $url;    //url without query string
    public $path = [];
    public $lang;
    public $direction;
    public $subFolder = 'pages';
    public $query_string; // query string
    public $forceNotRoute = false;
    public $themeRoute = true;
    public $found = true;
    public $langLocale = "en";
    public $theme = "one";
    public $error404page = "theme";
    public $serverType = "main";
    public static $isTheme = false;

    public function __construct()
    {
        global $database;

        $this->setProtocole();

        // $domainFull = protocol + domain name + '/'
        $this->domainFull = $this->protocol . $_SERVER['HTTP_HOST'] . '/';

        // $domain= domain name + '/'
        $this->domain = $_SERVER['HTTP_HOST'] . '/';

        //get uri = uri - '/'
        $this->uri = ltrim($_SERVER['REQUEST_URI'], '/');

        //domainFull=domainFull + (uri - '/')
        $this->url = $this->domainFull . $this->uri;

        //ignore www. from url and redirect to (protocol + (domain - 'www.') + uri)
        if (JK_APP_NON_WWW) {
            if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $this->protocol . substr($_SERVER['HTTP_HOST'], 4) . $_SERVER['REQUEST_URI']);
                exit;
            }
        }

        // divided url to 2 pieces ( url & query string) exp[0]=url and exp[1]=query string
        $exp = explode('?', $this->url);

        //  parse url and get query string variable and put that in $_get array
        if (isset($exp[1])) {
            parse_str($exp[1], $this->query_string);
            global $_GET;
            foreach ($this->query_string as $g => $v) {
                $_GET[$g] = $v;
            }
        }
        if (isset($_POST)) {
            global $_POST;
            foreach ($_POST as $g => $v) {
                if(!is_array($v)){
                    $_POST[$g] = trim(tr_num($v, 'en'));
                }
            }
        }
        //remove '/' from end of uri
        if (substr($this->uri, -1) == '/') {
            header("HTTP/1.1 301 Moved Permanently");
            redirect_to(rtrim($this->url, "/"));
        }
        //redirect to url without http or https
        if ($this->protocol == "http://" && JK_APP_FORCE_SSL) {
            header("HTTP/1.1 301 Moved Permanently");
            redirect_to("https://" . ltrim($this->url, $this->protocol));
        }
        // get domain from database and put that into website variable
//        echo $this->domain;
        $website =$this->getWebsite($this->domain);
        $serverType =$this->getServerType($this->domain);

        $this->serverType = $serverType;


        /*            $database->get('jk_websites', '*', [
                    "domain" =>
                ]);*/
        if (!isset($website['domain'])) {
            Errors::errorHandler(0, "website not found", __FILE__, __LINE__);
        }

        $this->uri = $this->removeVariblesOfQueryString($this->uri);

        $path = array_values(array_filter(explode('/', $this->uri)));

        if (sizeof($path) == 0) {
            redirect_to($this->url . $website['defaultLang']);
        }

        $tempLang = $path[0];

        $getLang=$this->getlang($website['domain'],$tempLang);


        if (!isset($getLang['slug'])) {
            redirect_to($this->domainFull . $website['defaultLang'] . '/' . $this->uri);
        }
        $this->websiteID = $website['id'];
        define('JK_WEBSITE_ID',$website['id']);
        $this->lang = $getLang['slug'];
        $this->langLocale = $getLang['locale'];
        $this->direction = $getLang['direction'];


        $this->lang = $tempLang;
        $this->theme = $getLang['theme'];

        unset($tempLang);
        unset($path[0]);
        $path = array_values(array_filter($path));
        $this->path = $path;
    }


    public function findRoute($dir, $module, $path)
    {
        $found_page = false;
        if ($module != "") {
            if (is_readable($dir . $module . DS . 'router.php') && !$this->forceNotRoute) {
                $found_page = $dir . $module . DS . 'router.php';
                return $found_page;
            }
            $module .= DS . $this->subFolder . DS;
        }
        $pathsize = sizeof($path);
        for ($i = $pathsize - 1; $i >= 0; $i--) {
            $tmp1 = implode(DS, $path);
//            echo $dir .$module. $tmp1 . '.php'.'<br/>';
            $tmp2 = '';
            if ($module == '') {
                $tmp2 = $tmp1;
                $tmp1 .= '.blade';
            }
            if (is_readable($dir . $module . $tmp1 . '.php')) {
                $found_page = $dir . $module . $tmp1 . '.php';
                return $found_page;
            } elseif (is_readable($dir . $module . $tmp2 . '.php')) {
                $found_page = $dir . $module . $tmp2 . '.php';
                return $found_page;
            } else {
                $path = array_values($path);
                unset($path[sizeof($path) - 1]);
                $path = array_values($path);
            }
        }
    }


    public function dispatch()
    {
        global $View;
        $fileDIR = JK_DIR_THEMES . $this->theme . DS;
        $path_t = $this->path;
        $found_page = false;

        if (is_array($path_t) && sizeof($path_t) >= 1) {
            if ($this->themeRoute) {
                $found_page = $this->findRoute($fileDIR, '', $path_t);
            }
            if (!$found_page) {
                $mod_temp = $path_t[0];
                unset($path_t[0]);
                array_values($path_t);
                $found_page = $this->findRoute(JK_DIR_MODULES, $mod_temp, $path_t);
            } else {
                self::$isTheme = true;
            }
        } else {
            if (is_readable($fileDIR . 'index.blade.php')) {
                $found_page = $fileDIR . 'index.blade.php';
                self::$isTheme = true;
            }
        }
        if (!$found_page && isset($this->path[0])) {
            global $database;
            $data = $database->get('jk_data', '*', [
                "slug" => rawurldecode($this->path[0])
            ]);
            if ($data['id'] && is_readable(JK_DIR_THEMES . $this->theme . DS . $data['module'] . '.blade.php')) {
                $found_page = JK_DIR_THEMES . $this->theme . DS . $data['module'] . '.blade.php';
                self::$isTheme = true;
            }

        }
        if ($found_page) {
            $View->setFile($found_page);
        } else {
            $this->found = false;
            self::$isTheme = true;
            if ($this->error404page == 'theme') {
                $View->setFile($fileDIR . '404.blade.php');
            } else {
                $View->setFile($this->error404page);
            }
        }
    }

    protected function removeVariblesOfQueryString($url)
    {
        if ($url != '') {
            $parts = explode("?", $url, 2);
            $url = $parts[0];
        }
        return $url;
    }


    public function setForceNotRoute($forceNotRoute)
    {
        $this->forceNotRoute = $forceNotRoute;
    }

    private function setProtocole()
    {
        $this->protocol = $this->isHttps() ? 'https://' : 'http://';
    }

    private function isHttps()
    {
        return (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        );
    }

    public function getWebsite($domain)
    {
//        require "RouteConfig.php";
        $output=false;
        foreach (JK_WEBSITES as $jk_website) {
            if($jk_website["domain"].'/'==$domain){
                $output= $jk_website;
            }
        }
//        dd($output);
        return $output;
    }
    public function getWebsites()
    {
//        require "RouteConfig.php";
        $output=[];
        foreach (JK_WEBSITES as $jk_website) {
            $output[$jk_website['id']]=$jk_website['domain'];
        }
//        dd($output);
        return $output;
    }

    public function getlang($websiteDomain,$tempLang)
    {
//        require "RouteConfig.php";

        $output=false;
        foreach (JK_WEBSITES[$websiteDomain]['languages'] as $jk_language) {
            if ($jk_language['slug']==$tempLang && $jk_language['status']=="active") {
                $output=$jk_language;
            }
        }
        return $output;
    }
    public function getlangs($websiteDomain)
    {
//        require "RouteConfig.php";

        $output=[];
        foreach (JK_WEBSITES[rtrim($websiteDomain,'/')]['languages'] as $jk_language) {
            array_push($output,[
                "slug"=>$jk_language['slug'],
                "name"=>$jk_language['name'],
            ]);
        }
        return $output;
    }
    public function getServerType($domain){
        $output='main';
        foreach (JK_WEBSITES as $jk_website) {
            if($jk_website["domain"].'/'==$domain){
                if(isset($jk_website["type"])){
                    $output= $jk_website["type"];
                }
            }
        }
        return $output;
    }

}