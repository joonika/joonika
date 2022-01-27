<?php


namespace Joonika\helper;


use Config\SiteConfigs;
use Joonika\Database;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\Config;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Core\phpFastCache;
use Phpfastcache\Drivers\Redis\Driver;

class Cache
{
    private static $instance = null;
    private static $CachedString = null;

    private function __construct()
    {

        if (empty(JK_WEBSITE()['cacheSetting'])) {
            $JK_CACHE_SETTING['path'] = JK_SITE_PATH() . 'storage/private/';
            $JK_CACHE_SETTING['driver'] = 'Files';
        } else {
            $JK_CACHE_SETTING = JK_WEBSITE()['cacheSetting'];
        }
        $driver = $JK_CACHE_SETTING['driver'];
        unset($JK_CACHE_SETTING['driver']);

        if ($driver == "Files") {
            $JK_CACHE_SETTING['itemDetailedDate'] = !empty($JK_CACHE_SETTING['itemDetailedDate']) ? $JK_CACHE_SETTING['itemDetailedDate'] : false;
            $config = new \Phpfastcache\Drivers\Files\Config();
            $config->setItemDetailedDate($JK_CACHE_SETTING['itemDetailedDate']);
            $config->setPath($JK_CACHE_SETTING['path']);
        } elseif ($driver == "redis") {
            $config = new \Phpfastcache\Drivers\Redis\Config();
            $config->setHost($JK_CACHE_SETTING['host']);
            $config->setPort($JK_CACHE_SETTING['port']);
        }
        static::$instance = CacheManager::getInstance($driver, $config);
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
        try {
            $CachedString = static::init($key);
            if (is_null($CachedString->get())) {
                $CachedString->set($value)->expiresAfter($time);
                static::$instance->save($CachedString);
            }
        } catch (\Exception $exception) {
        }
    }

    public static function get($key)
    {
        try {
            $CachedString = static::init($key);
            return $CachedString->get($key);
        } catch (\Exception $exception) {
            return false;
        }
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