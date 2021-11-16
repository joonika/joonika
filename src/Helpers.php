<?php

use Joonika\ACL;
use Joonika\Controller\AssetsController;
use Joonika\Controller\AstCtrl;
use Joonika\Controller\ThemeController;
use Joonika\Database;
use Joonika\FS;
use Joonika\Modules\Ws\Ws;
use Joonika\Request;
use Joonika\Route;


if (!function_exists('DS')) {
    function DS()
    {
        return DIRECTORY_SEPARATOR;
    }
}

/*--------------die and dump function--------------*/
if (!function_exists('dd')) {
    function dd($inp, $die = true)
    {
        if (isset($inp)) {
            if (is_array($inp) or is_object($inp)) {
                echo '<pre class="text-left text-break  ltr" dir="ltr">';
                print_r($inp);
//                var_dump($inp);
                echo '</pre>';
            } else {
                echo '<pre>' . $inp . '</pre><br>';
            }
        } else {
            echo "variable {$inp} is not set or null";
        }
        if ($die) {
            die();
        }
    }
}
if (!function_exists('jdie')) {
    function jdie($inp, $die = true, $loginId = 0)
    {
        $continue = true;
        if (!empty($loginId) && $loginId != JK_LOGINID()) {
            $continue = false;
        }
        if ($continue) {
            if (isset($inp)) {
                if (is_array($inp) or is_object($inp)) {
                    echo '<pre class="text-left text-break  ltr" dir="ltr">';
                    print_r($inp);
//                var_dump($inp);
                    echo '</pre>';
                } else {
                    echo '<pre>' . $inp . '</pre><br>';
                }
            } else {
                echo "variable {$inp} is not set or null";
            }
            if ($die) {
                die();
            }
        }
    }
}
if (!function_exists('jdieJson')) {
    function jdieJson($inp = [], $die = true)
    {
        if (is_array($inp)) {
            echo json_encode($inp, 256 | 128);
        }
        if ($die) {
            die();
        }
    }
}


if (!function_exists('getSubFolders')) {
    function getSubFolders($path)
    {
        $founded = [];
        $scanned_directory = array_diff(scandir($path), array('..', '.'));
        if (sizeof($scanned_directory) >= 1) {
            foreach ($scanned_directory as $elem) {
                if (is_dir($path . DS() . $elem)) {
                    array_push($founded, $elem);
                }
            }
        }
        return $founded;
    }
}


$rootPath = __DIR__ . DS() . ".." . DS() . ".." . DS() . ".." . DS() . ".." . DS();
$modulePath = $rootPath . "modules";
$themesPath = $rootPath . "themes";


if (FS::isDir($modulePath)) {
    $modules = getSubFolders($modulePath);
    foreach ($modules as $module) {
        $path = $rootPath . "modules" . DS() . $module . DS() . 'inc' . DS() . "helper.php";
        if (FS::isExistIsFile($path)) {
            include_once $path;
        }
    }
}

if (FS::isDir($themesPath)) {
    $themes = getSubFolders($themesPath);

    foreach ($themes as $theme) {
        $path = $rootPath . "themes" . DS() . $theme . DS() . 'inc' . DS() . "helper.php";
        if (file_exists($path)) {
            include_once $path;
        }
    }
}


if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}


if (!function_exists('event')) {
    function event($event)
    {
        dd('ssss');
        return;
    }
}

if (!function_exists('setPageTitle')) {
    function setPageTitle($obj, $title)
    {
        $obj->Route->properties['title'] = $title;
    }
}

if (!function_exists('setTitle')) {
    function setTitle($title)
    {
        Route::$JK_TITLE = $title;
    }
}

if (!function_exists('get_head')) {
    function get_head($view)
    {
        $view->head();
    }
}
if (!function_exists('get_foot')) {
    function get_foot($view)
    {
        $view->foot();
    }
}

if (!function_exists('jk_die')) {

    /**
     * @param string $message
     */
    function jk_die($message = "")
    {
        echo $message;
        die;
    }
}

if (!function_exists('hasPermission')) {
    function hasPermission($perm, $companyID = false)
    {
        $acl = ACL::ACL();
        return $acl->hasPermission($perm, $companyID);
    }
}

if (!function_exists('now')) {
    function now()
    {
        return date('Y/m/d H:i:s');
    }
}

//------ permissions functions ---------//
if (!function_exists('hasPermissionLogin')) {
    function hasPermissionLogin($perm)
    {
        $acl = ACL::ACL();
        return $acl->hasPermissionLogin($perm);
    }
}
if (!function_exists('aclNameById')) {
    function aclNameById($permID)
    {
        $acl = ACL::ACL();
        return $acl->aclNameById($permID);
    }
}
if (!function_exists('checkValueHtmlFa')) {
    function checkValueHtmlFa($value, $type = 1)
    {
        $acl = ACL::ACL();
        return $acl->checkValueHtmlFa($value, $type);
    }
}
if (!function_exists('permlist')) {
    function permlist($module)
    {
        $acl = ACL::ACL();
        return $acl->permlist($module);
    }
}
//------ permissions functions ---------//

if (!function_exists('theme_assets_to_ctrl')) {
    function theme_assets_to_ctrl($path, $type = null, $https = false)
    {
        $url = '/themes/' . get_active_theme() . '/assets/' . $path;
        if (!$type) {
            $type = pathinfo($path)['extension'];
        }
        switch ($type) {
            case "css":
                AstCtrl::ADD_HEADER_STYLES_FILES($url, false);
                break;
            case "js":
                AstCtrl::ADD_FOOTER_JS_FILES($url, false);
                break;
        }
    }
}

if (!function_exists('themes_assets')) {
    function themes_assets($path = null, $https = false)
    {
        $url = $https ? "https://" : "http://";
        $path .= $path ? $_SERVER['HTTP_HOST'] . '/themes/' . JK_THEME() . '/' . $path : $_SERVER['HTTP_HOST'] . '/themes/' . JK_THEME() . '/';
        return $url;
    }
}


if (!function_exists("do_scripts")) {
    function do_scripts()
    {
        AstCtrl::FOOTER_JS_FILES();
        AstCtrl::FOOTER_SCRIPTS();
    }
}


if (!function_exists('modules_assets_to_ctrl')) {
    function modules_assets_to_ctrl($path, $type = null, $https = false)
    {
        $url = JK_DOMAIN() . 'modules/' . $path;
        if (!$type) {
            $type = pathinfo($path)['extension'];
        }
        switch ($type) {
            case "css":
                AstCtrl::ADD_HEADER_STYLES_FILES($url, false);
                break;
            case "js":
                AstCtrl::ADD_FOOTER_JS_FILES($url, false);
                break;
        }
    }
}

if (!function_exists('modules_assets')) {
    function modules_assets($module, $path = null, $https = false)
    {
        $url = $https ? "https://" : "http://";
        $url .= $path ? $_SERVER['HTTP_HOST'] . '/modules/' . $path : $_SERVER['HTTP_HOST'] . '/modules/' . '/';
        return $url;
    }
}

if (!function_exists('url')) {
    function url($path = "")
    {
        return JK_DOMAIN_LANG() . $path;
    }
}

//--------------Consts

if (!function_exists('JK_SITE_PATH')) {
    function JK_SITE_PATH()
    {
        return Route::JK_SITE_PATH();
    }
}
if (!function_exists('JK_DOMAIN')) {
    function JK_DOMAIN()
    {
        return Route::JK_DOMAIN();
    }
}
if (!function_exists('JK_DOMAIN_WOP')) {
    function JK_DOMAIN_WOP()
    {
        return Route::JK_DOMAIN_WOP();
    }
}
if (!function_exists('JK_URI')) {
    function JK_URI()
    {
        return Route::JK_URI();
    }
}
if (!function_exists('JK_URL')) {
    function JK_URL()
    {
        return Route::JK_URL();
    }
}

if (!function_exists('JK_WEBSITE_ID')) {
    function JK_WEBSITE_ID()
    {
        return Route::JK_WEBSITE_ID();
    }
}
if (!function_exists('JK_LANG_LOCALE')) {
    function JK_LANG_LOCALE()
    {
        return Route::JK_LANG_LOCALE();
    }
}
if (!function_exists('JK_DIRECTION')) {
    function JK_DIRECTION()
    {
        return Route::JK_DIRECTION();
    }
}
if (!function_exists('JK_LANG')) {
    function JK_LANG()
    {
        return Route::JK_LANG();
    }
}
if (!function_exists('JK_LANGUAGES')) {
    function JK_LANGUAGES()
    {
        return Route::JK_LANGUAGES();
    }
}
if (!function_exists('JK_THEME')) {
    function JK_THEME()
    {
        return Route::JK_THEME();
    }
}
if (!function_exists('JK_DIRECTION_SIDE')) {
    function JK_DIRECTION_SIDE()
    {
        return Route::JK_DIRECTION_SIDE();
    }
}
if (!function_exists('JK_DIRECTION_SIDE_R')) {
    function JK_DIRECTION_SIDE_R()
    {
        return Route::JK_DIRECTION_SIDE_R();
    }
}
if (!function_exists('JK_DIRECTION_SIDE_S')) {
    function JK_DIRECTION_SIDE_S()
    {
        return Route::JK_DIRECTION_SIDE_S();
    }
}
if (!function_exists('JK_DIRECTION_DASH')) {
    function JK_DIRECTION_DASH()
    {
        return Route::JK_DIRECTION_DASH();
    }
}
if (!function_exists('JK_DIRECTION_DASH_R')) {
    function JK_DIRECTION_DASH_R()
    {
        return Route::JK_DIRECTION_DASH_R();
    }
}
if (!function_exists('JK_DOMAIN_LANG')) {
    function JK_DOMAIN_LANG()
    {
        return Route::JK_DOMAIN_LANG();
    }
}
if (!function_exists('JK_PROPERTIES')) {
    function JK_PROPERTIES($obj)
    {
        return $obj->Route->properties;
    }
}
if (!function_exists('JK_TITLE')) {
    function JK_TITLE()
    {
        return Route::JK_TITLE();
    }
}
if (!function_exists('JK_DIR_MODULES')) {
    function JK_DIR_MODULES()
    {
        return Route::JK_DIR_MODULES();
    }
}
if (!function_exists('JK_DIR_THEMES')) {
    function JK_DIR_THEMES()
    {
        return Route::JK_DIR_THEMES();
    }
}
if (!function_exists('JK_DIR_JOONIKA')) {
    function JK_DIR_JOONIKA()
    {
        return Route::JK_DIR_JOONIKA();
    }
}
if (!function_exists('JK_LOGINID')) {
    function JK_LOGINID()
    {
        return Route::JK_LOGINID();
    }
}
if (!function_exists('JK_USERID')) {
    function JK_USERID()
    {
        return Route::JK_USERID();
    }
}
if (!function_exists('JK_TOKENID')) {
    function JK_TOKENID()
    {
        return Route::JK_TOKENID();
    }
}
if (!function_exists('JK_WEBSITES')) {
    function JK_WEBSITES()
    {
        return Route::JK_WEBSITES();
    }
}

if (!function_exists('JK_WEBSITE')) {
    function JK_WEBSITE()
    {
        return Route::JK_WEBSITE();
    }
}

if (!function_exists('JK_MODULES')) {
    function JK_MODULES()
    {
        return Route::JK_MODULES();
    }
}

if (!function_exists('JK_HOST')) {
    function JK_HOST()
    {
        return Route::JK_HOST();
    }
}

if (!function_exists('JK_APP_DEBUG')) {
    function JK_APP_DEBUG()
    {
        return Route::JK_APP_DEBUG();
    }
}
if (!function_exists('JK_ROOT_PATH_FROM_JOONIKA')) {
    function JK_ROOT_PATH_FROM_JOONIKA()
    {
        return Route::JK_ROOT_PATH_FROM_JOONIKA();
    }
}
if (!function_exists('callApi')) {
    function callApi($method, $url, $data = null, $header = null)
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($header) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        // EXECUTE:

        $result = curl_exec($curl);

        if (!$result) {
            die("Connection Failure");
        }
        curl_close($curl);
        return $result;
    }
}
//------------------
if (!function_exists('import_navigation')) {
    function import_navigation($module)
    {
        if (FS::isExistIsFile(JK_SITE_PATH() . 'modules' . DS() . $module . DS() . 'inc' . DS() . 'navigation.php')) {
            require_once JK_SITE_PATH() . 'modules' . DS() . $module . DS() . 'inc' . DS() . 'navigation.php';
        } else {
            echo "<h5 class='p-3 text-center text-danger'>" . __('file') . " " . JK_SITE_PATH() . 'modules' . DS() . $module . DS() . 'inc' . DS() . 'navigation.php' . " " . __('not found!') . "</h5>";
        }
    }
}
/*------ create event log function --------*/
if (!function_exists('logInsert')) {
    function logInsert($options = [])
    {
        $database = Database::connect();
        $from = null;
        if (isset($options['from'])) {
            $from = $options['from'];
            if (is_array($from)) {
                $from = json_encode($options['from'], JSON_UNESCAPED_UNICODE);
            }
        }

        $to = null;
        if (isset($options['to'])) {
            $to = $options['to'];
            if (is_array($to)) {
                $to = json_encode($options['to'], JSON_UNESCAPED_UNICODE);
            }
        }
        $options = [
            "userID" => isset($options['userID']) ? $options['userID'] : '',
            "module" => isset($options['module']) ? $options['module'] : '',
            "moduleId" => isset($options['moduleID']) ? $options['moduleID'] : '',
            "type" => isset($options['type']) ? $options['type'] : '',
            "typeID" => isset($options['typeID']) ? $options['typeID'] : '',
            "from" => $from,
            "to" => $to,
            "description" => isset($options['description']) ? $options['description'] : '',
            "createdOn" => date('Y-m-d H:i:s'),
        ];
        $database->insert("jk_logs", $options);
    }
}

/*----------function for test form input data------------*/
if (!function_exists('test_input')) {
    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

/*function for create alert message*/
if (!function_exists('message')) {
    function message($msg, $type = 'danger')
    {
        ?>
        <div class="alert alert-<?php echo $type; ?> alert-dismissible">
            <span class="close" data-dismiss="alert">
                &times;
            </span>
            <p><?php echo $msg; ?></p>
        </div>
        <?php
    }
}

//shuffle str argument
if (!function_exists('str_rand')) {
    function str_rand($input)
    {
        return str_shuffle($input);
    }
}

//
if (!function_exists('str_limit')) {
    function str_limit($string, $limit)
    {
        return substr($string, 0, $limit);
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}


if (!function_exists('redirect_to')) {
    function redirect_to($url = "")
    {
        $url = rtrim($url, '/');
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('__')) {
    function __($msg, $module = null, $ucfirst = 0)
    {
        global $translate;
        if (!empty($translate[$msg])) {
            $return = $ucfirst == 1 ? ucfirst($translate[$msg]) : $translate[$msg];
            $return = $return != '' ? $return : ($ucfirst == 1 ? ucfirst($msg) : $msg);
        } else {
            $return = $ucfirst == 1 ? ucfirst($msg) : $msg;
        }
        return $return;
    }
}

if (!function_exists('__e')) {
    function __e($msg, $module = null, $ucfirst = 0)
    {
        echo __($msg, $ucfirst);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        new \Joonika\CSRF();
        return '<meta name="csrf-token" content="' . \Joonika\CSRF::getCsrfToken() . '">';
    }
}
if (!function_exists('csrf_token_filed')) {
    function csrf_token_filed()
    {
        echo "<input type='hidden' name='csrfToken' value='" . \Joonika\CSRF::getCsrfToken() . "' >";
    }
}

function arrayReverseKeyValue($array)
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $arrK => $arrV) {
            $back[$arrV] = $arrK;

        }
    }
    return $back;
}

function error404($return = true)
{
    http_response_code(404);
    if ($return) {
        templateRenderSimpleAlert(404, __("file not found"));
    }
}

function error403($return = true, $message = '')
{
    http_response_code(403);
    if ($return) {
        templateRenderSimpleAlert(403, __("access denied"), $message);
    }
}

function templateRenderSimpleAlert($code, $title, $description = '', $extraCode = '')
{
    $codeColor = '#00b5c3';
    $direction = JK_DIRECTION();
    $descriptionSize = strlen($description) >= 500 ? 15 : 20;
    $topSize = strlen($description) >= 500 ? 40 : 30;
    $isAjax = false;
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $isAjax = true;
    }
    switch ($code) {
        case 404:
            $codeColor = '#00b5c3';
            $description = !empty($description) ? $description : __("the requested url was not found on this website");
            break;
        case 403:
            $codeColor = '#00b5c3';
            $description = !empty($description) ? $description : __("you have not access this page or your permission limited");
            break;
        default:
            $codeColor = '#00b5c3';
            $direction = "ltr";
    }
    if (empty($isAjax)) {
        ?>
        <html>
        <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow"/>
        <title><?= $title ?></title>
        <?php
    }
    ?>
    <style>

        .center {
            min-height: 100px;
            width: 50%;
            text-align: center;
            bottom: 0;
            margin: auto;
            background-color: white;
            margin-top: 30px;
        }

        .center .title {
            font-size: 25px;
            color: <?=$codeColor?>;
        }

        .center .description {
            font-size: <?=$descriptionSize?>px;
            margin-top: 10px;
            padding-top: 20px;
            border-top: solid 1px silver;

        }

        .center .description p {
            word-wrap: break-word !important;
            width: 100%;
        }

        @media only screen and (max-width: 480px) {
            .center {
                width: 90%;
            }

            .center .title {
                font-size: 20px;
            }

            .center .description {
                font-size: <?=$descriptionSize-5?>px;
            }
        }
    </style>
    <?php
if (empty($isAjax)) {
    ?>
</head>
<body>
    <?php
}
    ?>
    <div class="center" style=" direction: <?= $direction ?>;">
        <div>
            <?php
            $codeView = '';
            if ($code != 200) {
                $codeView = $code;
                if (!empty($extraCode)) {
                    $codeView = $codeView . '-' . $extraCode;
                }
                $codeView = ' (' . sprintf(__("error code: %s"), $codeView) . ')';
            }
            ?>
            <div class="title"><?= $title ?><?= $codeView ?></div>
            <div class="description">
                <?= $description ?>
                <?php
                if ($code != 200) {
                    ?>
                    <hr style="margin: 30px 0!important;"/>
                    <div style="direction: <?= $direction ?>!important;text-align: <?= JK_DIRECTION_SIDE() ?>">
                        <div style="margin-top:15px;"><span
                                    style="color: grey;"><?= __("website") ?>: </span><span
                                    style="direction: ltr"><?= JK_HOST() ?></span></div>
                        <?php
                        if (JK_APP_DEBUG()) {
                            ?>
                            <div style="margin-top:15px;"><span
                                        style="color: grey;"><?= __("address") ?>: </span><span
                                        style="direction: ltr"><?= JK_URI() ?></span></div>
                            <?php
                        }
                        ?>
                        <div style="margin-top:15px;"><span
                                    style="color: grey;"><?= __("datetime") ?>: </span><span
                                    style="direction: ltr"><?= \Joonika\Idate::date_int("Y/m/d-H:i:s") ?></span></div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    if (empty($isAjax)) {
        ?>
        </body>
        </html>
        <?php
    }

}

function loading_fa($options = [])
{
    $opt = [
        "class" => 'text-center',
        "iclass" => '0',
        "iclass-size" => '3',
        "elem" => 'div',
        "text-sr" => __("loading, please wait"),
        "text" => "",
    ];

    if (is_array($options) && sizeof($options) >= 1) {
        foreach ($options as $tr => $val) {
            if (isset($opt[$tr])) {
                $opt[$tr] = $val;
            }
        }
    }
    $butshow_start = '';
    $butshow_end = '';
    if ($opt['elem'] != '') {
        $butshow_start .= '<' . $opt['elem'];
        if ($opt['class'] != '') {
            $butshow_start .= ' class="' . $opt['class'];

            $butshow_start .= '" ';
        }
        if ($opt['iclass'] == 0) {
            $opticlass = 'fa fa-spinner fa-pulse fa-fw fa-' . $opt['iclass-size'] . 'x';
        } else {
            $opticlass = $opt['iclass'];
        }
        if ($opt['text'] != '') {
            $opttext = $opt['text'];
        } else {
            $opttext = '';
        }
        $butshow_start .= '><i class="' . $opticlass . '"></i><span class="sr-only">' . $opt['text-sr'] . ' ...</span>' . $opttext;

        $butshow_end .= '</' . $opt['elem'] . '>';
    }

    $butshow = $butshow_start . $butshow_end;

    return $butshow;
}

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

if (!function_exists('toggle_modal')) {
    function toggle_modal($action = "hide", $id = "#modal_global", $time = 2000)
    {
        return '<script>
                setTimeout(function() {
                  $("' . $id . '").modal("' . $action . '");
                },' . $time . ')
              </script>';
    }
}

function ajax_load($options = [])
{
    $option = [
        "url" => '',
        "on" => '',
        "formID" => '',
        "validate" => false,
        "prevent" => false,
        "nonEmpty" => false,
        "type" => 'post',
        "data" => '$(this).serialize()',
        "success_response" => '',
        "error_response" => '',
        "error_swal" => true,
        "type_response" => 'html',
        "success_response_after" => '',
        "loading" => [],
        "show_modal" => false,
        "beforeAll" => "",
        "timeout" => 60000,
    ];
    $return = '';
    if (sizeof($option) >= 1) {
        foreach ($option as $key => $opt) {
            if (isset($options[$key])) {
                $option[$key] = $options[$key];
            }
        }
    }
    if (substr($option['formID'], 0, 1) != '[') {
        $option['formID'] = "#" . $option['formID'];
    }
    if ($option['on'] != '') {
        if ($option['formID'] != '') {
            if ($option['prevent'] == true) {
                $return .= '$("' . $option['formID'] . '").on("' . $option['on'] . '",function(e){
            e.preventDefault();
            ';
            } else {
                $return .= '$("' . $option['formID'] . '").on("' . $option['on'] . '",function(){
            ';
            }
        }
    }
    $return .= 'var datas=' . $option['data'] . ';
    ';
    if ($option['loading'] != '') {

        $return .= '$("' . $option['success_response'] . '").' . $option['type_response'] . '(\'' . loading_fa($option['loading']) . '\');';
    }
    $error_response = 'swal ( "' . __("an error occurred") . '" , msg + " \n ' . $option['url'] . '" ,  "error",{
          buttons: "' . __("ok") . '!",
        });';
    if ($option['error_swal'] == false) {
        $error_response = "";
    }
    $error_response .= $option['error_response'];
    if ($option['nonEmpty'] && $option['on'] == 'change') {
        $return .= '
       if($(this).val()!=""){
       ';
    }
    if ($option['show_modal']) {
        $modalName = str_replace("_body", "", $option['success_response']);
        $modalName = str_replace("#", "", $modalName);
        $return .= '$("#' . $modalName . '").modal("show");';
    }
    $return .= '' . $option['beforeAll'] . '';
    $return .= '
    $.ajax({
        url: "' . $option['url'] . '",
        type: "' . $option['type'] . '",
        timeout: ' . $option['timeout'] . ',
        data: datas,
        success: function (response) {
            $("' . $option['success_response'] . '").' . $option['type_response'] . '(response);
            ' . $option['success_response_after'] . '
        }, error: function (jqXHR,exception) {
        var msg = "";
        if (jqXHR.status === 0) {
            msg = "' . __("not connect. verify network.") . '";
        } else if (jqXHR.status == 404) {
            msg = "' . __("requested page not found. [404]") . '";
        } else if (jqXHR.status == 403) {
            msg = "' . __("you dont have sufficient permission. [403]") . '";
        } else if (jqXHR.status == 500) {
            msg = "' . __("internal server error [500].") . '";
        } else if (exception === "parsererror") {
            msg = "' . __("requested JSON parse failed.") . '";
        } else if (exception === "timeout") {
            msg = "' . __("time out error.") . '";
        } else if (exception === "abort") {
            msg = "' . __("ajax request aborted.") . '";
        } else {
            msg = "uncaught Error.\n" + jqXHR.responseText;
        }
        ' . $error_response . '
        
}
    });
    ';
    if ($option['nonEmpty'] && $option['on'] == 'change') {
        $return .= '
       }else{
        $("' . $option['success_response'] . '").html("");      
       }
       ';
    }

    if ($option['on'] != '') {
        if ($option['formID'] != '') {

            $return .= '
                return false
});
 ';

        }
    }
    return $return;
}


if (!function_exists('prettyJsonPrint')) {
    function prettyJsonPrint($json)
    {
        $json_pretty = json_encode(json_decode($json), JSON_PRETTY_PRINT | 256);
        return $json_pretty;
    }
}

if (!function_exists('listModules')) {
    function listModules()
    {
        $modules = [];
        if (FS::isDir(JK_DIR_MODULES())) {
            $scanned_directory = array_diff(scandir(JK_DIR_MODULES()), array('..', '.'));
            if (sizeof($scanned_directory) >= 1) {
                foreach ($scanned_directory as $elem) {
                    if (is_dir(JK_DIR_MODULES() . $elem)) {
                        array_push($modules, $elem);
                    }
                }
            }
        }


        $modulesInVendor = glob(JK_SITE_PATH() . 'vendor/joonika/module-*');
        if (checkArraySize($modulesInVendor)) {
            foreach ($modulesInVendor as $moduleInVendor) {
                $moduleName = explode('-', $moduleInVendor);
                if (sizeof($moduleName) == 2) {
                    $moduleCheckName = $moduleName[1];
                    if (!in_array($moduleCheckName, $modules)) {
                        array_push($modules, $moduleCheckName);
                    } else {
                        if (JK_APP_DEBUG()) {
                            die("vendor " . $moduleCheckName . ' is duplicate');
                        }
                    }
                }
            }
        }


        return $modules;
    }
}
if (!function_exists('listThemes')) {
    function listThemes()
    {
        $scanned_directory = array_diff(scandir(JK_DIR_THEMES()), array('..', '.'));
        $modules = [];
        if (sizeof($scanned_directory) >= 1) {
            foreach ($scanned_directory as $elem) {
                if (is_dir(JK_DIR_THEMES() . $elem)) {
                    array_push($modules, $elem);
                }
            }
        }
        return $modules;
    }
}


if (!function_exists('listModulesReadFiles')) {
    function listModulesReadFiles($file)
    {
        $modules = listModules();
        if (sizeof($modules) >= 1) {
            foreach ($modules as $mod) {
                if (file_exists(JK_SITE_PATH() . 'modules' . DS() . $mod . DS() . 'Views' . DS() . $file)) {
                    include_once JK_SITE_PATH() . 'modules' . DS() . $mod . DS() . 'Views' . DS() . $file;
                }
            }
        }
    }
}


if (!function_exists('get_active_theme')) {
    function get_active_theme()
    {
        return JK_THEME();
    }
}

function div_container($containerClass = '', $containerID = '')
{
    return div_start('container-fluid ' . $containerClass, $containerID);
}

function div_container_close()
{
    return div_close();
}

function ajax_validate($options = [])
{
    $option = [
        "url" => '',
        "on" => '',
        "formID" => '',
        "validateFormID" => '',
        "validate" => false,
        "prevent" => false,
        "ckeditor" => false,
        "type" => 'post',
        "data" => '$(form).serialize()',
        "type_response" => 'html',
        "success_response" => '',
        "success_response_after" => '',
        "loading" => false,
        "error_swal" => true,
        "error_response" => "",
        "beforeAll" => "",
        "readyFunction" => true,
        "timeout" => 60000,
        "rules" => "{}",
    ];
    $return = '';
    if (sizeof($option) >= 1) {
        foreach ($option as $key => $opt) {
            if (isset($options[$key])) {
                $option[$key] = $options[$key];
            }
        }
    }
    if (substr($option['formID'], 0, 1) != '[') {
        $option['formID'] = "#" . $option['formID'];
    }
    if ($option['validateFormID'] == "") {
        $option['validateFormID'] = $option['formID'];
    }
    if ($option['readyFunction']) {
        $return .= '
$(document).ready(function() { ';
    }
    $return .= '
            $("' . $option['validateFormID'] . '").validate({
                onfocusout: false,
            ignore: \'.select2-search__field, .ck\', // ignore hidden fields
        errorClass: \'validation-error-label\',
        successClass: \'validation-valid-label\',
        highlight: function(element, errorClass) {
            $(element).removeClass(errorClass);
        },';

    if ($option['rules'] != "{}") {
        $return .= "rules: " . $option["rules"] . ",";
    }

    $return .= '
        unhighlight: function(element, errorClass) {
            $(element).removeClass(errorClass);
        },

        // Different components require proper error label placement
        errorPlacement: function(error, element) {

            // Styled checkboxes, radios, bootstrap switch
            if (element.parents(\'div\').hasClass("checker") || element.parents(\'div\').hasClass("choice") || element.parent().hasClass(\'bootstrap-switch-container\') ) {
                if(element.parents(\'label\').hasClass(\'checkbox-inline\') || element.parents(\'label\').hasClass(\'radio-inline\')) {
                    error.appendTo( element.parent().parent().parent().parent() );
                }
                 else {
                    error.appendTo( element.parent().parent().parent().parent().parent() );
                }
            }

            // Unstyled checkboxes, radios
            else if (element.parents(\'div\').hasClass(\'checkbox\') || element.parents(\'div\').hasClass(\'radio\')) {
                error.appendTo( element.parent().parent().parent() );
            }

            // Input with icons and Select2
            else if (element.parents(\'div\').hasClass(\'has-feedback\') || element.hasClass(\'select2-hidden-accessible\')) {
                error.appendTo( element.parent() );
            }

            // Inline checkboxes, radios
            else if (element.parents(\'label\').hasClass(\'checkbox-inline\') || element.parents(\'label\').hasClass(\'radio-inline\')) {
                error.appendTo( element.parent().parent() );
            }

            // Input group, styled file input
            else if (element.parent().hasClass(\'uploader\') || element.parents().hasClass(\'input-group\')) {
                error.appendTo( element.parent().parent() );
            }

            else {
                error.insertAfter(element);
            }
        },
        validClass: "validation-valid-label",
         success: function(label) {
            label.addClass("validation-valid-label")
        },
  submitHandler: function(form) {
';
    $return .= '
                var oldTxt="";
                                var submitBtnFind=$("' . $option['validateFormID'] . '").find("button[type=submit]");
                if(submitBtnFind){
                oldTxt=submitBtnFind.html();
                }
                ';
    if ($option['ckeditor']) {
        $return .= '

for (instance in CKEDITOR.instances) {
                    CKEDITOR.instances[instance].updateElement();
                }

                        ';
    }

    $return .= '' . $option['beforeAll'] . '';
    $return .= 'var datas=' . $option['data'] . ';';
    if ($option['loading'] != false) {
        $return .= '$("' . $option['success_response'] . '").' . $option['type_response'] . '(\'' . loading_fa($option['loading']) . '\');';
    }
    $error_response = 'swal ( "' . __("an error occurred") . '" ,  "' . __("loading page failed") . ' \n ' . $option['url'] . '" ,  "error",{
          buttons: "' . __("ok") . '!",
        });';
    if ($option['error_swal'] == false) {
        $error_response = "";
    }
    $error_response .= $option['error_response'];
    $return .= '
                        if(submitBtnFind){
                submitBtnFind.text("' . __("please wait") . '").prop("disabled",true);
                }
    $.ajax({
        url: "' . $option['url'] . '",
        type: "' . $option['type'] . '",
        timeout: ' . $option['timeout'] . ',
        data: datas,
        success: function (response) {
            $("' . $option['success_response'] . '").' . $option['type_response'] . '(response);
            ' . $option['success_response_after'] . '
                                        submitBtnFind.html(oldTxt).prop("disabled",false);
        }, error: function () {
                                            submitBtnFind.text(oldTxt).prop("disabled",false);
        ' . $error_response . '
}
    });
    ';


    $return .= '

                }
                 });

                ';
    if ($option['readyFunction']) {
        $return .= '
                 });
';
    }
    return $return;
}

function hashpass($password)
{
    $hash = password_hash($password, PASSWORD_BCRYPT, array("cost" => 10));
    // you can set the "complexity" of the hashing algorithm, it uses more CPU power but it'll be harder to crack, even though the default is already good enough
    return $hash;
}

function redirect_to_js($url = '', $timeout = 0, $withScript = true)
{
    $txt = '';
    if ($withScript) {
        $txt .= '<script>';
    }
    if ($url == "") {
        $txt .= "setTimeout(function() {
            document.location.reload();
        }, " . $timeout . ");";
    } else {
        $txt .= "setTimeout(function() {
            window.location = '" . $url . "'
        }, " . $timeout . ");";
    }

    if ($withScript) {
        $txt .= '</script>';
    }
    return $txt;
}


function is_json($string, $return_data = false, $arrayAssoc = true)
{
    $data = json_decode($string, $arrayAssoc);
    return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
}

function arrayIfEmptyZero($array = [])
{
    if (sizeof($array) == 0) {
        $array = 0;
    }
    return $array;
}

function datatable_structure($options = [])
{
    $opt = [
        "id" => "id",
        "type" => "html",
        "ajax_type" => "POST",
        "ajax_url" => "",
        "tabIndex" => "",
        "dom" => 'flrtip',
        "exportName" => 'export',
        "responsive" => true,
        "drawCallback" => "",
        "columnDefs" => [],
        "iDisplayLength" => "25",
        "lengthMenu" => '[[25, 50,100, -1], [25, 50,100, "All"]]',
        "order" => "[[ 0, 'desc' ]]",
        "columns" => [],
        "buttons" => [],
        "buttons_opt" => [
            "title" => "",
            "messageTop" => "",
            "messageSelector" => "",
            "titleSelector" => ""
        ],
        "language" => '"decimal": "",
    "emptyTable":     "' . __("no data available in table") . '",
    "info":           "' . __("showing _START_ to _END_ of _TOTAL_ entries") . '",
    "infoEmpty":      "' . __("showing 0 to 0 of 0 entries") . '",
    "infoFiltered":   "' . __("&#40; filtered from _MAX_ total entries &#41;") . '",
    "infoPostFix":    "",
    "thousands":      ",",
    "lengthMenu":     "' . __("show _MENU_ entries") . '",
    "loadingRecords":     "' . __("loading") . '...",
    "processing":     "' . __("processing") . '...",
    "search":         "' . __("search") . '",
    "zeroRecords":    "' . __("no matching records found") . '",
    "paginate": {
        "first":      "' . __("first") . '",
        "last":       "' . __("last") . '",
        "next":       "' . __("next") . '",
        "previous":   "' . __("previous") . '"
    },
    "aria": {
        "sortAscending":  ": ' . __("activate to sort column ascending") . '",
        "sortDescending": ": ' . __("activate to sort column descending") . '"
    }',
    ];

    if (sizeof($options) >= 1) {
        foreach ($options as $tr => $val) {
            if (isset($opt[$tr])) {
                $opt[$tr] = $val;
            }
        }
    }

    $typetext = '';
    if ($opt['type'] === 'ajax') {
        $typetext = '"processing": true,
        "serverSide": true,
        "ajax": {
            "url": "' . $opt['ajax_url'] . '",
            "type": "' . $opt['ajax_type'] . '"
        },';
    }
    $responsive = '"responsive": true,';
    if (empty($opt['responsive'])) {
        $responsive = '';
    }
    $lengthMenu = '';
    if ($opt['lengthMenu'] != '') {
        $lengthMenu = '"lengthMenu": ' . $opt['lengthMenu'] . ',';
    }
    $iDisplayLength = '';
    if ($opt['iDisplayLength'] != '') {
        $iDisplayLength = '"iDisplayLength": ' . $opt['iDisplayLength'] . ',';
    }
    $tabIndex = '';
    if ($opt['tabIndex'] != '') {
        $tabIndex = '"tabIndex": ' . $opt['tabIndex'] . ',';
    }
    $order = '';
    if ($opt['order'] != '') {
        $order = '"order": ' . $opt['order'] . ',';
    }

    $columns = '';
    if (is_array($opt['columns'])) {
        if (sizeof($opt['columns']) >= 1) {
            $txtcols = '[';

            foreach ($opt['columns'] as $key => $col) {
                $txtcols .= '{ "data": "' . $col . '" },';
            }
            $txtcols .= ']';
            $columns = '"columns": ' . $txtcols . ',';
        }
    }


    $columnDefs = '';
    if (is_array($opt['columnDefs'])) {
        if (sizeof($opt['columnDefs']) >= 1) {
            $txtcols = '[';

            foreach ($opt['columnDefs'] as $key => $col) {
                $txtcols .= '{ "width": "' . $col . '", "targets": ' . $key . ' },';
            }
            $txtcols .= ']';
            $columnDefs = '"columnDefs": ' . $txtcols . ',';
        }
    } else {
        $columnDefs = '"columnDefs": ' . $opt['columnDefs'] . ',';
    }


    $buttons = '';
    if (sizeof($opt['buttons']) >= 1) {

        $opt_btn = [
            "title" => "",
            "messageTop" => "",
            "messageSelector" => "",
            "titleSelector" => ""
        ];
        foreach ($opt_btn as $k => $v) {
            $opt_btn[$k] = array_key_exists($k, $opt['buttons_opt']) ? $opt['buttons_opt'][$k] : "";
        }

        $title = !empty($opt_btn["titleSelector"]) ? $opt_btn["titleSelector"] : (!empty($opt_btn["title"]) ? '"' . ($opt_btn["title"]) . '"' : "''");
        $messageTop = !empty($opt_btn["messageSelector"]) ? $opt_btn["messageSelector"] : (!empty($opt_btn["messageTop"]) ? '"' . ($opt_btn["messageTop"]) . '"' : "''");
        $buttons = "buttons: [";

        foreach ($opt['buttons'] as $button) {

            if (in_array($button, ['csv', 'excel', 'pdf', 'pdfHtml5', 'print'])) {
                $tmp = '{
                        "extend": "' . $button . '",
                        "text": "' . __("export as ") . ' ' . $button . '",
                        "filename": "' . $opt['exportName'] . '",
                        "className": "btn btn-success btn-sm",
                        "charset": "utf-8",
                        "bom": "true",
                        "title" : ' . $title . ',
                        "messageTop": ' . $messageTop . ',
                        init: function(api, node, config) {
                            $(node).removeClass("btn-default");
                        },
                        customize: function ( win ) {
                        if( win.document !== undefined )
                            $(win.document.body).css("direction", "rtl");
                        }
                    }';
                $buttons .= $tmp . ",";
            } else {
                $buttons .= "'" . $button . "',";
            }
        }
        $buttons = rtrim($buttons, ',');
        $buttons .= "],";
        $opt['dom'] = "B" . $opt['dom'];
    }

    $return = '
    
    var table=$(\'#' . $opt['id'] . '\').DataTable( {
        ' . $columnDefs . '
        ' . $typetext . '
        ' . $lengthMenu . '
        ' . $responsive . '
        ' . $iDisplayLength . '
        ' . $order . '
        ' . $columns . '
        ' . $buttons . '
        ' . $tabIndex . '
        "dom":"' . $opt['dom'] . '",
        language: {
    ' . $opt['language'] . '
},
"drawCallback": function (Settings) {
$(\'[data-popup="tooltip"]\').tooltip();
' . $opt['drawCallback'] . '
},
   } );
    
    
    
    ';

    return $return;
}

function datatable_view($options = [])
{
    $database = Database::connect();
    $opt = [
        "CountAll" => 0,
        "lists" => [],
        "data" => [],
        "otherData" => [],
    ];

    if (sizeof($options) >= 1) {
        foreach ($options as $tr => $val) {
            if (isset($opt[$tr])) {
                $opt[$tr] = $val;
            }
        }
    }
    $return = '';


    $results = array(
        "draw" => $_POST["draw"],
        "iTotalRecords" => $opt['CountAll'],
        "iTotalDisplayRecords" => $opt['CountAll'],
        "aaData" => $opt['data'],
        "otherData" => $opt['otherData'],
    );
    return json_encode($results);
}

function datatable_get_opt()
{
    $return = false;
    if (isset($_POST['columns'])) {

        $start = $_POST['start'];
        $length = $_POST['length'];

        if (!isset($_POST['search']['value']) || $_POST['search']['value'] == "") {
            $search = '';
        } else {
            $search = $_POST['search']['value'];
        }
        $orderby = null;
        $orderval = null;
        if (!empty($_POST['order'])) {
            $orderby = $_POST['columns'][$_POST['order'][0]['column']]['data'];
            $orderval = strtoupper($_POST['order'][0]['dir']);
        }
        $return = [
            "search" => $search,
            "orderby" => $orderby,
            "orderval" => $orderval,
            "start" => $start,
            "length" => $length,
        ];
    } elseif (isset($_GET['columns'])) {
        $start = $_GET['start'];
        $length = $_GET['length'];

        if (!isset($_GET['search']['value']) || $_GET['search']['value'] == "") {
            $search = '';
        } else {
            $search = $_GET['search']['value'];
        }
        $orderby = null;
        $orderval = null;
        if (!empty($_GET['order'])) {
            $orderby = $_GET['columns'][$_GET['order'][0]['column']]['data'];
            $orderval = strtoupper($_GET['order'][0]['dir']);
        }


        $return = [
            "search" => $search,
            "orderby" => $orderby,
            "orderval" => $orderval,
            "start" => $start,
            "length" => $length,
        ];
    }
    return $return;
}

function datatable_selectAll($name = "def")
{
    global $View;
    $View->footer_js('<script>
$("#datatable_checkbox_' . $name . '_selectAll").on("click",function() {
    if (this.checked) {
           $(\'input[id^=datatable_checkbox_' . $name . ']\').prop(\'checked\', true);
           }else{
            $(\'input[id^=datatable_checkbox_' . $name . ']\').prop(\'checked\', false);

           }
});
</script>');
    $html = '<input type="checkbox" class="checkbox" id="datatable_checkbox_' . $name . '_selectAll" name="datatable_checkbox_' . $name . '_selectAll"> ';

    return $html;
}

function datatable_selectCheck($id, $value, $name = "def")
{
    global $View;
    $View->footer_js('<script>
$("#datatable_checkbox_' . $name . '_' . $id . '").on("click",function() {
    if (this.checked) {
           }else{
           $(\'#datatable_checkbox_' . $name . '_selectAll\').prop(\'checked\', false);

           }
});
</script>');
    $html = '<input type="checkbox" class="checkbox" id="datatable_checkbox_' . $name . '_' . $id . '" value="' . $value . '" name="datatable_checkbox_' . $name . '_' . $id . '"> ';

    return $html;
}

function datatable_selectString($stringName, $formName = "def")
{
    $html = '
    var ' . $stringName . '="";
      $(\'input[id^=datatable_checkbox_' . $formName . ']\').each(function () {
           if (this.checked) {
               ' . $stringName . '+= $(this).val()+",";
           }
});
    ';

    return $html;
}

function alert($options = [])
{
    $opt = [
        "type" => 'info',
        "class" => 'alert text-center IRANSans alert-',
        "elem" => 'div',
        "text" => __("done"),
        "addClass" => '',
    ];

    if (sizeof($options) >= 1) {
        foreach ($options as $tr => $val) {
            if (isset($opt[$tr])) {
                $opt[$tr] = $val;
            }
        }
    }
    $butshow = '';
    $butshow_start = '';
    $butshow_end = '';
    if ($opt['elem'] != '') {
        $butshow_start .= '<' . $opt['elem'];
        if ($opt['class'] != '') {
            if ($opt['addClass'] != '') {
                $opt['class'] = $opt['addClass'] . " " . $opt['class'];
            }
            $butshow_start .= ' class="' . $opt['class'];
            if ($opt['type'] != '') {
                $butshow_start .= $opt['type'];
            }
            $butshow_start .= '" ';
        }
        $butshow_start .= '>';

        $butshow_end .= '</' . $opt['elem'] . '>';
    }

    $butshow = $butshow_start . $opt['text'] . $butshow_end;


    return $butshow;
}

function alertWarning($text = "")
{
    if ($text == "") {
        $text = __("warning");
    }
    return alert([
        "type" => "warning",
        "text" => $text,
    ]);
}

function alertSuccess($text = "")
{
    if ($text == "") {
        $text = __("success");
    }
    return alert([
        "type" => "success",
        "text" => $text,
    ]);
}

function alertDanger($text = "")
{
    if ($text == "") {
        $text = __("danger");
    }
    return alert([
        "type" => "danger",
        "text" => $text,
    ]);
}

function alertInfo($text = "")
{
    if ($text == "") {
        $text = __("info");
    }
    return alert([
        "type" => "info",
        "text" => $text,
    ]);
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin($userId)
    {
        $user = Database::connect()->get('jk_users', '*', ['id' => $userId]);
        if ($user && isset($user['superAdmin']) && $user['superAdmin']) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('includeModuleFilesWithVariables')) {
    function includeModuleFilesWithVariables($filePath, $variables = [], $print = true)
    {
        $filePath = JK_SITE_PATH() . 'modules' . DS() . $filePath . ".php";
        $output = NULL;
        if (file_exists($filePath)) {
            extract($variables);
            ob_start();
            include $filePath;
            $output = ob_get_clean();
        }
        if ($print) {
            print $output;
        }
        return $output;
    }
}


if (!function_exists('includeThemeFilesWithVariables')) {
    function includeThemeFilesWithVariables($filePath, $variables = [], $print = true)
    {
        $filePath = JK_SITE_PATH() . 'themes' . DS() . $filePath . ".php";
        $output = NULL;
        if (file_exists($filePath)) {
            extract($variables);
            ob_start();
            include $filePath;
            $output = ob_get_clean();
        }
        if ($print) {
            print $output;
        }
        return $output;
    }
}

if (!function_exists("checkArraySize")) {
    function checkArraySize($array)
    {
        if ($array && is_array($array) && sizeof($array) >= 1) {
            return true;
        }
        return false;
    }
}

function arrayIndexToKey($array, $name = 'name')
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $arr) {
            if (isset($arr[$name])) {
                $back[$arr[$name]] = $arr;
            }
        }
    }
    return $back;
}

if (!function_exists('spliteSentence')) {
    function spliteSentence($sentence, $num, $etc = true)
    {
        $sen = [];
        $ex = explode(' ', $sentence);
        for ($i = 0; $i < $num; $i++) {
            if (isset($ex[$i])) {
                $sen[$i] = $ex[$i];
            }
        }
        $sen = implode(' ', $sen);
        if ($etc) {
            if (sizeof($ex) > $num) {
                $sen .= " ...";
            }
        }
        return $sen;
    }
}

if (!function_exists('getPostThumbnail')) {
    function getPostThumbnail($id, $th)
    {
        $fileId = Database::connect()->get('jk_data_th', 'fileID', [
            'thID' => $th,
            'dataID' => $id,
            'status' => 'active',
        ]);
        if ($fileId) {
            $file = Database::connect()->get('jk_uploads', 'file', [
                'id' => $fileId
            ]);
            if ($file) {
                $ex = explode('/', $file);
                array_shift($ex);
                return implode('/', $ex);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

function fault()
{
    return alert([
        "type" => "danger",
        "text" => __("fault"),
        "elem" => "span",
        "addClass" => 'w-100'
    ]);
}

function modal_create($options = [])
{
    $option = [
        "id" => 'modal_global',
        "size" => '',
        "title" => '',
        "title-size" => 6,
        "text" => loading_fa(),
        "bg" => "",
        "return" => false,
    ];
    if (sizeof($option) >= 1) {
        foreach ($option as $key => $opt) {
            if (isset($options[$key])) {
                $option[$key] = $options[$key];
            }
        }
    }
    if ($option['size'] != '') {
        $option['size'] = 'modal-' . $option['size'];
    }
    if ($option['bg'] != '') {
        $option['bg'] = 'bg-' . $option['bg'];
    }
    $html = '<div id="' . $option['id'] . '" class="modal fade IRANSans">
        <div class="modal-dialog ' . $option['size'] . '">
            <div class="modal-content">
                <div class="modal-header ' . $option['bg'] . '">

                    <h' . $option['title-size'] . ' class="modal-title"
                                                           id="' . $option['id'] . '_title">' . $option['title'] . '</h' . $option['title-size'] . '>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="' . $option['id'] . '_body">
                    ' . $option['text'] . '
                </div>
            </div>
        </div>
    </div>';
    if ($option['return'] == false) {
        echo $html;
    } else {
        return $html;
    }
}

function NestableTableGetData($id, $table, $lang, $parent = 0, $extra_float = "", $module = 0, $extra_title = "", $buttons = true, $options = [])
{
    $database = Database::connect();

    $moduleName = 'module';
    if (isset($options['conditions'])) {
        $moduleName = $options['conditions'];
    }
    if (isset($options['sortCol'])) {
        $sortCol = $options['sortCol'];
    } else {
        $sortCol = "sort";
    }

    if ($lang == '') {
        $getCol = ['id', 'title', 'parent'];
    } else {
        $getCol = ['id', 'parent'];
    }
    $condition['AND'] = [];

    $condition['AND']['parent'] = $parent;
    $condition['AND']['status'] = "active";

    if ($module !== "") {
        $condition['AND'][$moduleName] = $module;
    }
    if (isset($options['ANDCOL']) && sizeof($options['ANDCOL']) >= 1) {
        foreach ($options['ANDCOL'] as $opK => $opV) {
            $condition['AND'][$opK] = $opV;
        }
    }
    if (isset($options['limit'])) {
        $condition['LIMIT'] = $options['limit'];
    }
    $selects = $database->select($table, $getCol, [
        "AND" => $condition['AND'],
        "ORDER" => ["parent" => "ASC", $sortCol => "ASC"]
    ]);
    if (checkArraySize($selects)) {
        ?>
    <ol class="dd-list dd-list-<?php echo JK_DIRECTION(); ?>" id="nestable_dd_list_<?php echo $id; ?>">
        <?php
        foreach ($selects as $select) {
            ?>
            <li class="dd-item dd3-item" data-id="<?php echo $select['id'] ?>">
                <?php
                if (!isset($options['doNotSortChild']) || $select['parent'] == 0) {

                    ?>
                    <div class="dd-handle dd3-handle <?php
                    if (isset($options[$select['id']]['icon'])) {
                        echo 'dd3-handle-' . $options[$select['id']]['icon'];
                    }

                    ?>"></div>
                    <?php
                }

                ?>
                <div class="dd3-content"><?php
                    $title = "";
                    if (isset($options['showIds'])) {
                        $title .= $select['id'] . '- ';
                    }

                    if (!isset($options['showTitle']) || $options['showTitle'] != false) {
                        if ($lang == "") {
                            $title .= $select['title'];
                        } else {
                            $langTitle = langDefineGet($lang, $table, 'id', $select['id']);
                            $title .= $langTitle;
                        }
                        if ($title == "") {
                            $title .= '<span class="text-muted">' . __("not defined") . '</span>';
                        }
                    }
                    if (isset($extra_title[$select['id']])) {
                        $title .= $extra_title[$select['id']] . ' ';
                    }
                    if (isset($options[$select['id']]['title'])) {
                        $title = $options[$select['id']]['title'];
                    }
                    echo $title;
                    ?>
                    <span class="float-<?php echo JK_DIRECTION_SIDE_R(); ?> mt-1">
                        <?php
                        if (isset($extra_float[$select['id']])) {
                            echo $extra_float[$select['id']];
                        }
                        if ($buttons) {
                            ?>

                            <button class="btn btn-xs btn-outline-light"
                                    onclick="nestableEdit_<?php echo $id; ?>(<?php echo $select['id']; ?>)">
                                    <i class="fal fa-edit text-info"></i>
                                </button>
                            <button class="btn btn-xs btn-outline-light"
                                    onclick="nestableRemove_<?php echo $id; ?>(<?php echo $select['id']; ?>)">
                                                            <i class="fal fa-times text-danger"></i>
                                </button>
                            <?php
                        }
                        ?>
</span>
                    <div class="clearfix"></div>
                </div>
                <?php

                if (!isset($options['getChild']) || $options['getChild'] == true) {
                    NestableTableGetData($id, $table, $lang, $select['id'], $extra_float, $module, $extra_title, $buttons, $options);
                }

                ?>
            </li>
            <?php

        }
        ?></ol><?php

    }
}

function NestableTableInitHtml($id)
{
    ?>
    <div class="dd" id="nestable_ajax_<?php echo $id; ?>"></div>
    <div id="nestable_sort_result_<?php echo $id; ?>"></div>
    <textarea title="nestable_list_ajax_output_<?php echo $id; ?>" id="nestable_list_ajax_output_<?php echo $id; ?>"
              class="d-none"></textarea>
    <?php
}

function NestableTableJS($id, $sortURL, $group = 1, $maxDepth = 3, $formID = '')
{
    $formData = '""';
    if ($formID != "") {
        $formData = '$("#' . $formID . '").serialize()';
    }
    AstCtrl::ADD_FOOTER_SCRIPTS('
<script>
    $( document ).ready(function() {
        var UINestable = function () {
            var t = function (t) {
                var e = t.length ? t : $(t.target), a = e.data("output");
                window.JSON ? a.val(window.JSON.stringify(e.nestable("serialize"))) : a.val("JSON browser support required for this demo.")
            };
            return {
                init: function () {
                    $("#nestable_ajax_' . $id . '").nestable({group: ' . $group . ',maxDepth:' . $maxDepth . '}).on("change",function(e) {
                       t($("#nestable_ajax_' . $id . '").data("output", $("#nestable_list_ajax_output_' . $id . '")));
                       ' . ajax_load([
            "url" => $sortURL,
            "success_response" => "#nestable_sort_result_" . $id,
            "data" => "{sortval:$(\"#nestable_list_ajax_output_" . $id . "\").val(),formData:$formData}",
            "loading" => [
            ]
        ]) . '
                    });
                    $("#list_ajax_menu").on("click", function (t) {
                        var e = $(t.target), a = e.data("action");
                    });
                }
            }
        }();

        UINestable.init();
    });
</script>
');
}

function NesatableUpdateSortData($table, $data, $options = [])
{
    $jde = json_decode($data, true);
    NesatableUpdateSort($table, 0, $jde, $options);
}

function NesatableUpdateSort($table, $sub, $jde, $options = [])
{
    $database = Database::connect();
    $js = 0;
    if (isset($options['sortCol'])) {
        $sortCol = $options['sortCol'];
    } else {
        $sortCol = 'sort';
    }
    foreach ($jde as $j) {
        $database->update($table, [
            "parent" => $sub,
            $sortCol => $js,
        ], [
            "id" => $j['id']
        ]);
        $js++;
        if (isset($j['children']) && is_array($j['children'])) {
            NesatableUpdateSort($table, $j['id'], $j['children'], $options = []);
        }

    }
}

function langDefineGet($lang, $table, $column, $var)
{
    $database = Database::connect();
    $back = '';
    $concatName = $lang . "_" . $table . "_" . $column . "_" . $var;
    $langCache = \Joonika\helper\Cache::get($concatName);
    if (empty($langCache)) {
        $text = $database->query("SELECT SQL_CACHE text FROM jk_lang_defined WHERE tableName = '$table' AND lang = '$lang' AND varCol = '$column' AND var = '$var' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
        if (empty($text['text'])) {
            $text = $database->query("SELECT SQL_CACHE text FROM jk_lang_defined WHERE tableName = '$table' AND lang = 'en' AND varCol = '$column' AND var = '$var' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
        }
        if (!empty($text['text'])) {
            $back = $text['text'];
        }
    } else {
        $back = $langCache;
    }
    if (!empty($back) && empty($langCache)) {
        \Joonika\helper\Cache::set($concatName, $back, 60);
    }
    return !empty($back) ? $back : '';
}

if (!function_exists('pagination')) {
    function pagination($totalCount, $currentPage = 1, $options = [], $return = false)
    {
        $option = [
            "countPerPage" => 20,
            "onClickFunctionName" => 'goToPage'
        ];
        $html = '';
        if (sizeof($option) >= 1) {
            foreach ($option as $key => $opt) {
                if (isset($options[$key])) {
                    $option[$key] = $options[$key];
                }
            }
        }
        $countPerPage = $option["countPerPage"];
        $onClickFunctionName = $option["onClickFunctionName"];
        $pages = $totalCount / $countPerPage;
        $totalPages = ceil($pages);
        $showsPage = [];
        $startFrom = 1;
        $startTo = $totalPages;
        $hasBefore = false;
        $hasAfter = false;
        $hasBtnBA = false;
        if ($totalPages >= 20) {
            $startFrom = $currentPage - 7;
            $startTo = $currentPage + 7;
            $startFrom = $startFrom <= 0 ? 1 : $startFrom;
            $startTo = $startTo >= $totalPages ? $totalPages : $startTo;
            if ($startFrom != 1) {
                $hasBefore = true;
            }
            if ($startTo != $totalPages) {
                $hasAfter = true;
            }
            $hasBtnBA = true;
        }
        $html = '<div class="">
        <nav class="">
            <ul class="pagination justify-content-center my-1">';
        if ($hasBtnBA) {
            $btnDis = '';
            if ($currentPage == 1) {
                $btnDis = 'disabled';
            }
            $html .= '<li class="page-item ' . $btnDis . '" aria-current="page">
                        <button class="page-link ' . $btnDis . '" ' . $btnDis . '
                                onclick="' . $onClickFunctionName . "(" . 1 . ")" . '">' . __("first") . '</button>
                    </li>';
            $html .= '<li class="page-item ' . $btnDis . '" aria-current="page">
                        <button class="page-link ' . $btnDis . '" ' . $btnDis . '
                                onclick="' . $onClickFunctionName . "(" . ($currentPage - 1) . ")" . '">' . __("previous") . '</button>
                    </li>';

        }


        for ($i = $startFrom; $i <= $startTo; $i++) {
            $html .= '<li class="page-item ' . ($i == $currentPage ? 'active' : '') . '" aria-current="page">
            <button class="page-link" onclick="' . $onClickFunctionName . "(" . $i . ")" . '">' . $i . ' <span
                        class="sr-only">(current)</span>
            </button>
        </li>';
        }
        if ($hasBtnBA) {
            $btnDis = '';
            if ($currentPage == $totalPages) {
                $btnDis = 'disabled';
            }
            $html .= '<li class="page-item ' . $btnDis . '" aria-current="page">
            <button class="page-link ' . $btnDis . '" ' . $btnDis . '
                    onclick="' . $onClickFunctionName . "(" . ($currentPage + 1) . ")" . '">' . __("next") . '</button>
        </li>

        <li class="page-item ' . $btnDis . '" aria-current="page">
            <button class="page-link ' . $btnDis . '" ' . $btnDis . '
                    onclick="' . $onClickFunctionName . "(" . $totalPages . ")" . '">' . __("last") . '</button>
        </li>';

        }
        $html .= '</ul>
    </nav>
    </div>';
        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }
}

function langDefineSet($lang, $table, $column, $var, $text)
{
    $database = Database::connect();
    $get = $database->get("jk_lang_defined", ["id", "text"], [
        "AND" => [
            "tableName" => $table,
            "lang" => $lang,
            "varCol" => $column,
            "var" => $var,
        ]
    ]);
    if (isset($get['text'])) {
        $database->update('jk_lang_defined', [
            "text" => $text
        ], [
            "id" => $get['id']
        ]);
    } else {
        $database->insert("jk_lang_defined", [
            "tableName" => $table,
            "lang" => $lang,
            "varCol" => $column,
            "var" => $var,
            "text" => $text
        ]);
    }
}

function langDefineSearch($lang, $table, $column, $search = null, $join = "var", $var = null)
{
    $database = Database::connect();
    $consitions = [
        "AND" => [
            "tableName" => $table,
            "lang" => $lang,
            "varCol" => $column,
        ]
    ];
    if ($var) {
        $consitions["AND"]["var"] = $var;
    }
    if ($search) {
        $consitions["AND"]["text[~]"] = $search;
    }
    $searchs = $database->select("jk_lang_defined", $join, $consitions);
    return $searchs;
}

function tab_menus($menus, $link, $pathCheck = 1, $in = null, $endLink = '', $liClass = '', $ulClass = '')
{
    $ACL = ACL::ACL();

    if (isset($in->Route->path[$pathCheck])) {
        $active = $in->Route->path[$pathCheck];
    } else {
        $active = "";
    }

    if ($menus >= 1 && sizeof($menus) >= 1) {
        ?>
        <ul class="nav nav-tabs pr-0 pl-0 <?= $ulClass ?>" role="tablist">
            <?php
            foreach ($menus as $menu) {
                $linkCheck = $menu['link'];
                if (isset($menu['name'])) {
                    $linkCheck = $menu['name'];
                }
                $continue = true;
                if (isset($menu['permissions']) && !$ACL->hasPermission($menu['permissions'], true)) {
                    $continue = false;
                }

                if (stripos($menu['link'], JK_DOMAIN_LANG()) !== false) {
                    $linkShow = $menu['link'];
                } else {
                    $linkShow = $link . $menu['link'] . $endLink;
                }

                $linkToggle = '';
                $onClick = '';
                if (isset($menu['onClick'])) {
                    $onClick = 'onclick="' . $menu['onClick'] . '"';
                    $linkShow = 'javascript:;';
                    $linkToggle = ' data-toggle="tab" role="tab" ';
                }
                $disabled = "";
                if (isset($menu['disabled']) && $menu['disabled']) {
                    $disabled = "disabled";
                }
                if ($continue) {
                    $title = !empty($menu['title']) ? $menu['title'] : $menu['name'];
                    $icon = !empty($menu['icon']) ? $menu['icon'] : '';
                    ?>
                    <li class="nav-item <?= $liClass ?>">
                        <a class="nav-link px-2 navigationLink <?php if ($active == $linkCheck) { ?>active<?php } ?> <?= $disabled ?>"
                            <?= $onClick ?> <?= $disabled ?>

                           href="<?php echo $linkShow; ?>" <?= $linkToggle ?>
                            <?= isset($menu['id']) ? 'id="' . str_replace("/", '_', $menu['id']) . '"' : '' ?>>

                            <i class="<?= $icon; ?> position-left"></i>
                            <?= $title; ?>
                        </a>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
        <?php
    }

}

function div_start($class = '', $id = '', $close = false, $html = '', $attr = "")
{
    if ($class != '') {
        $class = ' class="' . $class . '"';
    }
    if ($id != '') {
        $id = ' id="' . $id . '"';
    }
    $append = $html;
    if ($close) {
        $append .= '</div>';
    }
    return '<div' . $class . $id . $attr . ' >' . $append;
}

function div_close($after = '')
{
    return '</div>' . $after;
}

function div_container_row($containerClass = '', $rowClass = 'justify-content-md-center', $containerID = '', $rowID = '')
{
    return div_start('container-fluid ' . $containerClass, $containerID) . div_start('row ' . $rowClass, $rowID);
}

function div_container_row_close()
{
    return div_close() . div_close();
}

function clearfix()
{
    return "<div class='clearfix'></div>";
}

function hr_html()
{
    return "<hr/>";
}

function jk_options_get($name, $cache = true)
{
    $domainFileName = 'jk_options_' . $name;
    if ($cache) {
        $cacheGet = \Joonika\helper\Cache::get($domainFileName);
        if (!empty($cacheGet)) {
            return $cacheGet;
        }
    }
    $database = Database::connect();
    if (!is_null($database)) {
        $value = $database->cache()->get('jk_options', 'value', [
            "name" => $name
        ]);
        if ($cache) {
            \Joonika\helper\Cache::set($domainFileName, $value, 10);
        }
        return $value;
    } else {
        return null;
    }
}

function jk_options_set($name, $value)
{
    $database = Database::connect();
    $option = $database->get('jk_options', '*', [
        "name" => $name
    ]);
    if (!isset($option['id'])) {
        $database->insert('jk_options', [
            "name" => $name,
            "value" => $value,
        ]);
        $optionID = $database->id();
        if ($optionID >= 1) {
            $option = $database->get('jk_options', '*', [
                "id" => $optionID
            ]);
        }
    } else {
        $database->update('jk_options', [
            "value" => $value
        ], [
            "name" => $name,
        ]);
        $option = $database->get('jk_options', '*', [
            "name" => $name
        ]);
    }
    return $option['value'];

}

function emailSend($fromEmail, $toMails, $subject, $text, $options = [])
{
    $database = Database::connect();
    $email = $database->get('jk_emails', "*", [
        "email" => $fromEmail
    ]);
    if (!isset($email['id'])) {
        return 'Email ' . $fromEmail . ' Not Set';
    }
    $SMTPMailServer = $email['server'];
    $SMTPMailPort = $email['port'];
    $SMTPMailUserName = $email['username'];
    $SMTPMailPassword = $email['password'];
    $SMTPMailFromName = $email['fromName'];
    $SMTPMailSecure = $email['secureType'];
    $SMTPMailDebug = $email['debug'];

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);                              // Passing `true` enables exceptions
    try {
        //Server settings
        $mail->SMTPDebug = $SMTPMailDebug;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = $SMTPMailServer;  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $SMTPMailUserName;                 // SMTP username
        $mail->Password = $SMTPMailPassword;                           // SMTP password
        $mail->SMTPSecure = $SMTPMailSecure;                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $SMTPMailPort;                                    // TCP port to connect to
        $mail->CharSet = 'UTF-8';
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );// Set mailer to use SMTP
        //Recipients
        $mail->setFrom($fromEmail, $SMTPMailFromName);
        if (is_array($toMails)) {
            foreach ($toMails as $em) {
                if (is_array($em) && isset($em['name'])) {
                    $name = $em['name'];
                    $email2 = $em['email'];
                } else {
                    $name = $em;
                    $email2 = $name;
                }
                $mail->addAddress($email2, $name);     // Add a recipient
            }
        } else {
            $mail->addAddress($toMails);     // Add a recipient

        }

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML


        if (isset($options['BCC']) && is_array($options['BCC'])) {
            foreach ($options['BCC'] as $bcc) {
                if ($bcc != '') {
                    $mail->addBCC($bcc);
                }
            }
        }

        if (isset($options['embeded'])) {
            $mail->AddEmbeddedImage(JK_DIR_INCLUDES . 'templates' . DS() . $options['embeded'], $options['embeded']);
        }


        if (file_exists(JK_DIR_INCLUDES . 'templates' . DS() . 'emails' . DS() . 'emailTpl-' . JK_DIRECTION() . '.html')) {
            $htmlbody = file_get_contents(JK_DIR_INCLUDES . 'templates' . DS() . 'emails' . DS . 'emailTpl-' . JK_DIRECTION() . '.html');
            $htmlbody = str_replace('%{%text%}%', $text, $htmlbody);
            if (isset($options['autoSender']) && $options['autoSender'] != false) {
                $htmlbody = str_replace('%{%autosender%}%', $options['autoSender'], $htmlbody);
            } else {
                $htmlbody = str_replace('%{%autosender%}%', __("please do not reply to this email; this address is not monitored. Please use our contact page."), $htmlbody);
            }
        } else {
            $htmlbody = $text;
        }


        if (isset($options['attachments']) && is_array($options['attachments'])) {
            $attachs = $options['attachments'];
            if (sizeof($attachs) >= 1) {
                foreach ($attachs as $attach) {
                    if (isset($attach['path']) && isset($attach['name']))
                        $mail->addAttachment($attach['path'], $attach['name']);
                }
            }

        }

        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $htmlbody;
        $mail->AltBody = $text;
        $mail->Timeout = 10;

        $mail->AltBody = $text;

        $mail->send();
        return "sent";
    } catch (Exception $e) {
        return 'Message could not be sent. Mailer Error: ' . $e->getMessage();
    }
}

function emailTemplate($templateName, $lang, $arguments = [])
{
    $database = Database::connect();
    $text = "";
    $gettext = $database->get('jk_emailTemplate', "*", [
        "AND" => [
            "name" => $templateName,
            "lang" => $lang,
        ]
    ]);
    if (isset($gettext['id'])) {
        $text = $gettext['text'];
        if (sizeof($arguments) >= 1) {
            foreach ($arguments as $key => $arg) {
                $text = str_replace('[%' . $key . '%]', $arg, $text);
            }
        }
    }
    return $text;
}

function tokenGenerate($string = "")
{
    if ($string == "") {
        $string = time();
    }
    $string = md5(uniqid($string . time(), true));
    return $string;
}


function emailsArray()
{
    $database = Database::connect();
    return $database->select('jk_emails', 'email', [
    ]);
}

function ArrayKeyEqualValue($array)
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $arr) {
            $back[$arr] = $arr;
        }
    }
    return $back;
}

function statusReturnConfirmedUnconfirmed($status)
{
    switch ($status) {
        case "confirmed":
            $st = __("confirmed");;
            break;
        case "unconfirmed":
            $st = __("unconfirmed");
            break;
        default:
            $st = __("unknown");
    }
    return $st;
}

function statusReturnActiveInactive($status, $color = false)
{
    if ($color == false) {
        switch ($status) {
            case "active":
                $st = __("active");;
                break;
            case "inactive":
                $st = __("inactive");
                break;
            default:
                $st = $status;
        }
    } else {
        switch ($status) {
            case "active":
                $st = '<span class="text-success">' . __("active") . '</span>';
                break;
            case "inactive":
                $st = '<span class="text-danger">' . __("inactive") . '</span>';
                break;
            default:
                $st = $status;
        }
    }
    return $st;
}

function statusReturnYesNoInt($status, $color = false)
{
    if ($color == false) {
        switch ($status) {
            case "0":
                $st = __("no");;
                break;
            case "1":
                $st = __("yes");
                break;
            default:
                $st = $status;
        }
    } else {
        switch ($status) {
            case "0":
                $st = '<span class="text-danger">' . __("inactive") . '</span>';
                break;
            case "1":
                $st = '<span class="text-success">' . __("active") . '</span>';
                break;
            default:
                $st = $status;
        }
    }
    return $st;
}

function arrayToKey($array)
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $arr) {
            $back[$arr] = $arr;
        }
    }
    return $back;
}

function arrayTitleIDToKey($array)
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $arr) {
            $back[$arr['id']] = $arr['title'];
        }
    }
    return $back;
}

function arrayTitleIDToArray($array, $key = 'id', $value = "title")
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $arrK => $arrV) {
            array_push($back, [
                $key => $arrK,
                $value => $arrV,
            ]);
        }
    }
    return $back;
}

function arrayJustKey($array)
{
    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $key => $arr) {
            $back[$key] = $key;
        }
    }
    return $back;
}

function SelectFieldKeyValue($array)
{

    $back = [];
    if (sizeof($array) >= 1) {
        foreach ($array as $key => $arr) {
            $back[$arr] = $arr;
        }
    }
    return $back;
}

function arrayJustVal($array)
{
    $back = [];
    if (checkArraySize($array)) {
        foreach ($array as $key => $arr) {
            array_push($back, $arr);
        }
    }
    return $back;
}

function datetime_default()
{
    return date("Y/m/d H:i:s");
}

function ago_time($time_ago)
{
    $cur_time = time();
    $time_elapsed = $cur_time - $time_ago;
    $seconds = $time_elapsed;
    $minutes = round($time_elapsed / 60);
    $hours = round($time_elapsed / 3600);
    $days = round($time_elapsed / 86400);
    $weeks = round($time_elapsed / 604800);
    $months = round($time_elapsed / 2600640);
    $years = round($time_elapsed / 31207680);
// Seconds
    if ($seconds <= 60) {
        return $seconds . ' ' . __("second ago");
    } //Minutes
    else if ($minutes <= 60) {
        if ($minutes == 1) {
            return __("a minute ago");
        } else {
            return $minutes . ' ' . __("minutes ago");
        }
    } //Hours
    else if ($hours <= 24) {
        if ($hours == 1) {
            return __("an hour ago");
        } else {
            return $hours . ' ' . __("hours ago");
        }
    } //Days
    else if ($days <= 7) {
        if ($days == 1) {
            return __("yesterday");
        } else {
            return $days . ' ' . __("days ago");
        }
    } //Weeks
    else if ($weeks <= 4.3) {
        if ($weeks == 1) {
            return __("a week ago");
        } else {
            return $weeks . ' ' . __("weeks ago");
        }
    } //Months
    else if ($months <= 12) {
        if ($months == 1) {
            return __("a month ago");
        } else {
            return $months . ' ' . __("month ago");
        }
    } //Years
    else {
        if ($years == 1) {
            return __("a year ago");
        } else {
            return $years . ' ' . __("year ago");
        }
    }
}

function ago_time_minutes($time_ago)
{
    $cur_time = time();
    $time_elapsed = $cur_time - $time_ago;
    $seconds = $time_elapsed;
    $minutes = intval($time_elapsed / 60);
// Seconds
    return $minutes;
}

function passedSecond($second)
{
    $time_elapsed = $second;
    $seconds = $second;
    $minutes = round($time_elapsed / 60);
    $hours = round($time_elapsed / 3600);
// Seconds
    if ($seconds <= 60) {
        return $seconds . ' ' . __("second");
    } //Minutes
    else if ($minutes <= 60) {
        if ($minutes == 1) {
            return __("a minute");
        } else {
            return $minutes . ' ' . __("minutes");
        }
    } //Hours
    else if ($hours <= 24) {
        if ($hours == 1) {
            return __("an hour");
        } else {
            return $hours . ' ' . __("hours");
        }
    }
}

function timeCalcStart()
{
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
    return $start;
}

function timeCalcEnd()
{
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    return $time;
}

function timeCalc($start, $finish)
{
    $total_time = round(($finish - $start), 4);
    return $total_time;
}

function verifyStatus($status, $color = false)
{
    $extraText = '';
    switch ($status) {
        case 'wfc':
            $backTXT = __("waiting for confirmation");
            $backColor = "warning";
            break;
        case 'verified':
            $backTXT = __("verified");
            $backColor = "success";
            break;
        case 'blocked':
            $backTXT = __("blocked");
            $backColor = "danger";
            break;
        case 'unVerified':
            $backTXT = __("unverified");
            $backColor = "info";
            break;
        case 'defect':
            $backTXT = __("defect");
            $backColor = "primary";
            break;
        default:
            $backTXT = $status;
            $backColor = "info";
    }
    if (!$color) {
        return $backTXT;
    } else {
        return '<span class="badge badge-' . $backColor . '">' . $backTXT . '</span>';
    }
}

function tr_num_js($idKeyUp)
{
    AstCtrl::ADD_FOOTER_SCRIPTS('<script>
$("#' . $idKeyUp . '").on("keyup",function() {
    var thisVal=$(this).val();
    var persianNumbers = [/۰/g, /۱/g, /۲/g, /۳/g, /۴/g, /۵/g, /۶/g, /۷/g, /۸/g, /۹/g],
        arabicNumbers  = [/٠/g, /١/g, /٢/g, /٣/g, /٤/g, /٥/g, /٦/g, /٧/g, /٨/g, /٩/g];

  if(typeof thisVal === \'string\')
  {
    for(var i=0; i<10; i++)
    {
      thisVal = thisVal.replace(persianNumbers[i], i).replace(arabicNumbers[i], i);
    }
  }
  $(this).val(thisVal.toString());
})
</script>');
}

function englishToPersianText($text)
{
    if ($text && $text != '') {
        $per = [
            'ش', 'ذ', 'ز', 'ی', 'ث', 'ب', 'ل', 'ا', 'ه',
            'ت', 'ن', 'م', 'پ', 'د', 'خ', 'ح', 'ض', 'ق',
            'س', 'ف', 'ع', 'ر', 'ص', 'ط', 'غ', 'ظ', 'و',
            'ج', 'چ', 'ک', 'گ', 'ژ', 'آ'
        ];
        $lat = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i',
            'g', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r',
            's', 't', 'u', 'v', 'w', 'x', 'y', 'z', ',',
            '[', ']', ';', '\'', 'C', 'H'
        ];
        return in_array($text[0], $lat) ? str_replace($lat, $per, $text) : $text;
    } else {
        return null;
    }

}

function arabicToPersianText($text)
{
    $name = str_replace('ي', 'ی', $text);
    $name = str_replace('ي', 'ی', $name);
    $name = str_replace('ئ', 'ی', $name);
    $name = str_replace('ك', 'ک', $name);
    $name = preg_replace('!\s+!', ' ', $name);
    return $name;
}

if (!function_exists('requests')) {
    function requests($key = null, $val = null)
    {
        if ($_SERVER['SCRIPT_NAME'] != "dev") {
            if ($key) {
                $request = new Request();
                if (is_null($val)) {
                    return $request->$command();
                } else {
                    return $request->$command($val);
                }
            } else {
                return new Request();
            }
        }
    }
}

if (!function_exists('response')) {
    function response($code, $msg = null, $output = null, $showDataOnly = false, $type = 'json', $header = 'application/json')
    {
        if ($_SERVER['SCRIPT_NAME'] != "dev") {
            $response = new \Joonika\Response($code, $msg, $output, $showDataOnly, $type, $header);
            $response->send();
        }
    }
}

if (!function_exists('app')) {
    function app()
    {
        return [
            'JK_DOMAIN_LANG' => JK_DOMAIN_LANG(),
            'JK_HOST' => JK_HOST(),
            "JK_SITE_PATH" => JK_SITE_PATH(),
            "JK_DOMAIN" => JK_DOMAIN(),
            "JK_DOMAIN_URL" => rtrim(JK_DOMAIN(), '/'),
            "JK_DOMAIN_WOP" => JK_DOMAIN_WOP(),
            "JK_URI" => JK_URI(),
            "JK_URL" => JK_URL(),
            "JK_WEBSITE_ID" => JK_WEBSITE_ID(),
            "JK_LANG_LOCALE" => JK_LANG_LOCALE(),
            "JK_DIRECTION" => JK_DIRECTION(),
            "JK_LANG" => JK_LANG(),
            "JK_THEME" => JK_THEME(),
            "JK_DIRECTION_SIDE" => JK_DIRECTION_SIDE(),
            "JK_DIRECTION_SIDE_R" => JK_DIRECTION_SIDE_R(),
            "JK_DIRECTION_SIDE_S" => JK_DIRECTION_SIDE_S(),
            "JK_DIRECTION_DASH" => JK_DIRECTION_DASH(),
            "JK_DIRECTION_DASH_R" => JK_DIRECTION_DASH_R(),
            "JK_TITLE" => JK_TITLE(),
            "JK_DIR_MODULES" => JK_DIR_MODULES(),
            "JK_DIR_THEMES" => JK_DIR_THEMES(),
            "JK_DIR_JOONIKA" => JK_DIR_JOONIKA(),
            "JK_LOGINID" => JK_LOGINID(),
            "JK_USERID" => JK_USERID(),
            "JK_TOKENID" => JK_TOKENID(),
            "JK_ROOT_PATH_FROM_JOONIKA" => JK_ROOT_PATH_FROM_JOONIKA(),
            "JK_APP_DEBUG" => JK_APP_DEBUG(),
            "JK_WEBSITES" => JK_WEBSITES(),
            "JK_WEBSITE" => JK_WEBSITE(),
            "JK_MODULES" => JK_MODULES(),
            "JK_SERVER_TYPE" => JK_SERVER_TYPE
        ];
    }
}


if (!function_exists('env')) {
    function env($key = null)
    {
        if (checkArraySize(Route::$env)) {
            if ($key) {
                if (!empty(Route::$env[$key])) {
                    return Route::$env[$key];
                } else {
                    return null;
                }
            } else {
                return Route::$env;
            }
        } else {
            return null;
        }
    }
}


if (!function_exists('databaseInfo')) {
    function databaseInfo($site = null)
    {
        $requiredYamlFile = JK_SITE_PATH() . "config/websites/$site.yaml";
        if (file_exists($requiredYamlFile)) {
            try {
                $yamlFile = yaml_parse_file($requiredYamlFile);
                if (!empty($yamlFile['database'])) {
                    return $yamlFile['database'];
                } else {
                    return [];
                }
            } catch (\Exception $exception) {
                throw new \Exception("invalid yaml file");
            }
        } else {
            return "config file not exist";
        }
    }
}


if (!function_exists('boom')) {
    function boom($action, $data = null, $return = false)
    {
        $boom = new \Joonika\boom\events($action, $data, $return);
        return $boom;
    }
}

if (!function_exists('callControllerApi')) {
    function callControllerApi($controller, $vars, $method = 'post', $headers = [])
    {
        try {
            $devYaml = yaml_parse_file(JK_SITE_PATH() . 'config/websites/dev.yaml');
            $domain = '';
            $lang = 'en';
            $protocol = 'http://';
            if (!empty($devYaml['domain'])) {
                $domain = $devYaml['domain'];
            }
            if (empty($domain)) {
                return [
                    'success' => false,
                    "errors" => [
                        ["message" => "domain not found"]
                    ]
                ];
            }
            if (!empty($devYaml['protocol'])) {
                $protocol = $devYaml['protocol'];
            }

            if (!empty($devYaml['defaultLang'])) {
                $lang = $devYaml['defaultLang'];
            }
            $serviceUrl = $protocol . $domain . '/' . $lang . '/api/' . $controller;
            if (!empty($_SERVER['HTTP_HOST'])) {
                $request = new Request();
                $headerT = $request->headers();
                if (!empty($headerT)) {
                    $headers = array_merge($headerT, $headers);
                }
            }
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $serviceUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => strtoupper($method),
                CURLOPT_POSTFIELDS => http_build_query($vars),
            ));
            if (!empty($headers)) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            }
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return [
                    "success" => false,
                    "errors" => [
                        "message" => $err
                    ],
                ];
            }
            $isJson = is_json($response, true, true);
            if ($isJson) {
                return $isJson;
            } else {
                return [
                    "success" => false,
                    "errors" => [
                        "message" => 'data not valid->' . $response
                    ],
                ];
            }
        } catch (Exception $exception) {
            return [
                "success" => false,
                "errors" => [
                    "message" => \Joonika\Errors::exceptionString($exception)
                ],
            ];
        }
    }
}

if (!function_exists('jk_temp_get')) {
    function jk_temp_get($name, $userId = null, $companyId = null)
    {
        $database = Database::connect();
        return $database->get('jk_temp', 'value', [
            "name" => $name,
            "userId" => $userId,
            "companyId" => $companyId,
        ]);
    }
}

if (!function_exists('jk_temp_set')) {
    function jk_temp_set($name, $value = null, $expireSeconds = 3600, $userId = null, $companyId = null)
    {

        $database = Database::connect();
        $columns = [
            "value" => $value,
            "name" => $name,
            "userId" => $userId,
            "companyId" => $companyId,
            "expire" => date('Y-m-d H:i:s', time() + $expireSeconds),
        ];
        $has = $database->get('jk_temp', 'id', [
            "name" => $name,
            "userId" => $userId,
            "companyId" => $companyId,
        ]);
        if ($has) {
            $columns = [
                "value" => $value,
                "name" => $name,
                "userId" => $userId,
                "companyId" => $companyId,
                "expire" => date('Y-m-d H:i:s', time() + $expireSeconds),
            ];
            $database->update('jk_temp', $columns, [
                "id" => $has
            ]);
        } else {
            $database->insert('jk_temp', $columns);
        }
    }
}