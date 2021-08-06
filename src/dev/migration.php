<?php

namespace Joonika\dev;

use Joonika\FS;
use Joonika\ManageTables;
use Joonika\Route;

class migration extends baseCommand
{

    public $faild = 0;

    public function __construct(AppCommand $app, $command = null, $connectToDataBase = false)
    {
        parent::__construct($app, $command, true);
    }

    public static function commandsList()
    {
        return [
            "migration:create" => [
                "title" => "create migration schema in module",
                "arguments" => ["moduleName"],
            ],
            "migration:up" => [
                "title" => "migrate schema to database",
                "arguments" => ["moduleName"],
                "options" => [
                    'all' => [
                        "desc" => "migrate all schemas' of module"
                    ],
                    'force' => [
                        "desc" => "allow to force alter table if table exist"
                    ],
                ]
            ],
            "migration:upAll" => [
                "title" => "migrate core and all modules database",
                "arguments" => ["all"],
                "options" => [
                    'force' => [
                        "desc" => "allow to force alter table if table exist"
                    ],
                ]
            ],
            "migration:down" => [
                "title" => "dump migration from database",
                "arguments" => ["moduleName"],
                "options" => [
                    'all' => [
                        "desc" => "drop all schemas' of module"
                    ],
                ]
            ],
        ];
    }

    public function create()
    {
        $moduleName = strtolower($this->checkInputArguments('moduleName'));
        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $moduleName, true);
        $this->checkModuleIsValid($moduleName);
        $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Database/Migration";
        if (!is_dir(JK_DIR_MODULES() . '/' . $moduleName . '/Database/Migration')) {
            \Joonika\FS::mkDir($mkDir, 0777);
        }
        $namespace = "\Modules\\$moduleName\Database\Migration";
        $newMigrationName = null;
        $this->ask('Enter migration name to create', $newMigrationName, true);
        $path = "$mkDir/$newMigrationName" . "Migration.php";
        if (FS::isExistIsFile($path)) {
            $this->writeOutPut($newMigrationName . ' Exist Already ' . PHP_EOL);
            die();
        }
        $template = FS::fileGetContent(__dir__ . "/../templates/files/migration/migration.txt");
        $template = str_replace("{moduleName}", $moduleName, $template);
        $template = str_replace("{migrateName}", $newMigrationName, $template);
        FS::filePutContent($path, $template);
        $this->writeOutPut('\'' . $newMigrationName . '\' migration created in \'' . $moduleName . '\' module' . PHP_EOL);
        die();
    }

    public function up()
    {
        $k = 1;
        $modulesNameArray = [];
        $modulesNameArray[0] = 'joonika';
        echo "[0] joonika" . PHP_EOL;
        $all = $this->checkOptions('all');

        $force = $this->checkOptions('force');
        $moduleName = strtolower($this->checkInputArguments('moduleName'));
        if (empty($moduleName)) {
            foreach (listModules() as $modules) {
                $module = basename($modules);
                echo "[$k] $module" . PHP_EOL;
                $modulesNameArray[$k++] = $module;
            }
            $moduleNumber = null;
            $this->ask('Please select module ( you can exit with  Ctrl + C  )', $moduleNumber, true);
            if (!in_array($moduleNumber, array_keys($modulesNameArray))) {
                $this->writeOutPut('your selected option is invalid');
                die('');
            }
            $moduleName = $modulesNameArray[$moduleNumber];
        }
        if ($moduleName == "joonika") {
            $this->upJoonikaMigration($force);
            if ($this->faild) {
                return 1;
            }
        } else {
            $this->upSingleModuleMigrations($moduleName, $all, $force);
            if ($this->faild) {
                return 1;
            }
        }

    }

    public function upAll()
    {
        $force = $this->checkOptions('force');
        $this->upJoonikaMigration($force);
        if ($this->faild) {
            return 1;
        }
        foreach (listModules() as $singleModule) {
            $this->upSingleModuleMigrations($singleModule, 1, $force, false);
            if ($this->faild) {
                return 1;
            }
        }
        return 0;
    }

    private function upJoonikaMigration($force = false)
    {
        $namespace = "Joonika\Migration\\joonika";
        if (!class_exists($namespace)) {
            $this->writeError('there isn\'t any migration in this module');
        } else {
            $migrationClass = new $namespace('up', ['options' => ["force" => $force]], 'joonika');
            $this->writeOutputs($migrationClass->output);
        }
    }

    private function writeOutputs($outputs)
    {
        if (!empty($outputs)) {
            foreach ($outputs as $output) {
                if ($output['type'] == 'success') {
                    $this->writeSuccess($output['msg'] . "\n");
                } elseif ($output['type'] == 'danger') {
                    $this->writeError($output['msg'] . "\n");
                    $this->faild = 1;
                } elseif ($output['type'] == 'info') {
                    $this->writeInfo($output['msg'] . "\n");
                }
            }
        }
    }


    private function upSingleModuleMigrations($moduleName, $all, $force, $showInfo = true)
    {
        $this->checkModuleIsValid($moduleName);
        $route=Route::$instance;
        if(in_array($moduleName,$route->modulesInVendor)){
            $mkDir = __DIR__ . "/../../../../../vendor/joonika/module-" . $moduleName . "/src/Database/Migration";
        }else{
            $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Database/Migration";
        }
        if (count(glob($mkDir . '/*')) === 0) {
            if ($showInfo) {
                $this->writeInfo('there isn\'t any migration in this module');
            }
        } else {
            $namespace = "\Modules\\$moduleName\Database\Migration";
            if ($all) {
                foreach (glob($mkDir . '/*.php') as $migrationFile) {
                    $migrationFile = basename($migrationFile, '.php');
                    $newNamespace = $namespace . "\\$migrationFile";
                    $migrationClass = new $newNamespace('up', ['options' => ["force" => $force]], $moduleName);
                    $this->writeOutputs($migrationClass->output);
                    if ($this->faild) {
                        return 1;
                    }
                }
                $this->writeInfo('all migrations executed successfuly' . PHP_EOL);
            } else {
                $i = 1;
                $migrationPointer = [];
                $migrationPointer[$i] = 'All';
                $this->writeInfo("[1] $migrationPointer[1]");
                $i++;
                foreach (glob($mkDir . '/*.php') as $migrationFile) {
                    $migrationFile = basename($migrationFile, '.php');
                    echo "[$i] $migrationFile" . PHP_EOL;
                    $migrationPointer[$i] = $migrationFile;
                    $i++;
                }
                $migrationNumber = null;
                $this->ask('Enter migration number to execute', $migrationNumber, true);
                if (in_array($migrationNumber, array_keys($migrationPointer))) {
                    if ($migrationNumber == 1) {
                        foreach (glob($mkDir . '/*.php') as $migrationFile) {
                            $migrationFile = basename($migrationFile, '.php');
                            $newNamespace = $namespace . "\\$migrationFile";
                            $runMig = new $newNamespace('up', ['options' => ['force' => $force]], $moduleName);
                            $this->writeOutputs($runMig->output);
                            if ($this->faild) {
                                return 1;
                            }
                        }
                    } else {
                        $namespace .= "\\$migrationPointer[$migrationNumber]" . "Migration";
                        $runMig = new $namespace('up', ['options' => ['force' => $force]], $moduleName);
                        $this->writeOutputs($runMig->output);
                        if ($this->faild) {
                            return 1;
                        }
                        $this->writeOutPut($migrationPointer[$migrationNumber] . " migrations executed successfuly" . PHP_EOL);
                    }
                } else {
                    $this->writeError('your selected option is invalid');
                    return 1;
                }
            }
        }
    }

    public function down()
    {
        $k = 1;
        $modulesNameArray = [];
        $all = $this->checkOptions('all');
        $moduleName = strtolower($this->checkInputArguments('moduleName'));
        if (empty($moduleName)) {
            foreach (glob(JK_DIR_MODULES() . '/*') as $modules) {
                $module = basename($modules);
                echo "[$k] $module" . PHP_EOL;
                $modulesNameArray[$k++] = $module;
            }
            $moduleNumber = null;
            $this->ask('Please enter option for module select ( you can exit with  Ctrl + C  )', $moduleNumber, true);
            if (!in_array($moduleNumber, array_keys($modulesNameArray))) {
                $this->writeOutPut('your selected option is invalid');
                die('');
            }
            $moduleName = $modulesNameArray[$moduleNumber];
        }
        $this->checkModuleIsValid($moduleName);
        $route=Route::$instance;
        if(in_array($moduleName,$route->modulesInVendor)){
            $mkDir = __DIR__ . "/../../../../../vendor/joonika/module-" . $moduleName . "/src/Database/Migration";
        }else{
            $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Database/Migration";
        }
        if (count(glob($mkDir . '/*')) === 0) {
            $this->writeError('there isn\'t any migration in this module');
        } else {
            $namespace = "\Modules\\$moduleName\Database\Migration";
            if ($all) {
                foreach (glob($mkDir . '/*Migration.php') as $migrationFile) {
                    $migrationFile = basename($migrationFile, '.php');
                    $newNamespace = $namespace . "\\$migrationFile";
                    new $newNamespace('down');
                }
                $this->writeInfo('all migrations dropped successfuly' . PHP_EOL);
                die('');
            } else {
                $i = 1;
                $migrationPointer = [];
                $migrationPointer[$i] = 'All';
                $this->writeInfo("[1] $migrationPointer[1]");
                $i++;
                foreach (glob($mkDir . '/*Migration.php') as $migrationFile) {
                    $migrationFile = basename($migrationFile, 'Migration.php');
                    echo "[$i] $migrationFile" . PHP_EOL;
                    $migrationPointer[$i] = $migrationFile;
                    $i++;
                }
                $migrationNumber = null;
                $this->ask('Enter migration number to drop from database', $migrationNumber, true);
                if (in_array($migrationNumber, array_keys($migrationPointer))) {
                    if ($migrationNumber == 1) {
                        foreach (glob($mkDir . '/*Migration.php') as $migrationFile) {
                            $newNamespace = null;
                            $migrationFile = basename($migrationFile, '.php');
                            $newNamespace = $namespace . "\\$migrationFile";
                            new $newNamespace('down');
                        }
                        $this->writeOutPut('all migrations drops executed successfuly' . PHP_EOL);
                        die('');
                    } else {
                        $namespace .= "\\$migrationPointer[$migrationNumber]" . "Migration";
                        new $namespace('down');
                        $this->writeOutPut(PHP_EOL);
                    }
                } else {
                    $this->writeError('your selected option is invalid');
                }
            }
        }
    }

}