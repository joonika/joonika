<?php


namespace Joonika;


use phpDocumentor\Reflection\Types\Self_;

class CSRF
{
    private static $csrf_token;
    private static $csrf_token_time;
    private static $error = [];

    /**
     * CSRF constructor.
     */
    public function __construct()
    {
        $length = 32;
        self::$csrf_token = uniqid(rand(10000, 99999)) . substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length) . uniqid(rand(10000, 99999));
        $_SESSION['csrfToken'] = self::$csrf_token;
        self::$csrf_token_time = time() + 600;
        $_SESSION['csrfToken_expire'] = self::$csrf_token_time;
    }

    /**
     * @return bool
     */
    public static function checkCsrfToken()
    {

        if (isset($_SESSION['csrfToken']) && $_SESSION['csrfToken'] == '' && isset($_POST['csrfToken']) && $_POST['csrfToken'] != '') {
//        if (true) {
//            if (true) {
            if (($_POST['csrfToken'] == $_SESSION['csrfToken'])) {
                if (time() < $_SESSION['csrfToken_expire']) {
                    return true;
                } else {
                    self::$error[] = 'csrf token is expired';
                }
            } else {
                self::$error[] = 'csrf token is invalid';
            }
        } else {
            self::$error[] = 'csrf token is invalid';
        }
        if (sizeof(Self::$error) > 0) {
            return false;
        }
    }

    /**
     * @return string
     */
    public static function getCsrfToken()
    {
        if (isset($_SESSION['csrfToken']) && $_SESSION['csrfToken'] != "") {
            return $_SESSION['csrfToken'];
        } else {
            return null;
        }
    }
}