<?php


namespace Joonika;


use Joonika\templates\redirects\RedirectTemplate;

class Redirect
{
    protected static function error($code, $return = true, $msg = '', $extraCode = '')
    {
        http_response_code($code);
        if ($return) {
            self::template($code, $msg, $extraCode);
        }
    }

    protected static function defaultCodeTemplate($code, $msg = '', $extraCode = '')
    {
        templateRenderSimpleAlert($code, $msg, '', $extraCode);
    }

    protected static function template($code, $msg = '', $extraCode = '')
    {

        $method = 'template' . $code;
        $getRedirectPage = new RedirectTemplate();
        $activeTheme = $getRedirectPage->activeTheme;
        $errorPageInModule = '';
        $Route = Route::$instance;
        $error = "error" . $code;
        if ($Route->isApi) {
            echo json_encode([
                "success" => false,
                "errors" => [
                    [
                        "message" => __("you are not logged in")
                    ]
                ],
            ]);
            exit();
        } elseif (is_object($Route) && !empty($Route->View->$error)) {
            if (FS::isExistIsFile($Route->View->$error)) {
                $output = NULL;
                extract(['This' => $Route->View]);
                ob_start();
                include $Route->View->$error;
                $output = ob_get_clean();
                echo $output;
            } else {
                self::defaultCodeTemplate($code, $msg, $extraCode);
            }
        } elseif ($activeTheme && empty($Route->mainModule)) {

            $path = JK_SITE_PATH() . "themes" . DS() . $activeTheme . DS() . 'partials' . DS() . 'pages' . DS() . 'errors' . DS() . $code . '.php';
            $path2 = JK_SITE_PATH() . "themes" . DS() . $activeTheme . DS() . 'Views' . DS() . 'code_' . $code . '.twig';
            if (FS::isExistIsFile($path) || FS::isExistIsFile($path2)) {
                include_once $path;
            } else {
                self::defaultCodeTemplate($code, $msg, $extraCode);
            }
        } else {
            self::defaultCodeTemplate($code, $msg, $extraCode);
        }
        die('');
    }

    public static function error403($return = true)
    {
        self::error(403, $return);
        die('');
    }

    public static function login()
    {
        redirect_to(url('cp/main/login'));
    }

    public static function code($code, $msg = '', $extraCode = "")
    {
        http_response_code($code);
        return self::error($code, true, $msg, $extraCode);
    }

    public static function url($url)
    {
        redirect_to($url);
    }
}