<?php
namespace Joonika\Joonika;

class Router
{
    public $protocol = []; //http or https
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

}