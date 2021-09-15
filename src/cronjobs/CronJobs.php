<?php


namespace Modules\joonika\cronjobs;


use Medoo\Medoo;

class CronJobs extends \Joonika\CronJobs
{
    public function init()
    {
        if (JK_SERVER_TYPE == 'main') {
            self::setCronFunction('joonika', 'deleteExpired', '*/5 * * * *', __CLASS__);
        }
    }

    public function removeExpired()
    {
        $database = \Joonika\Database::connect();
        $database->delete('jk.jk_temp', [
            "expireDate[<=]" => now(),
        ]);
        return true;
    }
}