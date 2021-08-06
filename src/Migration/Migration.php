<?php

namespace Joonika\Migration;

use Joonika\Database;
use Joonika\dev\db;

class Migration
{
    protected $tableName;
    public $output = [];

    public function __construct($initType = 'up', $variables = [], $module = '')
    {
        if (!$this->init()) {
            $this->output[] = ["type" => "danger", "msg" => 'the selected option has no valid structure for execution'];
        } else {
            foreach ($this->init() as $tables => $data) {
                $tables = self::tableQuote($tables);
                $schemaExplode = explode('.', $tables);
                $schemaName = $schemaExplode[0];
                $tableName = $schemaExplode[1];
                $tableFullName = $tableName;
                if (!empty($schemaName)) {
                    $tableFullName = $schemaName . '.' . $tableName;
                }

                $tableVersion = !empty($data['version']) ? $data['version'] : 1;
                $commentTmp = !empty($data['comment']) ? $data['comment'] : '';

                $tableComment = 'version: ' . $tableVersion . "\n";
                $tableComment .= 'module: ' . $module . "\n";
                $tableComment .= 'comment: ' . $commentTmp . "\n";

                $type = 'success';
                $output = 'Table: ' . $tables . " | ";
                $indexesAllowRun = false;
                $validatedParams = [];
                $indexes_new = [];
                $indexes_new_primary = [];
                $indexes_new_unique = [];
                $columns = !empty($data['columns']) ? $data['columns'] : [];
                $indexes = !empty($data['indexes']) ? $data['indexes'] : [];
                if (!empty($columns)) {
                    foreach ($columns as $column => $params) {
                        $parse = $this->requestParser($params);
                        if (in_array('PRIMARY KEY', $parse)) {
                            array_push($indexes_new_primary, $column);
                        }
                        $validatedParams[$column] = $parse;
                    }
                }
                if (!empty($indexes)) {
                    foreach ($indexes as $index) {
                        $iname = '';
                        preg_match('/(?<alias>[a-zA-Z0-9_]+)\)/i', $index, $m);
                        $alias = !empty($m['alias']) ? $m['alias'] : '';
                        $iname = str_replace('(' . $alias . ')', '', $index);
                        $iname = str_replace(' ', '', $iname);
                        if ($alias == 'unique') {
                            $indexes_new_unique[] = $iname;
                        } else {
                            $indexes_new[] = $iname;
                        }
                    }
                }
                if ($initType == 'up') {
                    $database = Database::connect();
                    $dbt = $database->query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '$schemaName' AND table_name = '" . $tableName . "' LIMIT 1;")->fetch(\PDO::FETCH_COLUMN);
                    if (!empty($dbt)) {

                        if (!empty($variables['options']['force'])) {
                            $output .= 'table need restructure : ' . "\n";
                            $tableToDescribe = $database->query('DESCRIBE ' . $tableFullName)->fetchAll(\PDO::FETCH_ASSOC);
                            $queries = $this->tableOldDescribe($tableFullName, $validatedParams, $tableToDescribe);
                            if (empty($queries)) {
                                $output .= "table has no changes";
                                $type = 'success';
                            } else {
                                $type = 'success';
                                $qR = 0;
                                foreach ($queries as $tb) {
                                    $qR += 1;
                                    $database->query($tb);
                                    $databaseError = $database->error;
                                    if (!empty($databaseError[0]) && $databaseError[0] != '00000') {
                                        $type = 'danger';
                                        $output .= $tableFullName . " -> failed query " . $qR . ": ";
                                        if (!empty($databaseError[2])) {
                                            $output .= $databaseError[2];
                                        } else {
                                            $output .= "The conditions not following the rules";
                                        }
                                    } else {
                                        $output .= $tableFullName . " -> success query " . $qR . ": run";
                                    }
                                    $output .= "\n";
                                }
                            }
                            $indexesAllowRun = true;
                        } else {
                            $tableInfo = $database->query("SELECT table_comment 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE table_schema='$schemaName' 
        AND table_name='" . $tableName . "';")->fetch(\PDO::FETCH_ASSOC);
                            $checkChanges = false;
                            $type = 'danger';
                            $version = !empty($data['version']) ? $data['version'] : 1;

                            if (isset($tableInfo['table_comment'])) {
                                $arrayInfo = [];
                                $rows = explode("\n", $tableInfo['table_comment']);

                                foreach ($rows as $row) {
                                    $infoRow = explode(":", $row);
                                    if (sizeof($infoRow) == 2) {
                                        $arrayInfo[trim($infoRow[0])] = trim($infoRow[1]);
                                    }
                                }
                                if (isset($arrayInfo['version'])) {
                                    if ($arrayInfo['version'] == $version) {
                                        $output .= ' (table version equal = ' . $arrayInfo['version'] . ') ';
                                    } else {
                                        $queriesForVersion = [];

                                        $versionsObject = !empty($data['versions']) ? $data['versions'] : [];

                                        if (!empty($versionsObject)) {
                                            $tryMake = 0;
                                            $oldVersion = $arrayInfo['version'];
                                            $nextVersion = $oldVersion + 1;
                                            $finishLoop = false;
                                            $sizeDone = 0;
                                            while (!$finishLoop) {
                                                $checkKey = $oldVersion . '_' . $nextVersion;
                                                if (array_key_exists($checkKey, $versionsObject)) {
                                                    $val = $versionsObject[$checkKey];
                                                    if (isset($val['renameColumns'])) {
                                                        if (!empty($val['renameColumns'])) {
                                                            foreach ($val['renameColumns'] as $r => $c) {
                                                                $queries[] = "ALTER TABLE " . $tableFullName . " RENAME COLUMN " . $r . " TO " . $c . " ;";
                                                            }
                                                        }
                                                        $sizeDone += 1;
                                                    }
                                                }
                                                $tryMake += 1;
                                                if ($tryMake >= (sizeof($versionsObject, 0) - $sizeDone)) {
                                                    $finishLoop = true;
                                                }
                                            }
                                        }
                                        if (empty($queries)) {
                                            $output .= ' (no query found to update) ';
                                        } else {
                                            foreach ($queries as $q) {
                                                $database->query($q);
                                            }
                                            $database->query("ALTER TABLE " . $tables . " COMMENT = '" . $tableComment . "';");
                                            $output .= ' (version: ' . $arrayInfo['version'] . ' -> ' . $version . ') ';
                                            $type = 'success';
                                        }

                                    }
                                    $indexesAllowRun = true;
                                }
                            }
                            if (!$checkChanges) {
                                if (!empty($tableInfo['table_comment'])) {
                                    $output .= 'table exist';
                                } else {
                                    $output .= 'table exist already without migration';
                                }
                                $type = 'info';
                            }
                        }
                    } else {
                        $database->create($tables, $validatedParams);
                        $databaseError = $database->error;
                        if (!empty($databaseError)) {
                            $type = 'danger';
                            $output .= $databaseError;
                        } else {
                            $output .= 'created successfully';
                            $database->query("ALTER TABLE " . $tables . " COMMENT = '" . $tableComment . "';");
                        }
                        $indexesAllowRun = true;
                    }

                    if ($indexesAllowRun) {

                        $indexes_old = [];
                        $indexes_old_unique = [];
                        $indexes_old_primary = [];

                        $indexesQuery = $database->query("select index_schema,
       index_name,
       group_concat(column_name order by seq_in_index) as index_columns,
       index_type,
       case non_unique
            when 1 then 0
            else 1
            end as is_unique,
        table_name
from information_schema.statistics
where table_schema = '$schemaName' and TABLE_NAME='$tableName'
group by index_schema,
         index_name,
         index_type,
         non_unique,
         table_name
order by index_schema,
         index_name; ;")->fetchAll(\PDO::FETCH_ASSOC);
                        if (!empty($indexesQuery)) {
                            $indexColsPrimary = [];
                            foreach ($indexesQuery as $iq) {
                                $indexCols = trim($iq['index_columns']);
                                $indexCols = str_replace(' ', '', $indexCols);
                                if ($iq['index_name'] == "PRIMARY") {
                                    array_push($indexes_old_primary, $indexCols);
                                    array_push($indexColsPrimary, $indexCols);
                                    array_push($indexes_new, $indexCols);
                                } elseif ($iq['is_unique']) {
                                    array_push($indexes_old_unique, $indexCols);
                                } else {
                                    array_push($indexes_old, $indexCols);
                                }
                            }
                        }

                        $queries_indexes = [];

                        $array_compare1 = array_diff($indexes_old_unique, $indexes_new_unique);
                        $array_compare2 = array_diff($indexes_new_unique, $indexes_old_unique);
                        if (!empty($array_compare1)) {
                            foreach ($array_compare1 as $arrayCom) {
                                $indexName = $tableName . '_' . str_replace(',', '_', $arrayCom) . '_uindex';
                                $queries_indexes[] = 'drop index ' . $indexName . ' on ' . $schemaName . '.' . $tableName . ';';
                            }
                        }
                        if (!empty($array_compare2)) {
                            foreach ($array_compare2 as $arrayCom) {
                                $indexName = $tableName . '_' . str_replace(',', '_', $arrayCom) . '_uindex';
                                $queries_indexes[] = 'create unique index ' . $indexName . ' on ' . $schemaName . '.' . $tableName . ' (' . $arrayCom . ');';
                            }
                        }

                        $array_compare1 = array_diff($indexes_old_primary, $indexes_new_primary);
                        $array_compare2 = array_diff($indexes_new_primary, $indexes_old_primary);
                        if (!empty($array_compare1)) {
                            foreach ($array_compare1 as $arrayCom) {
                                $queries_indexes[] = 'ALTER TABLE ' . $schemaName . '.' . $tableName . ' DROP PRIMARY KEY ;';
                            }
                        }
                        if (!empty($array_compare2)) {
                            foreach ($array_compare2 as $arrayCom) {
                                $queries_indexes[] = 'ALTER TABLE ' . $schemaName . '.' . $tableName . ' ADD PRIMARY KEY (' . $arrayCom . ');';
                            }
                        }

                        $array_compare1 = array_diff($indexes_old, $indexes_new);
                        $array_compare2 = array_diff($indexes_new, $indexes_old);
                        if (!empty($array_compare1)) {
                            foreach ($array_compare1 as $arrayCom) {
                                $indexName = $tableName . '_' . str_replace(',', '_', $arrayCom) . '_index';
//                            drop index jk_data2_template_index on jk_data2;
                                $qrun = 'drop index ' . $indexName . ' on ' . $schemaName . '.' . $tableName . ';';
                                $queries_indexes[] = $qrun;
                            }
                        }
                        if (!empty($array_compare2)) {
                            foreach ($array_compare2 as $arrayCom) {
                                if (!in_array($arrayCom, $indexes_new_unique)) {
                                    if (in_array($arrayCom, $indexColsPrimary)) {
                                        $indexName = $arrayCom;
                                    } else {
                                        $indexName = $tableName . '_' . str_replace(',', '_', $arrayCom) . '_index';
                                    }
                                    $queries_indexes[] = 'create index ' . $indexName . ' on ' . $schemaName . '.' . $tableName . ' (' . $arrayCom . ');';
                                }
                            }
                        }

                        if (!empty($queries_indexes)) {
                            $qR = 0;
                            foreach ($queries_indexes as $qi) {
                                $output .= "\n";
                                $qR += 1;
                                $database->query($qi);
                                $databaseError = $database->error;
                                if (!empty($databaseError) ) {
                                    $output .= $tables . " -> failed index query " . $qR . ": ".$databaseError;
                                } else {
                                    $output .= $tables . " -> success index query " . $qR . ": run";
                                }
                            }
                        }
                    }


                } elseif ($initType == 'down') {
//                    Database::drop($tables);
                }
                $this->output[] = ["type" => $type, "msg" => $output];
            }
        }
    }

    public function init()
    {
        return false;
    }

    public function up($moduleName)
    {

    }

    public function down($moduleName)
    {

    }

    public function isValidDataType($dataType)
    {
        $whiteList = [
            "TINYINT",
            "BOOLEAN",
            "BOOL",
            "SMALLINT",
            "MEDIUMINT",
            "INT",
            "INTEGER",
            "BIGINT",
            "DECIMAL",
            "DEC",
            "NUMERIC",
            "FIXED",
            "FLOAT",
            "DOUBLE",
            "BIT",
            "CHAR",
            "VARCHAR",
            "BINARY",
            "CHAR BYTE",
            "VARBINARY",
            "TINYBLOB",
            "BLOB",
            "MEDIUMBLOB",
            "LONGBLOB",
            "TINYTEXT",
            "TEXT",
            "MEDIUMTEXT",
            "LONGTEXT",
            "JSON",
            "ENUM",
            "ROW",
            "DATE",
            "TIME",
            "DATETIME",
            "TIMESTAMP",
            "YEAR"
        ];
        if (in_array(strtoupper($dataType), $whiteList))
            return true;
        return false;
    }

    public function isValidLength($length, $datatype = null)
    {
        if($datatype=="decimal"){
            return true;
        }
        if (is_numeric($length))
            return true;
        return false;
    }

    public function isValidIndex($index)
    {
        if ($index == 'primary' || $index == 'unique' || $index == 'foreign')
            return true;
        return false;
    }

    public function requestParser($request)
    {
        $result = null;
        $type=!empty($request['type'])?$request['type']:null;
        foreach ($request as $key => $value) {
            switch ($key) {
                case 'type':
                {
                    if ($this->isValidDataType($value))
                        $result[$key] = strtoupper($value);
                    else
                        die('invalid data type ' . $value . PHP_EOL);
                    break;
                }
                case 'length':
                {
                    if ($this->isValidLength($value,$type))
                        $result[$key] = $value;
                    else
                        die('invalid length' . PHP_EOL);
                    break;
                }
                case 'default':
                {
                    $result[$key] = $value;
                    break;
                }
                case 'collation':
                {
                    $result[$key] = $value;
                    break;
                }
                case 'nullable':
                {
                    if ($value === false)
                        $result[$key] = "NOT NULL";
                    else
                        $result[$key] = null;
                    break;
                }
                case 'ai':
                {
                    if ($value === true)
                        $result[$key] = "AUTO_INCREMENT";
                    else
                        $result[$key] = null;
                    break;
                }
                case 'index':
                {
                    if ($value == "primary")
                        $result[$key] = "PRIMARY KEY";
                    elseif ($value == "unique")
                        $result[$key] = "UNIQUE";
                    else
                        die('your index is invalid' . PHP_EOL);
                    break;
                }
                default:
                {
                    break;
                }
            }
        }
        if (!isset($result['default'])) {
            $result['default'] = null;
        } else {
            $result['default'] = 'DEFAULT ' . $result['default'];
        }
        if (!isset($result['collation']))
            $result['collation'] = null;
        if (!isset($result['nullable']))
            $result['nullable'] = null;
        if (!isset($result['ai']))
            $result['ai'] = null;
        if (!isset($result['index']))
            $result['index'] = null;
        if (!isset($result['length']) && $result['type'] == 'INT')
            $result['length'] = 11;
        if (!isset($result['length']) && $result['type'] == 'VARCHAR')
            $result['length'] = 255;
        if (isset($result['length']))
            $result["type"] .= '(' . $result["length"] . ')';


        $output = [
            $result["type"],
            $result["nullable"],
            $result["default"],
            $result["collation"],
            $result["ai"],
            $result["index"],
        ];
        return array_values(array_filter($output));
    }

    function tableOldDescribe($table, $validatedParams, $tableToDescribe)
    {
        $queries = [];
        $needRemoveColumn = '';
        $tableToDescribeF = [];
        $primaries = [];
        foreach ($tableToDescribe as $t) {
            $fName = $t['Field'];
            $out = [];
            array_push($out, strtoupper($t['Type']));
            $null = $t['Null'] == "NO" ? "NOT NULL" : "";
            if (!empty($null)) {
                array_push($out, $null);
            }
            if ($t['Extra'] == "auto_increment") {
                array_push($out, "AUTO_INCREMENT");
            }
            if ($t['Key'] == "PRI") {
                array_push($primaries, $t['Field']);
                array_push($out, "PRIMARY KEY");
            }
            $tableToDescribeF[$fName] = $out;
        }

        foreach ($validatedParams as $cl => $inf) {
            $isAI = in_array('AUTO_INCREMENT', $inf) ? ' AUTO_INCREMENT ' : '';
            $isPK = in_array('PRIMARY KEY', $inf) ? ' PRIMARY KEY ' : '';

            $isUN = in_array('UNIQUE', $inf) ? ' UNIQUE ' : '';
            $isN = in_array('NOT NULL', $inf) ? ' NOT NULL ' : '';
            $DEF = '';
            foreach ($inf as $cD) {
                if (substr($cD, 0, strlen("DEFAULT ")) == "DEFAULT ") {
                    $DEF = $cD;
                }
            }

            if (!isset($tableToDescribeF[$cl])) {
                $queries[] = "ALTER TABLE " . $table . " ADD COLUMN " . $cl . " " . $inf[0] . " " . $DEF . $isN . $isAI . $isUN . $isPK . " ;";
            } else {
                if (!empty($isPK) && !in_array($cl, $primaries)) {
                    $queries[] = "ALTER TABLE " . $table . " MODIFY COLUMN " . $cl . " " . $inf[0] . " " . $DEF . $isN . $isAI . $isUN . $isPK . " ;";
                } else {
                    $queries[] = "ALTER TABLE " . $table . " MODIFY COLUMN " . $cl . " " . $inf[0] . " " . $DEF . $isN . $isAI . $isUN . " ;";
                }
            }
        }
        return $queries;
    }

    protected function tableQuote($table)
    {
        if (!preg_match('/^[a-zA-Z0-9_.]+$/i', $table)) {
            throw new InvalidArgumentException("Incorrect table name \"$table\"");
        }
        if (stripos($table, '.') !== false) {
            $schemaExplode = explode('.', $table);
            $websiteDbConfig = JK_WEBSITE()['database'];
            $schema = '';
            if (!empty($websiteDbConfig['other'][$schemaExplode[0]])) {
                $schemaF = $websiteDbConfig['other'][$schemaExplode[0]];
                if (is_array($schemaF) && !empty($schemaF['db'])) {
                    $schema = $schemaF['db'];
                } elseif (is_string($schemaF)) {
                    $schema = $schemaF;
                }
            } else {
                $table = $websiteDbConfig['db'] . '.' . $schemaExplode[1];
            }
            if (!empty($schema)) {
                $table = $schema . '.' . $schemaExplode[1];
            }
        } else {
            $websiteDbConfig = JK_WEBSITE()['database'];
            if (!empty($websiteDbConfig['db'])) {
                $table = $websiteDbConfig['db'] . '.' . $table;
            }
        }
        return $table;
    }

    public function id_structure($bigInt = false)
    {
        return [
            "type" => $bigInt ? "bigint" : "int",
            "ai" => true,
            "nullable" => false,
            "index" => 'primary'
        ];
    }

    public function varchar($value = 255, $nullable = true, $default = "null")
    {
        return [
            "type" => "varchar",
            "length" => $value,
            "nullable" => true,
            "default" => $default
        ];
    }
    public function decimal($value = "14,3", $nullable = true, $default = "null")
    {
        return [
            "type" => "decimal",
            "length" => $value,
            "nullable" => true,
            "default" => $default
        ];
    }

    public function text($nullable = true, $default = "null")
    {
        return [
            "type" => "text",
            "nullable" => true,
            "default" => $default
        ];
    }

    public function longtext($nullable = true, $default = "null")
    {
        return [
            "type" => "longtext",
            "nullable" => true,
            "default" => $default
        ];
    }

    public function date($defaultCurrent = true, $nullable = true)
    {
        return [
            "type" => "date",
            "default" => $defaultCurrent ? "current_date()" : null,
            "nullable" => $nullable
        ];
    }

    public function datetime($defaultCurrent = true, $nullable = true)
    {
        return [
            "type" => "datetime",
            "default" => $defaultCurrent ? "current_timestamp()" : null,
            "nullable" => $nullable
        ];
    }

    public function int($default = "null", $value = 11, $nullable = true)
    {
        return [
            "type" => "int",
            "length" => $value,
            "nullable" => true,
            "default" => $default
        ];
    }

    public function float($default = "null", $nullable = true)
    {
        return [
            "type" => "int",
            "nullable" => true,
            "default" => $default
        ];
    }
}