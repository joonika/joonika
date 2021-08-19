<?php

namespace Joonika;

use Joonika\JMedoo;

use Medoo\Medoo;
use PDO;

class Database
{
    public $database;
    protected $is_connected;
    private static $instance = [];
    public static $instanceDuration = [];
    private static $dbNameSelected = "";

    public static function getDbConfig()
    {
        $dbList = null;
        foreach (JK_WEBSITES() as $WEBSITE) {
            if (JK_WEBSITE_ID() == $WEBSITE['id']) {
                if (!empty($WEBSITE['database'])) {
                    $mainDb = [
                        'db' => $WEBSITE['database']['db'],
                        'host' => $WEBSITE['database']['host'],
                        'user' => $WEBSITE['database']['user'],
                        'pass' => $WEBSITE['database']['pass'],
                        'port' => $WEBSITE['database']['port'],
                        'charset' => $WEBSITE['database']['charset'],
                        'driver' => $WEBSITE['database']['driver'],
                    ];
                    $dbList = [];
                    $dbList['main'] = $mainDb;
                    $entire = [];

                    if (!empty($WEBSITE['database']['other'])) {
                        foreach ($WEBSITE['database']['other'] as $otherK => $otherV) {
                            if (gettype($otherV) == 'array') {
                                $entire = array_merge($mainDb, $otherV);
                                $dbList[$otherV['db']] = $entire;
                            } else {
                                $entire['db'] = $otherV;
                                $entire = array_merge($mainDb, $entire);
                                $dbList[$otherK] = $entire;
                            }
                        }
                    }
                    break;
                }
            }
        }
        return $dbList;
    }

    public static function getDbNameSelected(){
        return self::$dbNameSelected;
    }

    private function __construct($info)
    {
        try {
            self::$dbNameSelected=$info['db'];
            $database = new JMedoo([
                // required
                'type' => $info['driver'],
                'host' => $info['host'],
                'database' => $info['db'],
                'username' => $info['user'],
                'password' => $info['pass'],
                // [optional]
                'charset' => $info['charset'],
                'port' => $info['port'],
                // [optional] Enable logging (Logging is disabled by default for better performance)
                'logging' => $info['logging']??false,
                // [optional] MySQL socket (shouldn't be used with server and port)
                'option' => [
                    PDO::ATTR_CASE => PDO::CASE_NATURAL,
                    PDO::ATTR_TIMEOUT => $info['connectTimeout'] ?? 10, // in seconds
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ],
                'error' => PDO::ERRMODE_EXCEPTION,

                // [optional] Medoo will execute those commands after connected to the database for initialization
                'command' => [
                    'SET SQL_MODE=ANSI_QUOTES'
                ]
            ]);
            self::$instance[$info['db']] = $database;
            self::$instanceDuration[$info['db']]=[];
        } catch (\PDOException $exception) {
            self::$instance[$info['db']] = false;
            throw new \PDOException($exception);
        }
    }

    public static function connect($dbName = null)
    {
        if (empty($dbName['db'])) {
            $db = JK_WEBSITE()['database'];
        } else {
            $db = $dbName;
        }
        $dbName = $db['db'];
        if (!isset(self::$instance[$dbName])) {
            new Database($db);
        }
        return self::$instance[$dbName];
    }

    private static function getInstance($str = null, $type = 'db')
    {
        if (gettype($str) == 'string') {
            $dbName = null;
            if (strpos($str, '.') !== false) {
                $strArray = explode('.', $str);
                $dbName = $strArray[0];
                if ($type == 'db') {
                    return self::connect($dbName);
                } elseif ($type == 'tableName')
                    return $strArray[1];
                elseif ($type == 'dbName')
                    return $dbName;
            } else {
                if ($type == 'dbName') {
                    return $str;
                } elseif ($type == 'tableName') {
                    return 'badArgument';
                } elseif ($type == 'db') {
                    return self::connect($str);
                }
            }
        }
        return self::connect($str);
    }

    public static function get($table, $join = null, $columns = null, $where = null)
    {
//        $numArgs = func_num_args();
//        if ($numArgs == 4) {
//            //check join table instance
////            foreach ($join as $k => $v) {
////                preg_match('/(\[(?<join>\<\>?|\>\<?)\])?(?<table>[a-zA-Z0-9_.]+)\s?(\((?<alias>[a-zA-Z0-9_]+)\))?/', $k, $match);
////                self::getInstance($match['table']);
////            }
//        }
        return self::getInstance($table)->get($table, $join, $columns, $where);
    }

    public static function select($table, $join, $columns = null, $where = null)
    {
        return self::getInstance($table)->select($table, $join, $columns, $where);
    }

    public static function update($table, $data, $where = null)
    {
        return self::getInstance($table)->update($table, $data, $where);
    }

    public static function delete($table, $where)
    {
        return self::getInstance($table)->delete($table, $where);
    }

    public static function debug($db = null)
    {
        return self::getInstance($db)->debug();
    }

    public static function info($db = null)
    {
        return self::getInstance($db)->info();
    }

    public static function query($query, $map = [], $db = null)
    {
        return self::getInstance($db)->query($query, $map);
    }

    public static function queryAndFetch($query, $map = [], $db = null)
    {
        return self::getInstance($db, 'db')->queryAndFetch($query, $map);
    }

    public static function log($db = null)
    {
        return self::getInstance($db)->log();
    }

    public static function id($db = null)
    {
        return self::getInstance($db)->id();
    }

    public static function last($db = null)
    {
        return self::getInstance($db)->last();
    }

    public static function insert($table, $data, $db = null)
    {
        return self::getInstance($db)->insert($table, $data);
    }

    public static function count($table, $join = null, $column = null, $where = null, $db = null)
    {
        return self::getInstance($db)->count($table, $join, $column, $where);
    }

    public static function error($db = null)
    {
        return self::getInstance($db)->error;
    }

    public static function drop($table, $db = null)
    {
        return self::getInstance($db)->drop($table);
    }

    public static function exec($query, $map = [], $db = null)
    {
        return self::getInstance($db)->exec($query, $map);
    }

    public static function create($table, $columns, $options = null, $db = null)
    {
        return self::getInstance($db)->create($table, $columns, $options);
    }

    public static function action($actions, $db = null)
    {
        return self::getInstance($db)->action($actions);
    }

    public static function raw($string, $map = [], $db = null)
    {
        return self::getInstance($db)->raw($string, $map);
    }

    public static function min($table, $join, $column = null, $where = null, $db = null)
    {
        return self::getInstance($db)->min($table, $join, $column, $where);
    }

    public static function max($table, $join, $column = null, $where = null, $db = null)
    {
        return self::getInstance($db)->max($table, $join, $column, $where);
    }

    public static function avg($table, $join, $column = null, $where = null, $db = null)
    {
        return self::getInstance($db)->avg($table, $join, $column, $where);
    }

    public static function rand($table, $join = null, $columns = null, $where = null, $db = null)
    {
        return self::getInstance($db)->rand($table, $join, $columns, $where);
    }

    public static function has($table, $join, $where = null, $db = null)
    {
        return self::getInstance($db)->has($table, $join, $where);
    }

    public static function replace($table, $columns, $where = null, $db = null)
    {
        return self::getInstance($db)->replace($table, $columns, $where);
    }

    public static function sum($table, $join, $column = null, $where = null, $db = null)
    {
        return self::getInstance($db)->sum($table, $join, $column, $where);
    }
}