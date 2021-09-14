<?php


namespace Modules\joonika\cronjobs;


use Medoo\Medoo;

class CronJobs extends \Joonika\CronJobs
{
    public function init()
    {
        self::setCronFunction('joonika', 'removeExpiredTemp', '*/5 * * * *', __CLASS__);
        self::setCronFunction('joonika', 'flushCache', '*/15 * * * *', __CLASS__);
    }

    public function removeExpiredTemp()
    {
        $database = \Joonika\Database::connect();
        $database->delete('jk.jk_temp', [
            "expireDate[<=]" => now(),
        ]);
        return true;
    }

    public function flushCache()
    {
        \Joonika\helper\Cache::clear();
    }
}