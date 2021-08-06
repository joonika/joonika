<?php


namespace Joonika\Session;


use Joonika\Seeder\Faker;

class Session
{

    public static function get($key, $default = null)
    {
        $res = (new Store())->get($_SESSION, $key);
        return $res;
    }

    public static function put($key, $val)
    {
        (new Store())->undot($key, $val);
        return true;
    }

    public static function save()
    {
        return (new Store())->save();
    }

    public static function pull($key)
    {
        return (new Store())->pull($_SESSION, $key);
    }

    public static function forget($key)
    {
        (new Store())->forget($_SESSION, $key);
        return true;
    }

    public static function where(callable $callback)
    {
        return (new Store())->where($callback);
    }

    public static function has($keys)
    {
        return (new Store())->has($_SESSION, $keys);
    }

    public static function all()
    {
        return $_SESSION;
    }
}