<?php


namespace Joonika\helper;


use Config\SiteConfigs;
use Joonika\Database;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\Config;
use Phpfastcache\Core\phpFastCache;

class Cache
{
    private static $instance = null;
    private static $CachedString = null;

    private function __construct()
    {

        $JK_CACHE_SETTING = !empty(JK_WEBSITE()['cacheSetting']) ? JK_WEBSITE()['cacheSetting'] : [
            "path"=>JK_SITE_PATH().'storage/private/',
            "driver"=>'Files',
        ];
        $JK_CACHE_SETTING['path']=!empty($JK_CACHE_SETTING['path'])?$JK_CACHE_SETTING['path']:(JK_SITE_PATH().'storage/private/');
        $JK_CACHE_SETTING['driver']=!empty($JK_CACHE_SETTING['driver'])?$JK_CACHE_SETTING['driver']:'Files';
        CacheManager::setDefaultConfig(new Config([
            "path" => $JK_CACHE_SETTING['path'],
            "itemDetailedDate" => false
        ]));
        static::$instance = CacheManager::getInstance($JK_CACHE_SETTING['driver']);
    }

    private static function start()
    {
        if (static::$instance == null) {
            new Cache();
        }
        return static::$instance;
    }

    private static function init($key)
    {
        self::start();
        $CachedString = static::$instance->getItem($key);
        return $CachedString;
    }

    public static function set($key, $value, $time = 60)
    {
        $CachedString = static::init($key);
        if (is_null($CachedString->get())) {
            $CachedString->set($value)->expiresAfter($time);
            static::$instance->save($CachedString);
        }
    }

    public static function get($key)
    {
        $CachedString = static::init($key);
        return $CachedString->get($key);
    }

    public static function getItemsAsJsonString(array $keys)
    {
        self::start();
        return static::$instance->getItemsAsJsonString($keys);
    }

    public static function clear()
    {
        self::start();
        return static::$instance->clear();
    }

    public static function deleteItem($keys)
    {
        self::start();
        if (is_string($keys)) {
            return static::$instance->deleteItem($keys);
        } elseif (is_array($keys)) {
            return static::$instance->deleteItems($keys);
        }
    }

    public static function getDriverName()
    {
        self::start();
        return static::$instance->getDriverName();
    }

    public static function getHelp()
    {
        self::start();
        return static::$instance->getHelp();
    }


}