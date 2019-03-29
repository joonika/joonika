<?php
namespace Joonika\Joonika;

class Router
{
    public $protocol = 'http://'; //http or https
    public $domainFull; //$protocol + $domain + '/' : example http://ipinbar.net/
    public $domain; // store website domain name from database
    public $websiteID;
    public $uri;   //uri without '/' start
    public $url;    //url without query string
    public $path = [];
    public $lang;
    public $direction;
    public $subFolder = 'pages';
    public $query_string; // query string
    public $forceNotRoute=false;
    public $themeRoute = true;
    public $found = true;
    public $langLocale = "en";
    public $theme = "one";
    public $clientIp = "";


    public function __construct()
    {
        global $database;
        // protocol = http or https
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        ) {
            $this->protocol = 'https://';
        } else {
            $this->protocol = 'http://';
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $this->clientIp = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->clientIp = $_SERVER['REMOTE_ADDR'];
        }

        // $domainFull = protocol + domain name + '/'
        $this->domainFull=$this->protocol.$_SERVER['HTTP_HOST'].'/';

        // $domain= domain name + '/'
        $this->domain=$_SERVER['HTTP_HOST'].'/';

        //get uri = uri - '/'
        $this->uri=ltrim($_SERVER['REQUEST_URI'],'/');

        //domainFull=domainFull + (uri - '/')
        $this->url=$this->domainFull.$this->uri;

        if(!defined('JK_APP_NON_WWW')){
            die("config not found");
        }
        //ignore www. from url and redirect to (protocol + (domain - 'www.') + uri)
        if(defined('JK_APP_NON_WWW') && JK_APP_NON_WWW){
            if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $this->protocol . substr($_SERVER['HTTP_HOST'],4) . $_SERVER['REQUEST_URI']);
                exit;
            }
        }

        // divided url to 2 pieces ( url & query string) exp[0]=url and exp[1]=query string
        $exp=explode('?',$this->url);

        //  parse url and get query string variable and put that in $_get array
        if(isset($exp[1])){
            parse_str($exp[1], $this->query_string);
            global $_GET;
            foreach ($this->query_string as $g=>$v){
                $_GET[$g]=$v;
            }
        }


        //remove '/' from end of uri
        if(substr($this->uri,-1)=='/'){
            header("HTTP/1.1 301 Moved Permanently");
            redirect_to(rtrim($this->url,"/"));
        }
        //redirect to url without http or https
        if($this->protocol=="http://" && JK_APP_FORCE_SSL){
            header("HTTP/1.1 301 Moved Permanently");
            redirect_to("https://".ltrim($this->url,$this->protocol));
        }
        // get domain from database and put that into website variable
        $website=$database->get('jk_websites','*',[
            "domain"=>$this->domain
        ]);

        if(!isset($website['id'])){
            Errors::errorHandler(0,"website not found" , __FILE__ , __LINE__);
        }

        $this->uri=$this->removeVariblesOfQueryString($this->uri);

        $path = array_values(array_filter(explode('/', $this->uri)));

        if(sizeof($path)==0){
            redirect_to($this->url.$website['defaultLang']);
        }

        $tempLang=$path[0];

        $getLang=$database->get('jk_languages','*',[
            "AND"=>[
                "websiteID"=>$website['id'],
                "slug"=>$tempLang,
                "status"=>"active",
            ]
        ]);
        if(!isset($getLang['id'])){
            redirect_to($this->domainFull.$website['defaultLang'].'/'.$this->uri);
        }
        $this->websiteID=$website['id'];
        $this->lang=$getLang['slug'];
        $this->langLocale=$getLang['locale'];
        $this->direction=$getLang['direction'];


        $this->lang=$tempLang;
        $this->theme=$getLang['theme'];

        unset($tempLang);
        unset($path[0]);
        $path=array_values(array_filter($path));
        $this->path=$path;
    }

}