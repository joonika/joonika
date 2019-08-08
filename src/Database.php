<?php

namespace Joonika;
if(!defined('jk')) die('Access Not Allowed !');

use Medoo\Medoo;
use PDO;

class Database
{
    protected $database;
    protected $is_connected;

    public static function connect()
    {
        global $DB_DATABASE;
        try {
            $database = new Medoo([
                // required
                'database_type' => JK_DB_DRIVER,
                'server' => JK_DB_HOST,
                'database_name' => JK_DB_DATABASE,
                'username' => JK_DB_USERNAME,
                'password' => JK_DB_PASSWORD,
                // [optional]
                'charset' => JK_DB_CHARSET,
                'port' => JK_DB_PORT,
                // [optional] Enable logging (Logging is disabled by default for better performance)
                'logging' => false,
                // [optional] MySQL socket (shouldn't be used with server and port)
                'option' => [
                    PDO::ATTR_CASE => PDO::CASE_NATURAL
                ],
                // [optional] Medoo will execute those commands after connected to the database for initialization
                'command' => [
                    'SET SQL_MODE=ANSI_QUOTES'
                ]
            ]);
            return $database;
        } catch (\PDOException $exception) {
            throw new \PDOException($exception);
        }
    }

}