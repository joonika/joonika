<?php

namespace Joonika\dev;

use Joonika\Database;
use Joonika\FS;
use Joonika\SSH;
use Joonika\Translate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use phpseclib\Net\SFTP;


class db extends baseCommand
{
    public function __construct(AppCommand $app, $command = null)
    {
        parent::__construct($app, $command, true, true);
    }


    public static function commandsList()
    {
        return [
            "db:backup" => [
                "title" => "get backup from database",
                "arguments" => ["tableName"],
                "options" => [
                    'all' => [
                        "desc" => "backup from all of database"
                    ],
                ]
            ],
            "db:restore" => [
                "title" => "get backup from database",
                "arguments" => ["tableName"],
                "options" => [
                    'all' => [
                        "desc" => "backup from all of database"
                    ],
                ]
            ],
            "db:foreign" => [
                "title" => "set foreign key",
            ],
            "db:insert" => [
                "title" => "insert data to table",
            ],
            "db:query" => [
                "title" => "db query",
            ],
            "db:select" => [
                "title" => "select from db",
            ],
            "db:command" => [
                "title" => "ssh query",
            ],
            "db:drop" => [
                "title" => "drop table query",
            ],
            "db:translateUpdate" => [
                "title" => "update translate table",
                "arguments" => ["lang"],
                "options" => [
                    'export' => [
                        "desc" => "export translate table"
                    ],
                ]
            ],
            "db:translate" => [
                "title" => "translate new strings",
                "arguments" => ['lang', "string"],
                "options" => [
                    'empty' => [
                        "desc" => "select not translated strings"
                    ],
                ]
            ],
            "db:translateExport" => [
                "title" => "get export from trnaslate table",
                "arguments" => ["name"],
                "options" => [
                    'empty' => [
                        "desc" => "select not translated strings"
                    ],
                    'filled' => [
                        "desc" => "select translated strings"
                    ],
                    'lang' => [
                        "desc" => "select translated strings"
                    ],
                ]
            ],
            "db:translateRestore" => [
                "title" => "restore translate table from file",
                "arguments" => ["name"]
            ],
        ];
    }

    public function dbSelect()
    {
        $dbList = array_keys(Database::getDbConfig($this->databaseInfo));
        foreach ($dbList as $key => $db) {
            $key++;
            if ($key == 1) {
                $dbName = Database::getDbConfig($this->databaseInfo)[$db]['db'];
                $this->writeInfo("[$key] $dbName ($db)");
            } else
                echo "[$key] $db" . PHP_EOL;
        }
        $this->ask('select database : ( you can exit with  Ctrl + C  )', $dbOption, true);
        return Database::getDbConfig($this->databaseInfo)[$dbList[$dbOption - 1]];
    }

    public function sshSelect()
    {
        $sshList = array_keys(SSH::getSshConfig());
        foreach ($sshList as $key => $ssh) {
            $key++;
            if ($key == 1) {
                $sshName = SSH::getSshConfig()[$ssh]['name'];
                $this->writeInfo("[$key] $sshName ($ssh)");
            } else
                echo "[$key] $ssh" . PHP_EOL;
        }
        $this->ask('select ssh config : ', $sshOption);
        if (empty($sshOption))
            $sshOption = 1;
        return SSH::getSshConfig()[$sshList[$sshOption - 1]];
    }

    public function backup()
    {
        $backupOptions = [
            "Backup From Local Database To Local Machine",
            "Backup From Local Database To Remote Machine",
            "Backup From Remote Database To Local Machine",
            "Backup From Remote Database To Remote Machine",
        ];
        foreach ($backupOptions as $key => $option) {
            $key++;
            if ($key == 1)
                $this->writeInfo(PHP_EOL . "[$key] $option (default)");
            else
                echo "[$key] $option" . PHP_EOL;
        }
        $this->ask('Select the appropriate option', $backupOption);

        if (empty($backupOption))
            $backupOption = 1;

        switch ($backupOption) {
            case '2':
                {
                    $selectedDb = $this->dbSelect();
                    date_default_timezone_set('Iran');
                    $filename = 'dbBackup_[' . $selectedDb['db'] . ']' . date('[Y-m-d][H:i:s]') . '.sql';
                    FS::mkDir(JK_SITE_PATH() . 'storage/cache/backup');
                    exec('mysqldump ' . $selectedDb['db'] . ' --host=' . $selectedDb['host'] . ' --password=' . $selectedDb['pass'] . ' --user=' . $selectedDb['user'] . ' --single-transaction >' . JK_SITE_PATH() . 'storage/cache/backup/' . $filename, $output);
                    $selectedSsh = $this->sshSelect();
                    $ssh = $this->sshConnect($selectedSsh);
                    $ssh->chdir('~');
                    $ssh->mkdir('backup');
                    if (empty($output)) {
                        $ssh->chdir('~');
                        $ssh->chdir('backup');
                        $ssh->put($filename, JK_SITE_PATH() . 'storage/cache/backup/' . $filename, SFTP::SOURCE_LOCAL_FILE);
                        $this->writeInfo('backup file sent to client successfully');
                    } else
                        $this->writeError('backup process failed : ' . $output);
                    exec('rm ' . JK_SITE_PATH() . 'storage/cache/backup/' . $filename, $err);
                    if (!empty($err))
                        $this->writeError($err);

                }
                break;
            case '3':
                {
                    $selectedSsh = $this->sshSelect();
                    $ssh = $this->sshConnect($selectedSsh);
                    $ssh->chdir('~');
                    $ssh->mkdir('tempBackup');
                    date_default_timezone_set('Iran');
                    $selectedDb = $this->dbSelect();
                    $output = null;
                    $filename = 'dbBackup_[' . $selectedDb['db'] . ']' . date('[Y-m-d][H:i:s]') . '.sql';
                    $ssh->exec('mysqldump ' . $selectedDb['db'] . ' --host=' . $selectedDb['host'] . ' --password=' . $selectedDb['pass'] . ' --user=' . $selectedDb['user'] . ' --single-transaction > ~/tempBackup/' . $filename);
                    $ssh->chdir('~');
                    $ssh->chdir('tempBackup');
                    $ssh->get($filename, JK_SITE_PATH() . 'backup/' . $filename);
                    $ssh->exec('rm -r ~/tempBackup');
                    $this->writeInfo('Backup From Remote Database Saved on Local Machine');
                }
                break;
            case '4':
                {
                    $selectedSsh = $this->sshSelect();
                    $ssh = $this->sshConnect($selectedSsh);
                    $ssh->chdir('~');
                    $ssh->mkdir('Backup');
                    date_default_timezone_set('Iran');
                    $selectedDb = $this->dbSelect();
                    $output = null;
                    $filename = 'dbBackup_[' . $selectedDb['db'] . ']' . date('[Y-m-d][H:i:s]') . '.sql';
                    $ssh->exec('mysqldump ' . $selectedDb['db'] . ' --host=' . $selectedDb['host'] . ' --password=' . $selectedDb['pass'] . ' --user=' . $selectedDb['user'] . ' --single-transaction > ~/Backup/' . $filename);
                    $this->writeInfo('Backup From Remote Database Saved on Remote Machine');
                    $this->writeInfo('Path: ~/Backup/' . $filename);
                }
                break;
            default:
                {
                    $selectedDb = $this->dbSelect();
                    date_default_timezone_set('Iran');
                    $filename = 'dbBackup_[' . $selectedDb['db'] . ']' . date('[Y-m-d][H:i:s]') . '.sql';
                    FS::mkDir(JK_SITE_PATH() . '/backup');
                    $result = exec('mysqldump ' . $selectedDb['db'] . ' --host=' . $selectedDb['host'] . ' --password=' . $selectedDb['pass'] . ' --user=' . $selectedDb['user'] . ' --single-transaction >' . JK_SITE_PATH() . '/backup/' . $filename, $output);
                    if (empty($output))
                        $this->writeInfo('backup file successfully created');
                    else
                        $this->writeError('backup process failed : ' . $output);
                }
                break;
        }

    }

    public function restore()
    {
        $i = 1;
        $backupsArray = [];
        if (count(glob(JK_SITE_PATH() . '/backup/*')) == 0) {
            $this->writeError('there is no backup for restore');
            die('');
        } else {
            foreach (glob(JK_SITE_PATH() . '/backup/dbBackup_*') as $backups) {
                $this->writeInfo("[$i]  " . explode('_', basename($backups, '*.sql'), 2)[1]);
                $backupsArray[$i] = basename($backups);
                $i++;
            }
            $this->ask('Please enter backup number ( you can exit with  Ctrl + C  )', $backupNumber, true);
            $backupName = $backupsArray[$backupNumber];
            $selectedDb = $this->dbSelect();
            $result = exec('mysql ' . $selectedDb['db'] . ' --host=' . $selectedDb['host'] . ' --user=' . $selectedDb['user'] . ' --password=' . $selectedDb['pass'] . ' < ' . JK_SITE_PATH() . '/backup/' . $backupName, $output);
            if (empty($output))
                $this->writeInfo('backup successfully restored');
            else
                $this->writeError('restoring process failed : ' . $output);
        }
    }

    public function foreign()
    {
//        die();
//
        $selectedDb = $this->dbSelect();
        $tableList = self::getTables($selectedDb['db']);
        foreach ($tableList as $key => $table) {
            $key++;
            echo "[$key] $table" . PHP_EOL;
        }
        $this->ask('select table : ( you can exit with  Ctrl + C  )', $tableOption, true);
        $colList = self::getColumns($tableList[$tableOption - 1], $selectedDb['db']);
        foreach ($colList as $key => $col) {
            $key++;
            echo "[$key] $col" . PHP_EOL;
        }
        $this->ask('select column to set as foreign key : ( you can exit with  Ctrl + C  )', $colOption, true);
        $FKey = $colList[$colOption - 1];


        foreach ($tableList as $key => $table) {
            $key++;
            echo "[$key] $table" . PHP_EOL;
        }
        $this->ask('select reference table : ( you can exit with  Ctrl + C  )', $rtableOption, true);
        $colList = self::getColumns($tableList[$rtableOption - 1], $selectedDb['db']);
        foreach ($colList as $key => $col) {
            $key++;
            echo "[$key] $col" . PHP_EOL;
        }
        $this->ask('select column to set as reference key : ( you can exit with  Ctrl + C  )', $rcolOption, true);
        $RKey = $colList[$rcolOption - 1];


        $result = exec('mysql ' . $selectedDb['db'] . ' --host=' . $selectedDb['host'] . ' --password=' . $selectedDb['pass'] . ' --user=' . $selectedDb['user'] . ' -e "ALTER TABLE ' . $tableList[$tableOption - 1] . ' ADD FOREIGN KEY (' . $FKey . ') REFERENCES ' . $tableList[$rtableOption - 1] . '(' . $RKey . ')"', $output);

        $this->writeInfo('<< ' . $tableList[$tableOption - 1] . '.' . $FKey . ' -> ' . $tableList[$rtableOption - 1] . '.' . $RKey . ' >> Created');
    }

    protected static function getTables($dbName = null)
    {
        $tables = [];
        $databaseInfo = databaseInfo('dev');
        if (checkArraySize($databaseInfo)) {
            if (in_array($dbName, $databaseInfo)) {
                foreach (Database::queryAndFetch("show tables", [], $dbName) as $table)
                    array_push($tables, array_values($table)[0]);
                return $tables;
            } else {
                return 'config file is invalid';
            }
        } else {
            return $databaseInfo;
        }

    }

    protected static function getColumns($tableName, $dbName)
    {
        $columns = [];
        foreach (Database::queryAndFetch("DESCRIBE $tableName", [], $dbName) as $col) {
            array_push($columns, $col['Field']);
        }
        return $columns;
    }

    public function insert()
    {
        $selectedDb = $this->dbSelect();
        $tableList = self::getTables($selectedDb['db']);
        foreach ($tableList as $key => $table) {
            $key++;
            echo "[$key] $table" . PHP_EOL;
        }
        $this->ask('select table : ( you can exit with  Ctrl + C  )', $tableOption, true);
        $colList = self::getColumns($tableList[$tableOption - 1], $selectedDb['db']);
        echo 'Columns : ';
        foreach ($colList as $key => $col) {
            $key++;
            echo " $col |";
        }
        echo PHP_EOL;
        $colValues = [];
        foreach ($colList as $colName) {
            $this->ask('insert data to ' . $colName, $colValue, ($colName != 'id'));
            if (strpos($colValue, 'fn.') !== false) {
                $fn = str_replace("fn.", "", $colValue);
                if (method_exists(db::class, $fn)) {
                    $colValue = $this->$fn();
                    $this->writeInfo('"' . $colValue . '" Generated');
                }
            }
            $colValues[$colName] = $colValue;
            $colValue = null;
        }
        Database::insert($tableList[$tableOption - 1], $colValues, $selectedDb['db']);
    }

    public function hash()
    {
        return $this->generateRandomString();
//        return sha1(md5($this->generateRandomString()));
    }

    public function now()
    {
        return date(now());
    }

    public function generateRandomString($length = 30)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function query()
    {
        $selectedDb = $this->dbSelect();
        $this->ask('write your query', $query, true);
        $result = Database::queryAndFetch($query, [], $selectedDb['db']);
        print_r($result);
    }

    public function select()
    {
        $selectedDb = $this->dbSelect();
        $tableList = self::getTables($selectedDb['db']);
        foreach ($tableList as $key => $table) {
            $key++;
            echo "[$key] $table" . PHP_EOL;
        }
        $this->ask('select table : ', $tableOption, true);

        $selectedTable = $tableList[$tableOption - 1];
        $columnsList = self::getColumns($selectedTable, $selectedDb['db']);
        $columnsStr = '';
        foreach ($columnsList as $cols) {
            $columnsStr .= $cols . '  ';
        }
        $this->writeInfo('Columns : ' . $columnsStr);
        $this->ask('write column names | default (*) : ', $columns);
        $colParsed = $columnsList;

        if (sizeof(explode(',', $columns)) > 1) {
            $colParsed = null;
            $colParsed = explode(',', $columns);
        }

        $this->ask('where ? | default (null) : ', $cond);
        preg_match("/(?<var>[\w\s]+)(?<opr>((=)|(>)|(<)|(!)|(~))+)(?<val>[(\w\W)]+)/", $cond, $condResult);
        $condResult = array_intersect_key($condResult, array_flip(['var', 'opr', 'val']));
        if (isset($cond) && isset($condResult['opr'])) {
            switch ($condResult['opr']) {
                case '!':
                case '!=':
                case '!==':
                case '<!>':
                case '<=>':
                    $condResult['opr'] = '!';
                    break;
                case '<>':
                    $max = explode(',', $condResult['val'])[0];
                    $min = explode(',', $condResult['val'])[1];
                    $condResult['val'] = [$max, $min];
                    break;
                case '><':
                    $min = explode(',', $condResult['val'])[1];
                    $max = explode(',', $condResult['val'])[0];
                    $condResult['opr'] = '<>';
                    $condResult['val'] = [$max, $min];
                    break;
            }

            $condResult['var'] .= '[' . $condResult['opr'] . ']';
            $cond = [
                $condResult['var'] => $condResult['val']
            ];
        } else
            $cond = null;


        $results = Database::select($selectedDb['db'] . '.' . $selectedTable, $colParsed, $cond);
        if (checkArraySize($results))
            foreach ($results as $result) {
                $dash = 0;
                $titleSpace = $this->maxLengthInArray($colParsed);
                $contentSpace = $this->maxLengthInArray($result);

                $dash = $contentSpace + $titleSpace + 7;
                $edge = '+';
                foreach ($colParsed as $title) {
                    $this->printer($dash, '-', $edge);
                    $edge = '-';
                    $this->output->write(PHP_EOL . '|<fg=white>' . $this->center($title, $titleSpace + 2) . '</>');
                    $this->output->write('|<fg=cyan>' . $this->center($result[$title], $contentSpace + 2) . '</>|' . PHP_EOL);
                }
                $this->printer($dash, '-', '+');
                echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
            }

    }

    public function center($str, $spaceCount)
    {
        return str_pad($str, $spaceCount, " ", STR_PAD_BOTH);
    }

    public function maxLengthInArray($array)
    {
        $lenArr = array_map('strlen', $array);
        $max = 0;
        foreach ($lenArr as $item) {
            if ($item >= $max)
                $max = $item;
        }
        return $max;
    }

    public function draw($array)
    {
        $lenArr = array_map('strlen', $array);
        $count = 0;
        $total = -1;
        foreach ($lenArr as $content) {
            $count += $content;
            $total += $content + 3;
        }
        $this->printer($total, null, '+');
        echo '| ';
        foreach ($array as $item) {
            echo $item . ' | ';
        }
        echo PHP_EOL;
    }

    protected function printer($count, $char = '-', $edgeChar = '', $type = 'normal')
    {
        $str = $edgeChar;
        $i = 2;
        while ($i < $count) {
            $str .= $char;
            $i++;
        }
        if ($type == 'normal')
            echo $str . $edgeChar;
        elseif ($type == 'info')
            $this->writeInfo($str . $edgeChar);
    }

    public function sshConnect($config)
    {
        $ssh = new SFTP($config['host']);
        if (!$ssh->login($config['user'], $config['pass'])) {
            die('Login Failed');
        } else
            return $ssh;
    }

    public function command()
    {
        $selectedSsh = $this->sshSelect();
        $ssh = $this->sshConnect($selectedSsh);
        $this->ask('command', $command, true);
        echo $ssh->exec($command);
    }

    public function drop()
    {
        $selectedDb = $this->dbSelect();
        $tableList = self::getTables($selectedDb['db']);
        foreach ($tableList as $key => $table) {
            $key++;
            echo "[$key] $table" . PHP_EOL;
        }
        $this->ask('select table : ( you can exit with  Ctrl + C  )', $tableOption, true);
        Database::drop($tableList[$tableOption - 1], $selectedDb['db']);
        $this->writeInfo($selectedDb['db'] . '.' . $tableList[$tableOption - 1] . ' Dropped Successfully');
    }

    public function translateUpdate()
    {
        $selectedDb = $this->dbSelect();
        $tableList = self::getTables($selectedDb['db']);
        $lang = $this->checkInputArguments('lang');
        if (in_array('jk_translate', $tableList)) {
            if ($lang) {
                $tr = Translate::TR(null, null, $lang);
                $tr->goToTranslate(false);
                $this->writeInfo('translate table updated succeesfull');
            } else {
                $this->writeError('please enter lang');
            }
        } else {
            $this->writeError('translate table not exist');
        }
    }

    public function translate()
    {
        $selectedDb = $this->dbSelect();
        $string = $this->checkInputArguments('string');
        $lang = $this->checkInputArguments('lang');
        $empty = $this->checkOptions('empty');
        $tableList = self::getTables($selectedDb['db']);
        if (in_array('jk_translate', $tableList)) {

            $condittion = [];
            if ($lang) {
                $condittion['AND']['lang'] = $lang;
            }
            if ($string) {
                $condittion['AND']['var'] = $lang;
            }
            if ($empty) {
                $condittion['AND']['text'] = '';
            }

            $translates = Database::select('jk_translate', '*', $condittion);
            if (checkArraySize($translates)) {
                foreach ($translates as $k => $v) {
                    $this->writeInfo("please enter translate of :\n\t");
                    $this->ask($v['var'] . " | lang : " . $v['lang'] . " | module : " . $v['dest'], $kv);
                    if ($kv) {
                        Database::update('jk_translate', ['text' => $kv], ['var' => $k]);
                        $this->writeSuccess("saved");
                    } else {
                        $this->writeError('not saved');
                    }
                }
                $this->writeInfo('translate table updated succeesfull');

            } elseif ($string) {
                $this->ask('string not found , do you want to create it ? (yes/no)', $create);
                if ($lang) {
                    if ($create && $create == "yes") {
                        $this->ask(trim($string), $kv);
                        if ($kv) {
                            Database::insert('jk_translate', [
                                'var' => trim($string),
                                'text' => trim($kv),
                                'lang' => $lang ? $lang : '',
                                'status' => 'active',
                                'dest' => 'main',
                                'type' => 'modules'
                            ]);
                            $this->writeSuccess("saved");
                        } else {
                            $this->writeError('not saved');
                        }
                    } else {
                        $this->writeError('not saved');
                    }
                } else {
                    $this->writeError('please enter lang');
                }
            } else {
                $this->writeError('not found any string');
            }
        } else {
            $this->writeError('translate table not exist');
        }
    }

    public function translateExport()
    {
        $selectedDb = $this->dbSelect();
        $name = $this->checkInputArguments('name');
        $empty = $this->checkOptions('empty');
        $lang = $this->checkOptions('lang');
        $filled = $this->checkOptions('filled');
        $tableList = self::getTables($selectedDb['db']);
        if (in_array('jk_translate', $tableList)) {
            $conditions = [];
            if ($empty) {
                $conditions["AND"]['text'] = '';
            } elseif ($filled) {
                $conditions['AND']['text[!]'] = '';
            }

            $setLang = null;
            if ($lang) {
                $this->ask('please enter lang', $setLang);
                if (!strlen($setLang) > 0) {
                    $this->writeError('lang is not valid');
                    die('');
                }
            }
            if ($setLang) {
                $conditions['AND']['lang'] = $setLang;
            }
            $strings = Database::select('jk_translate', '*', $conditions);
            if (checkArraySize($strings)) {
                try {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $row = 1;
                    foreach ($strings as $key => $val) {
                        $sheet->setCellValue('A' . $row, $val['var']);
                        $sheet->setCellValue('B' . $row, $val['lang']);
                        $sheet->setCellValue('C' . $row, $val['text']);
                        $sheet->setCellValue('D' . $row, $val['status']);
                        $sheet->setCellValue('E' . $row, $val['dest']);
                        $sheet->setCellValue('F' . $row, $val['type']);
                        $row += 1;
                    }

                    $writer = new Xlsx($spreadsheet);

                    if ($name) {
                        $filename = $name . '.xlsx';
                    } else {
                        $filename = JK_LANG() . '.xlsx';
                    }

                    $folder = 'files/tmp';
                    if (!FS::isDir(JK_SITE_PATH() . 'storage' . DS() . $folder)) {
                        FS::mkDir(JK_SITE_PATH() . 'storage' . DS() . $folder);
                    }
                    $path = $folder . DS() . $filename;

                    if (isset($path)) {
                        $file = JK_SITE_PATH() . 'storage' . DS() . $path;
                        if (empty($writer)) {
                            $writer = new Xlsx($spreadsheet);
                        }
                        $writer->save($file);
                    }
                    $this->writeInfo('backup file created in /storage/' . $path);
                    exit();

                } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
                    dd($e);
                }
            } else {
                $this->writeError('not found any strings');
            }
        } else {
            $this->writeError('translate table not exist');
        }
    }

    public function translateRestore()
    {

        $selectedDb = $this->dbSelect();
        $name = $this->checkInputArguments('name');
        $tableList = self::getTables($selectedDb['db']);
        if (in_array('jk_translate', $tableList)) {
            if ($name) {
                $path = JK_SITE_PATH() . 'storage' . DS() . "files" . DS() . "tmp" . DS() . $name . ".xlsx";
                if (FS::isExistIsFile($path)) {
                    try {
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                        $worksheet = $spreadsheet->getActiveSheet();
                        $rows = [];
                        foreach ($worksheet->getRowIterator() as $row) {
                            $cellIterator = $row->getCellIterator();
                            $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
                            $cells = [];
                            foreach ($cellIterator as $cell) {
                                $cells[] = $cell->getValue();
                            }
                            $rows[] = $cells;
                        }
                        if (checkArraySize($rows)) {
                            $keys = array_values(array_unique(array_column($rows, '1')));
                            if (sizeof($keys) > 1) {
                                \Joonika\Database::delete('jk_translate', []);
                            } else {
                                \Joonika\Database::delete('jk_translate', ['lang' => trim($keys[0])]);
                            }

                            foreach ($rows as $row) {
                                \Joonika\Database::insert('jk_translate', [
                                    'var' => $row[0],
                                    'lang' => $row[1],
                                    'text' => $row[2],
                                    'status' => $row[3],
                                    'dest' => $row[4],
                                    'type' => $row[5],
                                ]);
                            }
                            $this->writeSuccess('done');
                        } else {
                            $this->writeError('file is not valid');
                        }
                    } catch (Exception $ex) {
                        echo alertWarning($ex->getMessage());
                    }
                } else {
                    $this->writeError("( " . $name . ' ) not exist');
                }
            } else {
                $this->writeError('please enter file name to restore');
            }
        } else {
            $this->writeError('translate table not exist');
        }
    }

}