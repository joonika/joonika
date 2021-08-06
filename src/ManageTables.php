<?php

namespace Joonika;


use function Composer\Autoload\includeFile;

class ManageTables
{

    use \Joonika\Traits\Database;
    private static $moduleTables = null;
    public static $queryResult = true;

    private function __construct($query = null)
    {
        $this->database = Database::connect(self::$DatabaseInfo);
        $this->database->query($query)->execute();
    }

    public static function createAllJkTables($databaseInfo = null)
    {
        self::$DatabaseInfo = $databaseInfo;
        if (FS::isExistIsFile(__DIR__ . './database/database.sql')) {
            $query = FS::fileGetContent(__DIR__ . './database/database.sql');
            if (!self::execDataBaseQuery($query)) {
                self::$queryResult = false;
            }
            if (self::$queryResult) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function execDataBaseQuery($query)
    {
        return new ManageTables($query);
    }
    
    public static function moduleTablesExecute($name, $databaseInfo = null, $update = false)
    {
        self::$DatabaseInfo = $databaseInfo;
        $dbClass = "Modules\\" . $name . "\database\Database";
        if (class_exists($dbClass)) {
            $dbClass = new $dbClass();
            if ($update) {
                $method = 'updateTables';
            } else {
                $method = 'installTables';
            }
            if (method_exists($dbClass, $method)) {
                $dbClass->$method();
                $queries = $dbClass->getQuerires();
                if (checkArraySize($queries)) {
                    foreach ($queries as $query) {
                        if (!self::execDataBaseQuery($query)) {
                            self::$queryResult = false;
                        }
                        if (self::$queryResult) {
                            return [
                                'status' => true
                            ];
                        } else {
                            return [
                                'status' => false,
                                'msg' => 'create tables failed! please try again...'
                            ];
                        }
                    }
                } else {
                    return [
                        'status' => false,
                        'msg' => 'there is not any query to execute.'
                    ];
                }
            } else {
                return [
                    'status' => false,
                    'msg' => 'installTables is not in your Database class.'
                ];
            }
        } else {
            return [
                'status' => false,
                'msg' => 'database.sql in database directory not find ! , you must create database directory in your module, this directory must have database.sql file'
            ];
        }
    }
}
