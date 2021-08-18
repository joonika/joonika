<?php

namespace Joonika;
session_start();

use Joonika\Cookie\Cookie;
use Joonika\Middlewares\kernel;
use Joonika\Gates\Gates;
use PackageLoader\PackageLoader;

class Route
{

    public $protocol = ''; //http or https
    public $path = array();
    public $subFolder = 'Views';
    public $query_string; // query string
    public $forceNotRoute = false;
    public $found = false;
    public $themeRoute = false;
    public $args = [];
    public $modules = [];
    public $module = '';
    public $mainModule = '';
    private $routesFiles = [];
    public $requests;
    public $dispathObject;
    public $render = true;
    public $database;
    public $ViewRender = true;
    public $View = null;
    public $properties = [];
    public $User = null;
    public $sidebarMenus = [];
    public $cpDashboard = [];
    protected $siteConfig = null;
    public $isCpRoute = false;
    public $routeExecutionTime = 0;
    public $isApi = 0;
    public $jk_data = null;
    public $modulesInVendor = [];

    //------------------
    public static $instance = null;
    private static $JK_SITE_PATH;
    private static $JK_DOMAIN;
    private static $JK_DOMAIN_WOP;
    private static $JK_URI;
    private static $JK_URL;
    private static $DS = DIRECTORY_SEPARATOR;
    private static $JK_LANG_LOCALE;
    private static $JK_DIRECTION;
    private static $JK_LANG;
    private static $JK_LANGUAGES;
    private static $JK_THEME;
    private static $JK_DIRECTION_SIDE;
    private static $JK_DIRECTION_SIDE_R;
    private static $JK_DIRECTION_DASH;
    private static $JK_DIRECTION_DASH_R;
    private static $JK_DOMAIN_LANG;
    private static $JK_DIR_MODULES = "modules";
    private static $JK_DIR_THEMES = "themes";
    private static $JK_DIR_JOONIKA;
    public static $JK_LOGINID = 0;
    public static $JK_USERID = 0;
    public static $JK_TOKENID = null;
    public static $JK_TOKEN = null;
    private static $JK_DIRECTION_SIDE_S;
    private static $JK_APP_DEBUG;
    private static $JK_WEBSITE_ID;
    private static $JK_WEBSITES = [];
    private static $JK_WEBSITE;
    private static $JK_MODULES;
    private static $JK_HOST;
    public static $JK_TITLE = '';
    private static $JK_ROOT_PATH_FROM_JOONIKA = DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
    public static $env = [];

    public static function JK_SITE_PATH()
    {
        return self::$JK_SITE_PATH;
    }

    public static function JK_DOMAIN()
    {

        return self::$JK_DOMAIN;
    }

    public static function JK_DOMAIN_WOP()
    {

        return self::$JK_DOMAIN_WOP;
    }

    public static function JK_URI()
    {

        return self::$JK_URI;
    }

    public static function JK_URL()
    {
        return self::$JK_URL;
    }

    public static function DS()
    {
        return self::$DS;
    }

    public static function JK_WEBSITE_ID()
    {
        return self::$JK_WEBSITE_ID;
    }

    public static function JK_LANG_LOCALE()
    {
        return self::$JK_LANG_LOCALE;
    }

    public static function JK_DIRECTION()
    {
        return self::$JK_DIRECTION;
    }

    public static function JK_LANG()
    {
        return self::$JK_LANG;
    }

    public static function JK_LANGUAGES()
    {
        return self::$JK_LANGUAGES;
    }

    public static function JK_TITLE()
    {
        return self::$JK_TITLE;
    }


    public static function JK_THEME()
    {
        return self::$JK_THEME;
    }

    public static function JK_DIRECTION_SIDE()
    {
        return self::$JK_DIRECTION_SIDE;
    }

    public static function JK_DIRECTION_SIDE_R()
    {
        return self::$JK_DIRECTION_SIDE_R;
    }

    public static function JK_DIRECTION_SIDE_S()
    {
        return self::$JK_DIRECTION_SIDE_S;
    }

    public static function JK_DIRECTION_DASH()
    {
        return self::$JK_DIRECTION_DASH;
    }

    public static function JK_DIRECTION_DASH_R()
    {
        return self::$JK_DIRECTION_DASH_R;
    }

    public static function JK_DOMAIN_LANG()
    {
        return self::$JK_DOMAIN_LANG;
    }

    public static function JK_DIR_MODULES()
    {
        return self::$JK_DIR_MODULES;
    }

    public static function JK_DIR_THEMES()
    {
        return self::$JK_DIR_THEMES;
    }

    public static function JK_DIR_JOONIKA()
    {
        return self::$JK_DIR_JOONIKA;
    }

    public static function JK_LOGINID()
    {
        return self::$JK_LOGINID;
    }

    public static function JK_USERID()
    {
        return self::$JK_USERID;
    }

    public static function JK_TOKENID()
    {
        return self::$JK_TOKENID;
    }

    public static function JK_ROOT_PATH_FROM_JOONIKA()
    {
        return self::$JK_ROOT_PATH_FROM_JOONIKA;
    }

    public static function JK_APP_DEBUG()
    {
        return self::$JK_APP_DEBUG;
    }

    public static function JK_WEBSITES()
    {
        $websites = [];
        $scanned_directory = glob(self::JK_SITE_PATH() . 'config' . self::DS() . 'websites' . self::DS() . '*.yaml');
        if (!empty($scanned_directory)) {
            foreach ($scanned_directory as $sc) {
                $requiredYamlFile = $sc;
                if (file_exists($requiredYamlFile)) {
                    try {
                        $yamlParseFile = yaml_parse_file($requiredYamlFile);
                        if (!empty($yamlParseFile['type'])) {
                            $domain = str_replace('.yaml', '', basename($requiredYamlFile));
                            if (empty($yamlParseFile['domain'])) {
                                $yamlParseFile['domain'] = $domain;
                            }
                            $routeConfig = self::routerConfigStructure(self::JK_SITE_PATH());
                            foreach ($yamlParseFile as $rck => $rcv) {
                                if (!in_array($rck, ['languages', 'database'])) {
                                    if (isset($routeConfig[$rck])) {
                                        $routeConfig[$rck] = $rcv;
                                    }
                                }
                            }
                            if (!empty($yamlParseFile['languages'])) {
                                $routeConfig['languages'] = [];

                                foreach ($yamlParseFile['languages'] as $rck => $rcv) {
                                    $tpmLng = self::routerConfigLanguageSchema();
                                    foreach ($tpmLng as $lnk => $lnv) {
                                        if (isset($rcv[$lnk])) {
                                            $tpmLng[$lnk] = $rcv[$lnk];
                                        }
                                    }

                                    $routeConfig['languages'][$rck] = $tpmLng;
                                }
                            }

                            if (!empty($yamlParseFile['database'])) {
                                foreach ($routeConfig['database'] as $dbk => $dbv) {
                                    if (!in_array($dbk, ['other'])) {
                                        if (isset($yamlParseFile['database'][$dbk])) {
                                            $routeConfig['database'][$dbk] = $yamlParseFile['database'][$dbk];
                                        }
                                    }
                                }
                                if (isset($yamlParseFile['database']['other'])) {
                                    $routeConfig['database']['other'] = $yamlParseFile['database']['other'];
                                }
                            }

                            $websites[] = $routeConfig;
                        }
                    } catch (\Exception $exception) {
                    }
                }
            }
        }
        return $websites;
    }

    public static function JK_WEBSITE()
    {
        return self::$JK_WEBSITE;
    }

    public static function JK_MODULES()
    {
        return self::$JK_MODULES;
    }

    public static function JK_HOST()
    {
        return self::$JK_HOST;
    }

    //------------------
    public static function ROUTE($sitePath = '/../../../../', $silentType = false)
    {
        if (self::$instance == null) {
            if (isset($_SERVER['XDEBUG_CONFIG'])) {
                $silentType = true;
            }
            self::$instance = new Route($sitePath, $silentType);
        }
        return self::$instance;
    }

    public static function routerConfigStructure($sitePath)
    {
        $routeConfig = [
            "sitePath" => $sitePath,
            "domain" => 'dev',
            "id" => 1,
            "type" => 'main',
            "protocol" => "http://",
            "defaultLang" => "en",
            "debug" => false,
            "theme" => "install",
            "languages" => [
                "en" => self::routerConfigLanguageSchema()
            ],
            "database" => [
                "host" => "localhost",
                "db" => "install",
                "user" => "root",
                "pass" => "password",
                "port" => "3306",
                "charset" => "utf8",
                "driver" => "mysql",
//                "other" => self::routerConfigOtherDbSchema()
            ],
        ];
        return $routeConfig;
    }

    public static function routerConfigLanguageSchema()
    {
        $script_tz = date_default_timezone_get();
        $languageSchema = [
            "name" => "english",
            "slug" => "en",
            "direction" => "ltr",
            "locale" => "en_us",
            "tz" => $script_tz,
        ];
        return $languageSchema;
    }

    public static function routerConfigOtherDbSchema()
    {

        $otherSchema = [
            "logs" => "logs"
        ];
        return $otherSchema;
    }

    /**
     * @throws \ErrorException
     */
    public function __construct($sitePath, $silentType = false)
    {
        self::$instance = $this;
        $languageSchema = self::routerConfigLanguageSchema();
        $otherSchema = self::routerConfigOtherDbSchema();
        $routeConfig = self::routerConfigStructure($sitePath);
        $routeExecutionTimeStart = microtime(TRUE);
        set_error_handler('Joonika\Errors::errorHandler');
        set_exception_handler('Joonika\Errors::exceptionHandler');
        self::$JK_SITE_PATH = $routeConfig['sitePath'];
        $routeConfig['domain'] = $_SERVER['HTTP_HOST'] ?? $routeConfig['domain'];
        $requiredYamlFile = self::JK_SITE_PATH() . 'config/websites/dev.yaml';
        if ($routeConfig['domain'] == 'dev') {
            self::$JK_APP_DEBUG = true;
            $silentType = true;
        } else {
            $portCheck = in_array($_SERVER['SERVER_PORT'], [80, 443]) ? '' : ('_' . $_SERVER['SERVER_PORT']);
            $domainGet = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], ':'));
            $domainGet = !empty($domainGet) ? $domainGet : $_SERVER['HTTP_HOST'];
            $requiredYamlFile = self::JK_SITE_PATH() . 'config/websites/' . $domainGet . $portCheck . '.yaml';
        }
        if (file_exists($requiredYamlFile)) {
            try {
                $yamlParseFile = yaml_parse_file($requiredYamlFile);
                if (!empty($yamlParseFile['env'])) {
                    self::$env = $yamlParseFile['env'];
                }
                if (!empty($yamlParseFile['type'])) {
                    foreach ($yamlParseFile as $rck => $rcv) {
                        if (!in_array($rck, ['languages', 'database'])) {
                            if (isset($routeConfig[$rck])) {
                                $routeConfig[$rck] = $rcv;
                            }
                        }
                    }
                    if (!empty($yamlParseFile['languages'])) {
                        $routeConfig['languages'] = [];
                        foreach ($yamlParseFile['languages'] as $rck => $rcv) {
                            $tpmLng = $languageSchema;
                            foreach ($tpmLng as $lnk => $lnv) {
                                if (isset($rcv[$lnk])) {
                                    $tpmLng[$lnk] = $rcv[$lnk];
                                }
                            }
                            $routeConfig['languages'][$rck] = $tpmLng;
                        }
                    }
                    if (!empty($yamlParseFile['database'])) {
                        foreach ($routeConfig['database'] as $dbk => $dbv) {
                            if (!in_array($dbk, ['other'])) {
                                if (isset($yamlParseFile['database'][$dbk])) {
                                    $routeConfig['database'][$dbk] = $yamlParseFile['database'][$dbk];
                                }
                            }
                        }
                        if (isset($yamlParseFile['database']['other'])) {
                            $routeConfig['database']['other'] = $yamlParseFile['database']['other'];
                        }
                    } else {
                        unset($routeConfig['database']);
                    }
                }
            } catch (\Exception $exception) {
                throw new \Exception("invalid yaml file");
            }
        } elseif (!$silentType) {
            Errors::errorHandler(0, "config file has not correctly configs.", __FILE__, __LINE__);
        }

        self::$JK_WEBSITE = $routeConfig;
        self::$JK_APP_DEBUG = $routeConfig['debug'] ?? self::$JK_APP_DEBUG;
        $this->protocol = (!empty($_SERVER['HTTPS']) && in_array($_SERVER['HTTPS'], ['on', 1]) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ? 'https://' : "http://";
        $routeConfig['protocol'] = $this->protocol;
        if (self::$JK_WEBSITE) {
            self::$JK_DOMAIN = $this->protocol . $routeConfig['domain'] . '/';
            self::$JK_DOMAIN_WOP = $silentType ? '' : ($routeConfig['domain'] . '/');
        } else {
            Errors::errorHandler(0, "config file has not correctly configs.", __FILE__, __LINE__);
        }

        $this->setErrorReporting($routeConfig['debug'], E_ALL);
        self::$JK_URI = isset($_SERVER['REQUEST_URI']) ? $this->removeVariblesOfQueryString(ltrim($_SERVER['REQUEST_URI'], '/')) : null;
        self::$JK_URL = self::$JK_DOMAIN . (!empty(self::$JK_URI) ? ('/' . self::$JK_URI) : '');
        self::$JK_WEBSITE_ID = !empty(self::$JK_WEBSITE['id']) ? self::$JK_WEBSITE['id'] : null;
        self::$JK_HOST = !empty(self::$JK_WEBSITE['domain']) ? self::$JK_WEBSITE['domain'] : null;
        if (!defined("JK_SERVER_TYPE")) {
            define("JK_SERVER_TYPE", self::$JK_WEBSITE['type']);
        }

        $path = array_values(array_filter(explode('/', self::JK_URI())));
        if (!$silentType) {
            $q = $this->getQueryString(ltrim($_SERVER['REQUEST_URI'], '/'));
            $this->query_string = !empty($q) ? $q : null;
            if (empty($path)) {
                redirect_to(self::JK_URL() . self::$JK_WEBSITE['defaultLang']);
            }
        }
        if (!isset($path[0])) {
            $path[0] = self::$JK_WEBSITE['defaultLang'];
        }
        $tempLang = $path[0];

        $getLang = $this->getlang($routeConfig['domain'], $tempLang);
        if (!$silentType) {
            if (!isset($getLang['slug'])) {
                $getLang = $this->getlang($routeConfig['domain'], self::$JK_WEBSITE['defaultLang']);
//                redirect_to(self::JK_DOMAIN() . $routeConfig['defaultLang'] . '/' . self::JK_URI());
            }
        }
        unset($path[0]);
        $path = array_values(array_filter($path));
        if (!empty($getLang['tz'])) {
            @date_default_timezone_set($getLang['tz']);
        }

        if (!$silentType) {
            //remove '/' from end of uri
            $q = $this->query_string;
            $qAddAfter = !empty($q) ? ('?' . $q) : '';
            if (substr(self::$JK_URI, -1) == '/') {
                $urlGo = rtrim(self::JK_DOMAIN() . self::JK_URI(), "/");
                header("HTTP/1.1 301 Moved Permanently");
                redirect_to($urlGo . $qAddAfter);
            }
            //redirect to url without http or https
            if (!$this->isHttps() && $this->protocol == "https://") {
                $urlGo = "https://" . ltrim(self::JK_URL(), $this->protocol);
                header("HTTP/1.1 301 Moved Permanently");
                redirect_to($urlGo . $qAddAfter);
            }
        }

        try {
            if (!empty($routeConfig['database'])) {
                $dataBaseInfo = $routeConfig['database'];
                if ($silentType) {
                    $this->database = $dataBaseInfo;
                } else {
                    $this->database = Database::connect($dataBaseInfo);
                }
            }
        } catch (\Exception $exception) {
            echo "!!! please check your connection , connection to database failed !!!";
        }
        if ($this->query_string != "") {
            parse_str($this->query_string, $exp);
            global $_GET;
            foreach ($exp as $g => $v) {
//                  $_GET[$g] = trim(Idate::tr_num($v, 'en'));
                $_GET[$g] = $v;
            }
        }
        if (!$silentType) {
            $this->requests = new Request();
        }
        $loader = new \Joonika\PackageLoader();
        $foundedModules = [];

        if (FS::isDir(self::JK_SITE_PATH() . self::DS() . 'modules')) {
            $scanned_directory = array_diff(scandir(self::JK_SITE_PATH() . self::DS() . 'modules' . self::DS()), array('..', '.'));
            if (!empty($scanned_directory)) {
                foreach ($scanned_directory as $elem) {
                    if (is_dir(self::JK_SITE_PATH() . 'modules' . self::DS() . $elem)) {
                        array_push($foundedModules, $elem);
                    }
                }
            }
        }

        $modulesInVendor = glob(self::JK_SITE_PATH() . 'vendor/joonika/module-*');
        if (checkArraySize($modulesInVendor)) {
            foreach ($modulesInVendor as $moduleInVendor) {
                $moduleName = explode('-', basename($moduleInVendor));
                if (sizeof($moduleName) == 2) {
                    $moduleCheckName = $moduleName[1];
                    $this->modulesInVendor[] = $moduleCheckName;
                    if (!in_array($moduleCheckName, $foundedModules)) {
                        array_push($foundedModules, $moduleCheckName);
                        $autoload['Modules\\' . $moduleCheckName . '\\'][] = 'src/';
                        $loader->loadPSRDir($moduleInVendor, $autoload, true);
                    } else {
                        if (self::JK_APP_DEBUG()) {
                            die("vendor " . $moduleCheckName . ' is duplicate');
                        }
                    }
                }
            }
        }

        self::$JK_MODULES = $this->modules = $foundedModules;
        //Gates
        if (!empty($this->modules)) {
            foreach ($this->modules as $singleModule) {
                $singleModule = "Modules\\" . $singleModule . "\Providers\Gate";
                $singleModuleMethod = "registerGate";
                if (class_exists($singleModule) && method_exists($singleModule, $singleModuleMethod)) {
                    $singleModule::$singleModuleMethod();
                }
            }
        }

        $autoload['autoload']['psr-4'] = [];
        if (!empty($foundedModules)) {
            foreach ($foundedModules as $foundedModule) {
                if (FS::isExistIsFileIsReadable(self::JK_SITE_PATH() . 'modules' . self::DS() . $foundedModule . self::DS() . 'functions.php')) {
                    include self::JK_SITE_PATH() . 'modules' . self::DS() . $foundedModule . self::DS() . 'functions.php';
                }
                $autoload['Joonika\\Modules\\' . ucfirst($foundedModule) . '\\'][] = $foundedModule . '/src/';
            }
            $loader->loadPSRDir(self::JK_SITE_PATH() . 'modules' . self::DS(), $autoload, true);
        }


        self::$JK_LANG_LOCALE = $getLang['locale'];
        self::$JK_DIRECTION = $getLang['direction'];
        self::$JK_LANG = $getLang['slug'];
        if (!empty($routeConfig['languages'])) {
            self::$JK_LANGUAGES = $routeConfig['languages'];
        }
        self::$JK_THEME = self::$JK_WEBSITE['theme'] ?? null;


        if (isset($path[0]) && $path[0] != 'api') {
            $this->module = $path[0];
        } elseif (isset($path[1])) {
            if ($path[0] == 'api') {
                $this->isApi = 1;
                unset($path[0]);
                $path = array_values(array_filter($path));
                $this->module = $path[0];
            } else {
                $this->module = $path[1];
            }
        }
        $this->path = $path;
        if ($this->mainModule == '' && isset($path[0]) && in_array($path[0], $this->modules)) {
            $this->mainModule = $path[0];
        }

        if (self::JK_DIRECTION() == 'ltr') {
            self::$JK_DIRECTION_SIDE = "left";
            self::$JK_DIRECTION_SIDE_R = "right";
            self::$JK_DIRECTION_SIDE_S = "l";
        } else {
            self::$JK_DIRECTION_SIDE = "right";
            self::$JK_DIRECTION_SIDE_R = "left";
            self::$JK_DIRECTION_SIDE_S = "r";
        }
        self::$JK_DIRECTION_DASH = self::JK_DIRECTION() . '-';
        self::$JK_DIRECTION_DASH_R = '-' . self::JK_DIRECTION();

        self::$JK_DOMAIN_LANG = self::JK_DOMAIN() . self::JK_LANG() . '/';
        self::$JK_DIR_MODULES = self::JK_SITE_PATH() . 'modules' . self::DS();
        self::$JK_DIR_THEMES = self::JK_SITE_PATH() . 'themes' . self::DS();
        self::$JK_DIR_JOONIKA = self::JK_SITE_PATH() . 'vendor' . self::DS() . 'joonika' . self::DS() . 'joonika' . self::DS() . 'src' . self::DS();
        $systemHasUsers = in_array('users', listModules());
        if (!$silentType) {
            global $translate;
            $translate = [];
            $userToken = false;
            if (!is_null($this->database) && $systemHasUsers) {
                Translate::TR();
                $expiredTokenAfter = !empty(self::$JK_WEBSITE['expiredTokenAfter']) ? self::$JK_WEBSITE['expiredTokenAfter'] : 172800;
                $token = $this->requests->headers('token') ?? null;
//                session_destroy();
//                unset($_COOKIE);
                if (!empty($token)) {
                    $hastDupToken = $this->database->get('jk_users_tokens', ['id', 'userID', 'token'], [
                        "AND" => [
                            "token" => $token,
                            "expired[>=]" => date("Y/m/d H:i:s"),
                            "status" => 'active',
                        ]
                    ]);
                    if (!empty($hastDupToken['id'])) {
                        self::$JK_TOKENID = $hastDupToken['id'];
                        self::$JK_LOGINID = $hastDupToken['userID'];
                        self::$JK_USERID = self::$JK_LOGINID;
                        self::$JK_TOKEN = $token;
                    }
                } elseif (!empty($_SESSION[self::$JK_DOMAIN_WOP]['userID'])) {
                    if (empty($_SESSION[self::$JK_DOMAIN_WOP]['token'])) {
                        $existUser = $this->database->get('jk_users', ['id', 'mobile'], ['id' => $_SESSION[self::$JK_DOMAIN_WOP]['userID']]);
                        if ($existUser) {
                            $userToken = Token::tokenGenerate($existUser['id'], $existUser['mobile'], 'session', false, null, null, $expiredTokenAfter);
                            if ($userToken['status'] == 200) {
                                self::$JK_LOGINID = $existUser['id'];
                                self::$JK_USERID = self::$JK_LOGINID;
                                self::$JK_TOKENID = $userToken['id'];
                                self::$JK_TOKEN = $userToken['token'];
                                $_SESSION[self::$JK_DOMAIN_WOP]['token'] = $userToken['token'];
                                $_SESSION[self::$JK_DOMAIN_WOP]['userID'] = $existUser['id'];
                            }
                        }
                    } else {
                        $userToken = $this->database->get('jk_users_tokens', ['id', 'userID', 'status'], [
                            "token" => $_SESSION[self::$JK_DOMAIN_WOP]['token'],
                            "status" => "active"
                        ]);
                        if (!empty($userToken['id'])) {
                            self::$JK_TOKENID = $userToken['id'];
                            self::$JK_TOKEN = $_SESSION[self::$JK_DOMAIN_WOP]['token'];
                            self::$JK_LOGINID = $_SESSION[self::$JK_DOMAIN_WOP]['userID'];
                            self::$JK_USERID = self::$JK_LOGINID;
                        } else {
                            unset($_SESSION[self::$JK_DOMAIN_WOP]['token']);
                            unset($_SESSION[self::$JK_DOMAIN_WOP]['userID']);
                        }
                    }
                } elseif (!empty($_COOKIE['loginCredential'])) {
                    $hastDupToken = $this->database->get('jk_users_tokens', ['id', 'userID'], [
                        "AND" => [
                            "token" => $_COOKIE['loginCredential'],
                            "expired[>=]" => date("Y/m/d H:i:s"),
                            "status" => 'active',
//                            "userAgent" => "",  // TODO check userAgent

                        ]
                    ]);
                    if (!empty($hastDupToken['id'])) {
                        self::$JK_TOKENID = $hastDupToken['id'];
                        self::$JK_LOGINID = $hastDupToken['userID'];
                        self::$JK_USERID = self::$JK_LOGINID;
                        self::$JK_TOKEN = $_COOKIE['loginCredential'];
                        $_SESSION[self::$JK_DOMAIN_WOP]['token'] = $_COOKIE['loginCredential'];
                        $_SESSION[self::$JK_DOMAIN_WOP]['userID'] = $hastDupToken['userID'];
                    }
                }
                if (!empty(self::$JK_TOKENID)) {
                    $timeExpire = time() + $expiredTokenAfter;
                    $updToken['expired'] = date("Y/m/d H:i:s", $timeExpire);

                    $updToken['last_try'] = date("Y/m/d H:i:s");
                    $this->database->update('jk_users_tokens', $updToken, [
                        "id" => self::$JK_TOKENID,
                    ]);

                    if (!$this->isApi && (empty($_COOKIE['loginCredential']) || $_COOKIE['loginCredential'] != self::$JK_TOKEN)) {
                        setcookie("loginCredential", self::$JK_TOKEN, $timeExpire, '/', trim(JK_DOMAIN_WOP(), '/'), false, true);
                    }
                }
            }
//            jdie($_COOKIE);
            $middleWare = new kernel(self::JK_LOGINID(), $this);
            $middleWare->dispatch();

            $this->View = new \Joonika\View($this);

            if ($this->mainModule != "" && class_exists('Modules\\' . $this->mainModule . '\\Router')) {
                $classExistCheck = 'Modules\\' . $this->mainModule . '\\Router';
                new $classExistCheck($this);
            }

            if ($systemHasUsers && !is_null($this->database)) {
                $this->User = \Joonika\Modules\Users\Users::getUser(self::JK_LOGINID(), $this);
            }

            new AutomaticRouter($this);
            self::sendRequestToController();

            if (self::$JK_THEME) {
                if (FS::isExistIsFileIsReadable(self::JK_SITE_PATH() . 'themes' . self::DS() . self::JK_THEME() . self::DS() . 'inc' . self::DS() . "foot.php")) {
                    $this->View->foot_file = self::JK_SITE_PATH() . 'themes' . self::DS() . self::JK_THEME() . self::DS() . 'inc' . self::DS() . "foot.php";
                }
                if (FS::isExistIsFileIsReadable(self::JK_SITE_PATH() . 'themes' . self::DS() . self::JK_THEME() . self::DS() . 'inc' . self::DS() . "head.php")) {
                    $this->View->head_file = self::JK_SITE_PATH() . 'themes' . self::DS() . self::JK_THEME() . self::DS() . 'inc' . self::DS() . "head.php";
                }
            }
        }

        $routeExecutionTimeFinish = microtime(TRUE);
        $this->routeExecutionTime = $routeExecutionTimeFinish - $routeExecutionTimeStart;
        $checkDbDuration = 0;
        if (!empty(Database::$instanceDuration)) {
            foreach (Database::$instanceDuration as $schema) {
                if (!empty($schema)) {
                    foreach ($schema as $s) {
                        @$checkDbDuration += $s;
                    }
                }
            }
        }
        @header('DatabaseExecutionTime: ' . $checkDbDuration);
        @header('RouteExecutionTime: ' . $this->routeExecutionTime);
    }

    private function setErrorReporting($debug, $level)
    {
        self::$JK_APP_DEBUG = $debug;
        if (self::JK_APP_DEBUG()) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting($level);
        }
    }

    private function loadThemeControllers()
    {
        $controllers = glob(self::JK_SITE_PATH() . 'themes' . self::DS() . self::JK_THEME() . self::DS() . 'Controllers' . self::DS() . '*.php');
        foreach ($controllers as $controller) {
            $nameSpace = "Theme\\" . self::JK_THEME() . "\Controllers\\" . basename($controller, '.php');
            require_once realpath($controller);
            $nameSpace::view($this);
        }
    }

    private function searchForRouters($path)
    {
        $routesDir = scandir($path);
        foreach ($routesDir as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            } else {
                $this->routesFiles[] = $path . self::DS() . $item;
            }
        }
    }

    private function callController($dispathObject)
    {
        global $Route;
        $this->searchForRouters(__DIR__ . self::JK_ROOT_PATH_FROM_JOONIKA() . "routes");
        foreach ($Route->modules as $module) {
            $this->searchForRouters(__DIR__ . self::JK_ROOT_PATH_FROM_JOONIKA() . "modules" . self::DS() . $module . self::DS() . 'routes');
        }
        foreach ($this->routesFiles as $route) {
            require_once $route;
        }

        \Joonika\Router::dispatch($_SERVER["REQUEST_URI"], $_SERVER['REQUEST_METHOD'], $dispathObject);
    }

    private function hasThemeController()
    {
        $currentTheme = self::JK_THEME();
        $themeController = "Themes\\" . $currentTheme . "\Controllers\\" . $currentTheme;
        if (class_exists($themeController)) {
            return true;
        } else {
            return false;
        }
    }

    private function sendRequestToThemeController()
    {
        $currentTheme = self::JK_THEME();
        $this->tempPath = $this->path;
        $this->checkSubControllers($currentTheme);
        if ($this->findController) {
            $this->path['method'] = $this->findMethod;
            $this->View->directory = "./themes/" . lcfirst($currentTheme) . "/Views/";
            new $this->controllerClass($this, $this->sc);
        } else {
            $this->controllerClass = "Themes\\{$currentTheme}\\Controllers\\{$currentTheme}";
            $this->View->directory = "./themes/" . lcfirst($currentTheme) . "/Views/";
            if (isset($this->path[0])) {
                $this->path['method'] = $this->path[0];
            } else {
                $this->path['method'] = "index";
            }
            if (class_exists($this->controllerClass)) {
                new $this->controllerClass($this, $this->sc);
            } else {
                new \Joonika\Controller($this);
            }
        }
    }

    private $tempPath = null;
    private $findModule = null;
    private $findMethod = null;
    private $findController = false;
    private $controllerClass = null;
    private $controllerClassF1 = null;
    private $controllerClassF2 = null;
    private $controllerClassF3 = null;
    private $sc = false;

    private function checkSubControllers($theme = null)
    {
        $pathForFind = $this->tempPath;
        unset($pathForFind[0]);
        $pathSize = sizeof($pathForFind);
        if ($pathSize >= 1) {
            $find = implode('\\', $pathForFind);
            if ($theme) {
                $this->controllerClass = "Themes\\" . $theme . "\Controllers\\" . $find;
            } else {
                $this->controllerClass = "\\Modules\\" . $this->findModule . "\Controllers\\" . $find;
            }
            if (class_exists($this->controllerClass)) {
                $t = $this->path;
                unset($t[0]);
                $pathExpls = implode('\\', $t);
                $findMethod = substr($pathExpls, strlen($find) + 1);
                $pos = strpos($findMethod, '\\');
                if (!empty($pos)) {
                    $findMethod = substr($findMethod, 0, $pos);
                }
                $this->findMethod = $findMethod;
                $this->sc = true;
                unset($this->tempPath[1]);
                $this->findController = true;
            } else {
                unset($this->tempPath[sizeof($this->tempPath) - 1]);
                $this->tempPath = array_values($this->tempPath);
                $this->checkSubControllers($theme);
            }
        } elseif (sizeof($this->path) >= 2) {
            $find = $this->path[0];
            if ($theme) {
                $this->controllerClass = "Themes\\" . $theme . "\Controllers\\" . $find;
            } else {
                $this->controllerClass = "\\Modules\\" . $this->findModule . "\Controllers\\" . $find;
            }
            if (class_exists($this->controllerClass)) {
                $this->findMethod = $this->path[1];
                $this->sc = true;
                $this->findController = true;
            }
        }
//        jdie($pathForFind);
//        $pathForFind = array_values($pathForFind);
//        $outMethod = $pathForFind;
//        array_pop($pathForFind);
//        $pathSize = sizeof($pathForFind);
//        if ($pathSize >= 0) {
//            $find1 = implode('\\', $pathForFind);
//            $find2 = !empty($this->tempPath[sizeof($this->tempPath) - 1])?$this->tempPath[sizeof($this->tempPath) - 1]:'';
//            $find3 = !empty($this->tempPath[0])?$this->tempPath[0]:'';
//            if ($theme) {
//                $this->controllerClassF1 = "Themes\\" . $theme . "\Controllers\\" . $find1;
//                $telsehis->controllerClassF2 = "Themes\\" . $theme . "\Controllers\\" . $find2;
//                $this->controllerClassF3 = "Themes\\" . $theme . "\Controllers\\" . $find3;
//            } else {
//                $this->controllerClassF1 = "\\Modules\\" . $this->findModule . "\Controllers\\" . $find1;
//                $this->controllerClassF2 = "\\Modules\\" . $this->findModule . "\Controllers\\" . $find2;
//                $this->controllerClassF3 = "\\Modules\\" . $this->findModule . "\Controllers\\" . $find3;
//            }
////            jdie($this->controllerClassF2,0);
//            if (class_exists($this->controllerClassF1)) {
//                $this->findMethod = $outMethod[sizeof($outMethod) - 1];
//                $this->sc = true;
//                unset($this->tempPath[1]);
//                $this->findController = true;
//                $this->controllerClass = $this->controllerClassF1;
//            } elseif (class_exists($this->controllerClassF2)) {
//                $this->findMethod = $outMethod[sizeof($outMethod) - 1];
//                $this->sc = true;
//                if (!empty($this->tempPath[1])) {
//                    unset($this->tempPath[1]);
//                }
//                $this->findController = true;
//                $this->controllerClass = $this->controllerClassF2;
//            } elseif (class_exists($this->controllerClassF3)) {
//                $this->findMethod = $this->tempPath[1];
//                $this->sc = true;
//                unset($this->tempPath[1]);
//                $this->findController = true;
//                $this->controllerClass = $this->controllerClassF3;
//            } else {
//                if (!empty($outMethod[sizeof($outMethod) - 1])) {
//                    $this->findMethod = $outMethod[sizeof($outMethod) - 1];
//                }
//                unset($this->tempPath[sizeof($this->tempPath) - 1]);
//                $this->tempPath = array_values($this->tempPath);
//                $this->checkSubControllers($theme);
//            }
//        }
//        if ($pathSize >= 2) {
//            $controllerName = $this->tempPath[$pathSize - 2];
//
//            if ($theme) {
//                $this->controllerClass = "Themes\\" . $theme . "\Controllers\\" . $controllerName;
//            } else {
//                $this->controllerClass = "Modules\\" . $this->findModule . "\Controllers\\" . $controllerName;
//            }
//            if (class_exists($this->controllerClass)) {
//                $this->findMethod = $this->tempPath[$pathSize - 1];
//                $this->sc = true;
//                unset($this->tempPath[1]);
//                $this->findController = true;
//            } else {
//                $this->findMethod = $this->tempPath[$pathSize - 1];
//                unset($this->tempPath[$pathSize - 1]);
//                $this->tempPath = array_values($this->tempPath);
//                $this->checkSubControllers($theme);
//            }
//        }
    }

    private function checkSubControllers2($theme = null)
    {
        $pathForFind = $this->tempPath;
        unset($pathForFind[0]);
        $pathForFind = array_values($pathForFind);
        $outMethod = $pathForFind;
        array_pop($pathForFind);
        $pathSize = sizeof($pathForFind);
        if ($pathSize >= 0) {
            $find1 = implode('\\', $pathForFind);
            $find2 = !empty($this->tempPath[sizeof($this->tempPath) - 1]) ? $this->tempPath[sizeof($this->tempPath) - 1] : '';
            $find3 = !empty($this->tempPath[0]) ? $this->tempPath[0] : '';
            if ($theme) {
                $this->controllerClassF1 = "Themes\\" . $theme . "\Controllers\\" . $find1;
                $this->controllerClassF2 = "Themes\\" . $theme . "\Controllers\\" . $find2;
                $this->controllerClassF3 = "Themes\\" . $theme . "\Controllers\\" . $find3;
            } else {
                $this->controllerClassF1 = "\\Modules\\" . $this->findModule . "\Controllers\\" . $find1;
                $this->controllerClassF2 = "\\Modules\\" . $this->findModule . "\Controllers\\" . $find2;
                $this->controllerClassF3 = "\\Modules\\" . $this->findModule . "\Controllers\\" . $find3;
            }
//            jdie($this->controllerClassF2,0);
            if (class_exists($this->controllerClassF1)) {
                $this->findMethod = $outMethod[sizeof($outMethod) - 1];
                $this->sc = true;
                unset($this->tempPath[1]);
                $this->findController = true;
                $this->controllerClass = $this->controllerClassF1;
            } elseif (class_exists($this->controllerClassF2)) {
                $this->findMethod = $outMethod[sizeof($outMethod) - 1];
                $this->sc = true;
                if (!empty($this->tempPath[1])) {
                    unset($this->tempPath[1]);
                }
                $this->findController = true;
                $this->controllerClass = $this->controllerClassF2;
            } elseif (class_exists($this->controllerClassF3)) {
                $this->findMethod = $this->tempPath[1];
                $this->sc = true;
                unset($this->tempPath[1]);
                $this->findController = true;
                $this->controllerClass = $this->controllerClassF3;
            } else {
                if (!empty($outMethod[sizeof($outMethod) - 1])) {
                    $this->findMethod = $outMethod[sizeof($outMethod) - 1];
                }
                unset($this->tempPath[sizeof($this->tempPath) - 1]);
                $this->tempPath = array_values($this->tempPath);
                $this->checkSubControllers($theme);
            }
        }
//        if ($pathSize >= 2) {
//            $controllerName = $this->tempPath[$pathSize - 2];
//
//            if ($theme) {
//                $this->controllerClass = "Themes\\" . $theme . "\Controllers\\" . $controllerName;
//            } else {
//                $this->controllerClass = "Modules\\" . $this->findModule . "\Controllers\\" . $controllerName;
//            }
//            if (class_exists($this->controllerClass)) {
//                $this->findMethod = $this->tempPath[$pathSize - 1];
//                $this->sc = true;
//                unset($this->tempPath[1]);
//                $this->findController = true;
//            } else {
//                $this->findMethod = $this->tempPath[$pathSize - 1];
//                unset($this->tempPath[$pathSize - 1]);
//                $this->tempPath = array_values($this->tempPath);
//                $this->checkSubControllers($theme);
//            }
//        }
    }

    private function goToController()
    {
        $this->tempPath = $this->path;
        $this->findModule = isset($this->path[0]) ? $this->path[0] : null;
        if (!is_null($this->findModule) && in_array($this->module, $this->modules)) {
            $this->checkSubControllers();
            if ($this->findController) {
                $this->path['method'] = $this->findMethod;
                $this->View->directory = "./modules/" . lcfirst($this->findModule) . "/Views/";
                new $this->controllerClass($this, $this->sc);

            } elseif (!empty($this->themeRoute) && $this->hasThemeController()) {
                $this->sendRequestToThemeController();
            }
        } else if ($this->hasThemeController()) {
            $this->sendRequestToThemeController();
        } else {
            new \Joonika\Controller($this);
        }
    }

    private function sendRequestToController()
    {
        $this->goToController();

    }

    protected function removeVariblesOfQueryString($url)
    {
        if ($url != '') {
            $parts = explode("?", $url, 2);
            $url = $parts[0];
        }
        return $url;
    }

    protected function getQueryString($url)
    {
        $outout='';
        if ($url != '') {
            $parts = explode("?", $url);
            if (isset($parts[1])) {
                $outout = $parts[1];
            }
        }
        return $outout;
    }

    public function setForceNotRoute($forceNotRoute)
    {
        $this->forceNotRoute = $forceNotRoute;
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
        $output = false;
        foreach (self::JK_WEBSITES() as $jk_website) {
            if ($jk_website["domain"] . '/' == $domain) {
                $output = $jk_website;
            }
        }
        return $output;
    }

    public function getWebSiteId($domain)
    {
        $lists = $this->getWebsites();
        $id = 0;
        foreach ($lists as $k => $v) {
            if ($domain == $v) {
                $id = $k;
                break;
            }
        }
        return $id;
    }

    public function getWebsites()
    {
        $output = [];
        foreach (self::JK_WEBSITES() as $jk_website) {
            $output[$jk_website['domain']] = $jk_website['domain'];
        }
        return $output;
    }

    public function getWebsiteIds()
    {
        $output = [];
        $ids = [];
        $websites = self::JK_WEBSITES();
        if (!empty($websites)) {
            foreach ($websites as $jk_website) {
                if (empty($ids[$jk_website['id']])) {
                    $ids[$jk_website['id']] = [];
                }
                array_push($ids[$jk_website['id']], $jk_website);
            }
            foreach ($ids as $idV) {
                foreach ($idV as $idb) {
                    if (empty($output[$idb['id']])) {
                        $output[$idb['id']] = $idb['domain'];
                    } else {
                        $output[$idb['id']] .= ',' . $idb['domain'];
                    }
                }
            }
        }
        return $output;
    }

    public function getWebsiteInfoByType($type)
    {
        $output = false;
        foreach (self::JK_WEBSITES() as $jk_website) {
            if (isset($jk_website[$type]) && $jk_website[$type] == true) {
                $output = $jk_website;
            }
        }
        return $output;
    }

    public function getMainWebsite()
    {

        $output = false;
        foreach (self::JK_WEBSITES() as $jk_website) {
            if ($jk_website['type'] == 'main') {
                if (isset($jk_website['protocol'])) {
                    $output = $jk_website['protocol'] . '://' . $jk_website['domain'];
                }
            }
        }
        return $output;
    }

    public function getWebsiteDomainByType($type)
    {
        $output = false;
        $siteInfo = $this->getMainWebsiteInfo($type);
        if ($siteInfo) {
            $output = $siteInfo['domain'];
        }
        return $output;
    }

    public function getlang($websiteDomain, $tempLang)
    {
        $output = false;
        foreach (self::$JK_WEBSITE['languages'] as $jk_language) {
            if ($jk_language['slug'] == $tempLang) {
                $output = $jk_language;
            }
        }
        return $output;
    }

    public function getlangs($websiteDomain = '')
    {
        $output = [];
        if (!empty(self::$JK_WEBSITE['languages'])) {
            foreach (self::$JK_WEBSITE['languages'] as $jk_language) {
                array_push($output, [
                    "slug" => $jk_language['slug'],
                    "name" => $jk_language['name'],
                ]);
            }
        }
        return $output;
    }

    public function getServerType($domain)
    {
        $output = 'main';
        if (isset(self::JK_WEBSITES()[rtrim($domain, '/')])) {
            $jk_website = self::JK_WEBSITES()[rtrim($domain, '/')];
            if (isset($jk_website["type"])) {
                $output = $jk_website["type"];
            }
        }
        return $output;
    }

}
