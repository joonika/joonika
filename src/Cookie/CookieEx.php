<?php


namespace Joonika\Cookie;


class CookieEx extends \Symfony\Component\HttpFoundation\Cookie
{
    public $crypt = false;

    public function __construct(string $name, string $value = null, $expire = 0, string $path = '/', string $domain = null, bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = 'lax', $crypt = false)
    {
        if ($crypt) {
            $this->crypt = true;
        }
        parent::__construct($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    public function make()
    {
        if ($this->crypt) {
            $cookieEncryptKey = !empty(JK_WEBSITE()['cookieEncryptKey']) ? JK_WEBSITE()['cookieEncryptKey'] : 'cookieEncryptKey';
            if ($cookieEncryptKey && strlen($cookieEncryptKey) > 0) {
                $this->value = $this->encrypt_decrypt('encrypt', $this->value);
            } else {
                return __('please set key for encrypt cookie');
            }
        }
        setcookie($this->name, $this->value, $this->expire, $this->path, $this->domain, $this->secure, $this->httpOnly);
        return $this;
    }

    private function createPasswordForHash($key)
    {
        return substr(hash('sha256', $key, true), 0, 32);
    }

    public function get()
    {


        if (in_array($this->name, array_keys($_COOKIE))) {
//            jdie($this->crypt);
            if ($this->crypt) {
                $cookieEncryptKey = !empty(JK_WEBSITE()['cookieEncryptKey']) ? JK_WEBSITE()['cookieEncryptKey'] : 'cookieEncryptKey';
                if ($cookieEncryptKey && strlen($cookieEncryptKey) > 0) {
                    return $this->encrypt_decrypt('decrypt', $_COOKIE[$this->name]);
                } else {
                    return __('please set key for encrypt cookie');
                }
            }
            return $_COOKIE[$this->name];
        } else {
            return sprintf('%s not set as cookie', $this->name);
        }
    }

    public function encrypt_decrypt($action, $string)
    {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = !empty(JK_WEBSITE()['cookieEncryptKey']) ? JK_WEBSITE()['cookieEncryptKey'] : 'cookieEncryptKey';
        if ($secret_key && strlen($secret_key) > 0) {
            $secret_iv = 'lbkwlrwe0hbkjpsfbsfjse4rtgtsfdsgsd';
            // hash
            $key = hash('sha256', $secret_key);
            // iv - encrypt method AES-256-CBC expects 16 bytes
            $iv = substr(hash('sha256', $secret_iv), 0, 16);
            if ($action == 'encrypt') {
                $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
                $output = base64_encode($output);
            } else if ($action == 'decrypt') {
                $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }
            return $output;
        } else {
            return __('please set key for encrypt cookie');
        }
    }

}