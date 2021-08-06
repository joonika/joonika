<?php

namespace Joonika;


abstract class RequestScaffold
{
    protected $request_method;
    protected $post;
    protected $server;
    protected $get;
    public $userId = 0;
    protected $files;
    protected $uploadFilesResult;
    protected $instance;
    protected $headers;
    private $qsArr = null;
    private $allowMimeType = [
        "application/graphql",
        "application/javascript",
        "application/json",
        "application/ld+json",
        "application/msword",
        "application/pdf",
        "application/sql",
        "application/vnd.api+json",
        "application/vnd.ms-excel",
        "application/vnd.ms-powerpoint",
        "application/vnd.oasis.opendocument.text",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/x-www-form-urlencoded",
        "application/xml",
        "application/zip",
        "application/zstd",
        "audio/mpeg",
        "audio/ogg",
        "image/gif",
        "image/apng",
        "image/flif",
        "image/webp",
        "image/x-mng",
        "image/jpeg",
        "image/png",
        "multipart/form-data",
        "text/css",
        "text/csv",
        "text/html",
        "text/php",
        "text/plain",
        "text/xml",
    ];
    public $purifier = null;

    public function __construct()
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.Allowed', 'a[href],blockquote,br,del,em,figcaption[class],figure[class|style],h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],img[title|alt|src|style],li[style],ol[style],p[class|style],span[class|style],pre,strong,ul[style]');
        $config->set('HTML.DefinitionID', 'enduser-customize.html tutorial');
        $config->set('HTML.DefinitionRev', 1);
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addElement('figcaption', 'Block', 'Flow', 'Common');
            $def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
        }
        $this->purifier = new \HTMLPurifier($config);

        if (isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = urldecode($_SERVER['REQUEST_URI']);
        }
        if (isset($_SERVER['HTTP_REFERER'])) {
            $_SERVER['HTTP_REFERER'] = urldecode($_SERVER['HTTP_REFERER']);
        }
        $_SERVER['REQUEST_URI'] = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
        if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == "POST") {
//            if (!CSRF::checkCsrfToken()) {
//                dd('s');
//            };
        }

        global $_GET;
        $json_str = file_get_contents('php://input');
        $jsonInputs = json_decode($json_str, true);
        if (is_array($jsonInputs) && sizeof($jsonInputs) > 0) {
            foreach ($jsonInputs as $key => $val) {
                if (is_string($val)) {
                    $_POST[$key] = htmlspecialchars($val);
                }
            }
        }
        $this->request_method = !empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
        $this->headers = getallheaders();
        $this->server = $_SERVER;
        if (checkArraySize($_POST)) {
            foreach ($_POST as $postK => $postV) {
                if (is_array($postV)) {
                    $_POST[$this->purifier->purify($postK)] = $this->purifyArray($postV);
                } else {
                    $_POST[$this->purifier->purify($postK)] = $this->purifier->purify(Idate::tr_num($postV));
                }
            }
        }
        if (checkArraySize($_GET)) {
            foreach ($_GET as $getK => $getV) {
                if (is_array($getV)) {
                    $_GET[$this->purifier->purify($getK)] = $this->purifyArray($getV);
                } else {
                    $_GET[$this->purifier->purify($getK)] = $this->purifier->purify(Idate::tr_num($getV));
                }
            }
        }

        $this->getQueryStrings();
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->get = $this->qsArr;

    }


    private function purifyArray($array_of_html)
    {
        $array = array();
        if (checkArraySize($array_of_html)) {
            foreach ($array_of_html as $key => $value) {
                if (checkArraySize($value)) {
                    $array[$this->purifier->purify($key)] = $this->purifyArray($value);
                } else {
                    $array[$this->purifier->purify($key)] = $this->purifier->purify(Idate::tr_num($value));
                }
            }
        }
        return $array;
    }

    final function allowMimeType()
    {
        if (jk_options_get('mimeTypeAllow_' . JK_WEBSITE_ID())) {
            return jk_options_get('mimeTypeAllow_' . JK_WEBSITE_ID());
        } else {
            return $this->allowMimeType;
        }
    }

    final function allowFileSize()
    {
        if (jk_options_get('uploadFileSize_' . JK_WEBSITE_ID())) {
            return jk_options_get('uploadFileSize_' . JK_WEBSITE_ID());
        } else {
            return 16;
        }
    }

    private function getQueryStrings()
    {
        $queryStringFromREQUEST_URI = explode('?', $_SERVER['REQUEST_URI']);
        $queryStringFromHTTP_REFERER = isset($_SERVER['HTTP_REFERER']) ? explode('?', $_SERVER['HTTP_REFERER']) : [];

        if (sizeof($queryStringFromREQUEST_URI) == 2 && !empty($queryStringFromREQUEST_URI[1]) && (strpos($queryStringFromREQUEST_URI[1], '=') !== false)) {
            parse_str($queryStringFromREQUEST_URI[1], $this->qsArr);
        } elseif (sizeof($queryStringFromHTTP_REFERER) == 2 && !empty($queryStringFromHTTP_REFERER[1]) && (strpos($queryStringFromHTTP_REFERER[1], '=') !== false)) {
            parse_str($queryStringFromHTTP_REFERER[1], $this->qsArr);
        }
        return $this->qsArr;
    }

    abstract public function Input($input);

    abstract public function all();

    abstract public function queryStrings($field = null);

    abstract public function hasQueryString($field);

    abstract public function fillQueryString($field);

    abstract public function requestMethod();

    abstract public function isMethod($method);

    abstract public function has($field);

    abstract public function fill($field);

    abstract public function ip();

    abstract public function scriptName();
}