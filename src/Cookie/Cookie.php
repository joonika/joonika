<?php


namespace Joonika\Cookie;


class Cookie
{
    /**
     * Create a new cookie instance.
     *
     * @param string $name
     * @param string $value
     * @param int $minutes
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     * @return bool true
     * @static
     */
    public static function make($name, $value, $minutes = 0, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = 'Lax')
    {
        return (new CookieBase())->make($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * Create a new cookie instance.
     *
     * @param string $name
     * @param string $value
     * @param int $minutes
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     * @return bool true
     * @static
     */
    public static function makeAndCrypt($name, $value, $minutes = 0, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
    {
        return (new CookieBase())->make($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite, true);
    }

    /**
     * Create a cookie that lasts "forever" (five years).
     *
     * @param string $name
     * @param string $value
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     * @return \Symfony\Component\HttpFoundation\Cookie
     * @static
     */
    public static function forever($name, $value, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
    {
        return self::make($name, $value, 2628000, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * Expire the given cookie.
     *
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @return \Symfony\Component\HttpFoundation\Cookie
     * @static
     */
    public static function forget($name, $path = null, $domain = null)
    {
        return self::make($name, null, -2628000, $path, $domain);
    }

    /**
     * Check has cookie.
     *
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @return \Symfony\Component\HttpFoundation\Cookie
     * @static
     */
    public static function has($name, $valid = true)
    {
        if (in_array($name, array_keys($_COOKIE))) {
            return true;
        } else {
            return false;
        }
    }

    public static function get($name)
    {
        return (new CookieBase())->get($name);
    }

    public static function getAndDecrypt($name)
    {
        return (new CookieBase())->get($name, true);
    }
}