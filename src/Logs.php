<?php


namespace Joonika;


use Joonika\Modules\Users\Users;
use function Joonika\Idate\date_int;

class Logs extends Controller
{

    private static $table = null;

    private static function getModuleName($logTable = null)
    {
        $module = '';
        if (isset(self::$route)) {
            $modules = self::$route->modules;
            if (isset(self::$route->path[0]) && in_array(self::$route->path[0], $modules)) {
                $module = self::$route->path[0];
            }
        } else {
            $module = $logTable;
        }
        return $module;
    }

    private static function getTable($table = null)
    {
        if ($table) {
            self::$table = $table;
        } elseif (is_null(self::$table)) {
            self::$table = self::getModuleName() . '_logs';
        }
    }

    private static function insertInDb($options)
    {
        $from = null;
        if (isset($options['from'])) {
            $from = $options['from'];
            if (is_array($from)) {
                $from = json_encode($options['from'], JSON_UNESCAPED_UNICODE);
            }
        }
        $to = null;
        if (isset($options['to'])) {
            $to = $options['to'];
            if (is_array($to)) {
                $to = json_encode($options['to'], JSON_UNESCAPED_UNICODE);
            }
        }

        $options = [
            "event" => isset($options['event']) ? $options['event'] : null,
            "companyId" => isset($options['companyId']) ? $options['companyId'] : null,
            "loginId" => isset($options['loginId']) ? $options['loginId'] : JK_USERID(),
            "websiteId" => isset($options['websiteId']) ? $options['websiteId'] : JK_WEBSITE_ID(),
            "tableName" => isset($options['tableName']) ? $options['tableName'] : null,
            "columnName" => isset($options['columnName']) ? $options['columnName'] : null,
            "module" => isset($options['module']) ? $options['module'] : null,
            "row" => isset($options['row']) ? $options['row'] : null,
            "description" => isset($options['description']) ? $options['description'] : null,
            "check" => null,
            "global" => isset($options['global']) ? $options['global'] : null,
            "from" => $from,
            "to" => $to,
            "createdAt" => now(),
            "createdBy" => JK_USERID(),
        ];
        $database = Database::connect();
        $database->insert(self::$table, $options);
    }

    public static function insert($options = [], $logTable = null)
    {
        self::getTable($logTable);
        self::insertInDb($options);
    }

    public static function setTable($table)
    {
        self::$table = $table;
    }

    public static function delete($options = [], $logTable = null)
    {
        self::getTable($logTable);
        self::insertInDb($options);
    }

    public static function update($options = [], $logTable = null)
    {
        self::getTable($logTable);
        self::insertInDb($options);
    }

    public static function __callStatic($name, $arguments)
    {
        $table = '';
        $conditions = null;
        if (sizeof($arguments) > 0) {
            if (sizeof($arguments) == 1) {
                $conditions = $arguments[0];
            } elseif (sizeof($arguments) == 2) {
                $conditions = $arguments[0];
                $table = $arguments[1];
            }
        }
        if (is_string($conditions)) {
            $conditions = [
                'type' => $conditions,
                'module' => $name
            ];
        }
        $conditions['module'] = $name;
        self::$table = $table ? $table : self::getTable();
        $result = Database::connect()->select(self::$table, '*', $conditions);
        return $result;
    }


}