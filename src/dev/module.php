<?php


namespace Joonika\dev;


use Joonika\FS;
use Joonika\ManageTables;
use Joonika\Traits\Repository;
use Modules\api\Controllers\apiController;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class module extends baseCommand
{

    use Repository;
    public static $defaultDirectories = ['assets', 'src', 'Controllers', 'Middlewares', 'Views', 'Models', 'langs', 'database', 'console', 'Events', 'Listeners', 'Providers'];
    public static $defaultFiles = ['Router.php'];

    public static function commandsList()
    {
        return [
            "module:create" => [
                "title" => "Create new module",
                "arguments" => ["name"],
                "options" => [
                    'dd' => [
                        "desc" => "Create module's default directories . default directoies are : " . self::getDefaultDirectories()
                    ],
                    'df' => [
                        "desc" => "Create module's default files . default directoies are : " . self::getDefaultFiles()
                    ],
                ]
            ],
            "module:controller" => [
                "title" => "Create new controller for module",
                "arguments" => ["name", 'moduleName'],
                "options" => [
                    'res' => [
                        "desc" => "Create resource controller"
                    ],
                    'api' => [
                        "desc" => "Create api controller"
                    ]
                ]
            ],
            "module:install" => [
                "title" => "Install a special module",
                "arguments" => ['name'],
                "options" => [
                    'all' => [
                        "desc" => "install all modules tables"
                    ],
                    'update' => [
                        "desc" => "update module tables"
                    ]
                ]
            ],
            "module:installReqired" => [
                "title" => "Install required modules"
            ],
            "module:middleware" => [
                "title" => "Create new middleware for module",
                "arguments" => ["name", 'moduleName'],
                "options" => [
                    'global' => [
                        "desc" => "Create global middleware"
                    ]
                ]
            ],
            "module:event" => [
                "title" => "Create new event for module",
                "arguments" => ["name", 'moduleName'],
            ],
            "module:listener" => [
                "title" => "Create new listener for module",
                "arguments" => ["name", 'moduleName'],
            ],
            "module:model" => [
                "title" => "Create new model for module",
                "arguments" => ["name", 'moduleName', 'tableName'],
            ],
            "module:test" => [
                "title" => "Create new test for module",
                "arguments" => ["name", 'moduleName'],
                "options" => [
                    'global' => [
                        "desc" => "Create global middleware"
                    ]
                ]
            ],
            "module:migration" => [
                "title" => "Migrate schema to database",
                "arguments" => ["moduleName"],
                "options" => [
                    'create' => [
                        "desc" => "Create migration template"
                    ],
                    'migrate' => [
                        "desc" => "migrate to database"
                    ],
                ]
            ],
            "module:backupFiles" => [
                "title" => "Backup module as zip file",
                "arguments" => ["moduleName", 'cmt', 'pass'],
                "options" => [
                    'comment' => [
                        "desc" => "set comment for your zip file"
                    ],
                    'password' => [
                        "desc" => "set password for your zip file"
                    ],
                ]
            ],
            "module:restoreBackupFile" => [
                "title" => "Restore backup file",
                "arguments" => ["moduleName", 'fn', 'ow'],
                "options" => [
                    'install' => [
                        "desc" => "install module backup file DB"
                    ],
                    'fileName' => [
                        "desc" => "your specific zip file name"
                    ],
                    'overWrite' => [
                        "desc" => "if choose this option you will over write your module with this backup file"
                    ],
                ]
            ],
            "module:console" => [
                "title" => "Create Cli for module",
                "arguments" => ["moduleName", 'name'],
                "options" => [
                    'name' => [
                        "desc" => "Another name except module name"
                    ],
                ]
            ],
            "module:helper" => [
                "title" => "Create helper function file for module",
                "arguments" => ["moduleName"],
            ],
            "module:remove" => [
                "title" => "Remove module",
                "arguments" => ["moduleName"],
                "options" => [
                    'db' => [
                        "desc" => "remove module tables"
                    ],
                ]
            ],
            "module:rename" => [
                "title" => "Rename module",
                "arguments" => ["moduleName", "newName"],
                "options" => [
                    'db' => [
                        "desc" => "remove module tables"
                    ],
                ]
            ],
            "module:clone" => [
                "title" => "Clone module",
                "arguments" => ["moduleName", "newName"],
                "options" => [
                    'db' => [
                        "desc" => "remove module tables"
                    ],
                ]
            ],
            "module:config" => [
                "title" => "Create config file for module",
                "arguments" => ["moduleName"]
            ],
        ];
    }

    public static function getDefaultDirectories()
    {
        $out = '';
        foreach (self::$defaultDirectories as $dir) {
            $out .= " '" . $dir . "' ";
        }
        return $out;
    }

    public static function getDefaultFiles()
    {
        $out = '';
        foreach (self::$defaultFiles as $dir) {
            $out .= " '" . $dir . "' ";
        }
        return $out;
    }

    public function create()
    {
        $moduleName = $this->checkInputArguments('name');
        $createDefaultDirectories = $this->checkOptions('dd');
        $createDefaultFiles = $this->checkOptions('df');

        $listDirectories = [];
        $listFiles = [];

        $name = '';

        if (!$moduleName) {
            $this->writeOutPut('Please enter module name');
            $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $name, true);
        } else {
            $name = $moduleName;
        }


        if ($createDefaultDirectories) {
            foreach (self::$defaultDirectories as $dir) {
                if ($this->io->confirm("Do you want to create '" . $dir . "' ? ")) {
                    $listDirectories[] = $dir;
                }
            }
            $extraDirectories = $this->io->ask("If you want to create extra directories please type thats name's -- seperate with < | > --");
            if ($extraDirectories != '') {
                $extraDirectories = explode('|', $extraDirectories);
                foreach ($extraDirectories as $dir) {
                    $listDirectories[] = trim($dir);
                }
            }
        }

        if ($createDefaultFiles) {
            foreach (self::$defaultFiles as $file) {
                if ($this->io->confirm("Do you want to create '" . $file . "' ? ")) {
                    $listFiles[] = $file;
                }
            }
            $extraFiles = $this->io->ask("If you want to create extra files please type thats name's -- seperate with < | > --");
            if ($extraFiles != '') {
                $extraFiles = explode('|', $extraFiles);
                foreach ($extraFiles as $file) {
                    if (stripos($file, '.') === false) {
                        $listFiles[] = trim($file . '.php');
                    } else {
                        $listFiles[] = trim($file);
                    }
                }
            }
        }

        $this->writeOutPut("creating new module ... ");

        \Joonika\FS::mkDir(__DIR__ . '/../../../../../modules/' . $name, 0777, true);
        foreach ($listDirectories as $dir) {
            \Joonika\FS::mkDir(__DIR__ . '/../../../../../modules/' . $name . '/' . $dir, 0777, true);
        }

        foreach ($listFiles as $file) {
            $path = __DIR__ . "/../../../../../modules/{$name}/{$file}";
            $baseName = pathinfo($file)['filename'];
            $getSample = FS::fileGetContent(__dir__ . "/../templates/files/modules/{$baseName}.txt");
            FS::filePutContent($path, $getSample);
        }

        $this->writeSuccess("Module : " . $name . ' created successfully . ');
    }

    public function test()
    {
        $name = $this->checkInputArguments('name');
        $moduleName = strtolower($this->checkInputArguments('moduleName'));

        $this->ask('Please enter test name ( you can exit with  Ctrl + C  )', $name, true);

        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $moduleName, true);

        $this->checkModuleIsValid($moduleName);

        $namespace = "Modules\\$moduleName\Tests";

        \Joonika\FS::mkDir(__DIR__ . "/../../../../../modules/" . $moduleName . "/Tests", 0777, true);

        $path = explode('\\', $name);
        $name = array_pop($path);
        $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Tests";
        if (sizeof($path) > 0) {
            foreach ($path as $folder) {
                $mkDir = $mkDir . '/' . $folder;
                if (!\Joonika\FS::isDir($mkDir)) {
                    \Joonika\FS::mkDir($mkDir, 0777);
                }
            }
        }

        $getSample = FS::fileGetContent(__dir__ . "/../templates/files/test/test.txt");


        $path = sizeof($path) > 0 ? '\\' . join('\\', $path) : '';
        $getSample = str_replace("{namespace}", $namespace . $path, $getSample);
        $getSample = str_replace("{name}", $name, $getSample);

        $filePath = $mkDir . '/' . $name . 'Test.php';

        //-------------------
        $oldPhpUnitXml = FS::fileGetContent(__dir__ . "/../../../../bin/phpunit.xml");
        $pattern = "<testsuites>(.*)<\/testsuites>";
        preg_match_all("/" . $pattern . "/ms", $oldPhpUnitXml, $variables);
        $testRouteTemplate = "\t" . '<testsuite name="{testName}">' . "\n\t\t\t" . '<directory suffix="{testSuffix}">{testPath}</directory>' . "\n\t\t" . '</testsuite>';
        $arr2 = [];
        if (is_array($variables) && sizeof($variables) > 0) {
            $arr2 = $variables[1];
        }
        $m = str_replace("{testName}", $name, $testRouteTemplate);
        $m = str_replace("{testSuffix}", 'Test.php', $m);
        $m = str_replace("{testPath}", "../../modules/" . $moduleName . "/Tests", $m);
        $arr2[] = $m . "\n";
        $arr2 = implode(" ", $arr2);
        $getSample2 = FS::fileGetContent(__dir__ . "/../templates/files/test/phpunitxml.txt");

        $getSample2 = str_replace("{testPath}", $arr2, $getSample2);
        \Joonika\FS::filePutContent($filePath, $getSample);
        \Joonika\FS::filePutContent(__dir__ . "/../../../../bin/phpunit.xml", $getSample2);

        $this->writeSuccess("Test -- " . $name . " -- created successfully");
    }

    public function middleware()
    {
        $name = $this->checkInputArguments('name');
        $moduleName = strtolower($this->checkInputArguments('moduleName'));
        $isGlobal = $this->checkOptions('global');

        $this->ask('Please enter middleware name ( you can exit with  Ctrl + C  )', $name, true);
        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $moduleName, true);

        $this->checkModuleIsValid($moduleName);

        $namespace = "Modules\\$moduleName\Middlewares";

        \Joonika\FS::mkDir(__DIR__ . "/../../../../../modules/" . $moduleName . "/Middlewares", 0777, true);

        $path = explode('\\', $name);

        $name = array_pop($path);

        $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Middlewares";
        if (sizeof($path) > 0) {
            foreach ($path as $folder) {
                $mkDir = $mkDir . '/' . $folder;
                if (!\Joonika\FS::isDir($mkDir)) {
                    \Joonika\FS::mkDir($mkDir, 0777);
                }
            }
        }

        if ($isGlobal) {
            $name = $name . '_global';
        }
        $getSample = FS::fileGetContent(__dir__ . "/../templates/files/middleware/middleware.txt");

        $path = sizeof($path) > 0 ? '\\' . join('\\', $path) : '';
        $getSample = str_replace("{namespace}", $namespace . $path, $getSample);
        $getSample = str_replace("{name}", $name, $getSample);

        $filePath = $mkDir . '/' . $name . '.php';

        FS::filePutContent($filePath, $getSample);

        $this->writeSuccess("Middleware created successfully");

    }

    public function model()
    {
        $name = $this->checkInputArguments('name');
        $moduleName = strtolower($this->checkInputArguments('moduleName'));
        $tableName = strtolower($this->checkInputArguments('tableName'));

        $this->ask('Please enter model name ( you can exit with  Ctrl + C  )', $name, true);
        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $moduleName, true);
        $this->ask('Please enter table name ( you can exit with  Ctrl + C  )', $tableName, false);

        $this->checkModuleIsValid($moduleName);

        $namespace = "Modules\\$moduleName\Models";

        \Joonika\FS::mkDir(__DIR__ . "/../../../../../modules/" . $moduleName . "/Models", 0777, true);

        $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Models";
        $getSample = FS::fileGetContent(__dir__ . "/../templates/files/model/model.txt");

        $getSample = str_replace("{namespace}", $namespace, $getSample);
        $getSample = str_replace("{name}", ucfirst($name), $getSample);
        if ($tableName) {
            $getSample = str_replace("{tableName}", $tableName, $getSample);
        } else {
            $tableName = '';
            $last_letter = strtolower($name[strlen($name) - 1]);
            switch ($last_letter) {
                case 'y':
                    $tableName = substr($name, 0, -1) . 'ies';
                    break;
                case 's':
                    $tableName = $name . 'es';
                    break;
                default:
                    $tableName = $name . 's';
                    break;
            }
            $getSample = str_replace("{tableName}", $tableName, $getSample);
        }

        $filePath = $mkDir . '/' . ucfirst($name) . '.php';
        FS::filePutContent($filePath, $getSample);

        $this->writeSuccess("Model created successfully");

    }

    public function event()
    {
        $name = $this->checkInputArguments('name');
        $moduleName = strtolower($this->checkInputArguments('moduleName'));

        $this->ask('Please enter event name ( you can exit with  Ctrl + C  )', $name, true);
        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $moduleName, true);

        $this->checkModuleIsValid($moduleName);

        $namespace = "Modules\\$moduleName\Events";
        $providerNamespace = "Modules\\$moduleName\Providers";

        \Joonika\FS::mkDir(__DIR__ . "/../../../../../modules/" . $moduleName . "/Events", 0777, true);
        \Joonika\FS::mkDir(__DIR__ . "/../../../../../modules/" . $moduleName . "/Providers", 0777, true);

        $path = explode('\\', $name);

        $name = array_pop($path);

        $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Events";
        $providerMkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Providers";

        if (sizeof($path) > 0) {
            foreach ($path as $folder) {
                $mkDir = $mkDir . '/' . $folder;
                if (!\Joonika\FS::isDir($mkDir)) {
                    \Joonika\FS::mkDir($mkDir, 0777);
                }
            }
        }

        $getSample = FS::fileGetContent(__dir__ . "/../templates/files/eventListener/events.txt");

        $path = sizeof($path) > 0 ? '\\' . join('\\', $path) : '';
        $getSample = str_replace("{namespace}", $namespace . $path, $getSample);
        $getSample = str_replace("{name}", $name, $getSample);

        $filePath = $mkDir . '/' . $name . '.php';

        FS::filePutContent($filePath, $getSample);

        //------------- create eventListener file
        if (!FS::isExistIsFileIsReadable(__DIR__ . "/../../../../../modules/" . $moduleName . "/Providers/EventListener.php")) {
            $getSample = FS::fileGetContent(__dir__ . "/../templates/files/eventListener/eventListener.txt");
            $getSample = str_replace("{namespace}", $providerNamespace, $getSample);
            $getSample = str_replace("{name}", 'EventListener', $getSample);
            $filePath = $providerMkDir . '/EventListener.php';
            FS::filePutContent($filePath, $getSample);
        }

        $this->writeSuccess("Event created successfully");

    }

    public function listener()
    {
        $name = $this->checkInputArguments('name');
        $moduleName = strtolower($this->checkInputArguments('moduleName'));

        $this->ask('Please enter listener name ( you can exit with  Ctrl + C  )', $name, true);
        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $moduleName, true);

        $this->checkModuleIsValid($moduleName);

        $namespace = "Modules\\$moduleName\Listeners";
        $providerNamespace = "Modules\\$moduleName\Providers";

        \Joonika\FS::mkDir(__DIR__ . "/../../../../../modules/" . $moduleName . "/Listeners", 0777, true);
        \Joonika\FS::mkDir(__DIR__ . "/../../../../../modules/" . $moduleName . "/Providers", 0777, true);

        $path = explode('\\', $name);

        $name = array_pop($path);

        $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Listeners";
        $providerMkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Providers";

        if (sizeof($path) > 0) {
            foreach ($path as $folder) {
                $mkDir = $mkDir . '/' . $folder;
                if (!\Joonika\FS::isDir($mkDir)) {
                    \Joonika\FS::mkDir($mkDir, 0777);
                }
            }
        }

        $getSample = FS::fileGetContent(__dir__ . "/../templates/files/eventListener/listenere.txt");

        $path = sizeof($path) > 0 ? '\\' . join('\\', $path) : '';
        $getSample = str_replace("{namespace}", $namespace . $path, $getSample);
        $getSample = str_replace("{name}", $name, $getSample);

        $filePath = $mkDir . '/' . $name . '.php';

        FS::filePutContent($filePath, $getSample);

        //------------- create eventListener file
        if (!FS::isExistIsFileIsReadable(__DIR__ . "/../../../../../modules/" . $moduleName . "/Providers/EventListener.php")) {
            $getSample = FS::fileGetContent(__dir__ . "/../templates/files/eventListener/eventListener.txt");
            $getSample = str_replace("{namespace}", $providerNamespace, $getSample);
            $getSample = str_replace("{name}", 'EventListener', $getSample);
            $filePath = $providerMkDir . '/EventListener.php';
            FS::filePutContent($filePath, $getSample);
        }

        $this->writeSuccess("Listener created successfully");

    }

    public function controller()
    {
        $name = $this->checkInputArguments('name');
        $moduleName = strtolower($this->checkInputArguments('moduleName'));
        $isResource = $this->checkOptions('res');
        $isApi = $this->checkOptions('api');

        $this->ask('Please enter controller name ( you can exit with  Ctrl + C  )', $name, true);
        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $moduleName, true);

        $this->checkModuleIsValid($moduleName);

        $namespace = "Modules\\$moduleName\Controllers";

        \Joonika\FS::mkDir(__DIR__ . "/../../../../../modules/" . $moduleName . "/Controllers", 0777, true);

        $path = explode('\\', $name);

        $name = array_pop($path);

        $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Controllers";
        if (sizeof($path) > 0) {
            foreach ($path as $folder) {
                $mkDir = $mkDir . '/' . $folder;
                if (!\Joonika\FS::isDir($mkDir)) {
                    \Joonika\FS::mkDir($mkDir, 0777);
                }
            }
        }

        $getSample = '';
        if ($isResource) {
            $getSample = FS::fileGetContent(__dir__ . "/../templates/files/controller/resourceController.txt");
        } else {
            $getSample = FS::fileGetContent(__dir__ . "/../templates/files/controller/Controller.txt");
        }


        if ($isApi) {
            $namespace = "Modules\\$moduleName\Controllers\api";
            $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Controllers/api";
            \Joonika\FS::mkDir(__DIR__ . "/../../../../../modules/" . $moduleName . "/Controllers/api", 0777, true);
            $getSample = str_replace('extends Controller', 'extends apiController', $getSample);
            $getSample = str_replace('use Joonika\Controller;', 'use Modules\api\Controllers\apiController;', $getSample);
        }

        $path = sizeof($path) > 0 ? '\\' . join('\\', $path) : '';
        $getSample = str_replace("{namespace}", $namespace . $path, $getSample);
        $getSample = str_replace("{name}", $name, $getSample);

        $filePath = $mkDir . '/' . $name . '.php';

        FS::filePutContent($filePath, $getSample);

        $this->writeSuccess("Controller created successfully");

    }

    public function install()
    {
        $update = $this->checkOptions('update');
        $allModules = $this->checkOptions('all');

        if ($update) {
            $opName = 'Updating';
            $update = 1;
        } else {
            $opName = 'Installing';
            $update = 0;
        }

        if ($allModules) {
            $this->writeInfo("Installing all Modules tables: ..." . "\n");
            if (checkArraySize(listModules())) {
                $i = 1;
                foreach (listModules() as $module) {
                    $this->writeInfo("\t" . $i . "- $update " . $module . " tables ..." . "\n");
                    $createTable = ManageTables::moduleTablesExecute($module, $this->databaseInfo, $update);
                    if ($createTable['status']) {
                        $this->writeSuccess("\t\tresult : success" . "\n");
                    } else {
                        $this->writeError("\t\tresult : failed - " . $createTable['msg'] . "\n");
                    }
                    $i++;
                }
            }
        } else {
            $name = strtolower($this->checkInputArguments('name'));

            $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $name, true);

            $this->checkModuleIsValid($name);

            $this->writeInfo("$opName $name tables: ..." . "\n");

            $createTable = ManageTables::moduleTablesExecute($name, $this->databaseInfo, $update);

            if ($createTable['status']) {
                $this->writeOutPut($name . " queries executed successfully :)");
            } else {
                $this->writeOutPut($createTable['msg']);
            };
        }

    }

    public function installReqired()
    {
        $requiredModules = self::getRequiredModulesForInitSystem($this->databaseInfo);
        $requiredText = "";
        if (checkArraySize($requiredModules)) {
            foreach ($requiredModules as $rModule) {
                $requiredText .= " , " . $rModule['name'];
            }
            if ($this->io->confirm("Do you want to install required modules ? \n required module is : " . trim($requiredText, ' ,'), true)) {
                $i = 1;
                foreach ($requiredModules as $rModule) {
                    $this->writeInfo($i . "- install module " . $rModule['name']);
                    $resultInstallRequiredModule = self::installRepository($rModule, $this->databaseInfo);
                    if ($resultInstallRequiredModule['code'] == "200") {
                        $this->writeSuccess("\t module" . $rModule['name'] . " installed");
                    } else {
                        $this->writeError("\t " . $resultInstallRequiredModule['msg']);
                    }
                    $i++;
                }
            }
        }
    }

    public function migration()
    {
        $moduleName = strtolower($this->checkInputArguments('moduleName'));
        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $moduleName, true);
        $this->checkModuleIsValid($moduleName);
        $create = $this->checkOptions('create');
        $migrate = $this->checkOptions('migrate');
        $type = $migrate ? 'migrate' : 'create';
        $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/Database/Migration";

        if (!is_dir(JK_DIR_MODULES() . '/' . $moduleName . '/Database/Migration')) {
            \Joonika\FS::mkDir($mkDir, 0777);
        }

//        $namespace = "\Modules\\$moduleName\Database\Migration\\$moduleName"."Migration";
        $namespace = "\Modules\\$moduleName\Database\Migration";
//                $a = new $namespace();

        if (!$create & !$migrate) {
            $number = null;
            $this->writeOutPut('[1] create' . PHP_EOL . '[2] migrate');
            $this->ask('Enter your option: ', $number, true);
            if ($number == '1')
                $create = true;
            elseif ($number == '2')
                $migrate = true;
            else
                $this->writeOutPut('your selected option is invalid');
        }

        if ($create) {
            $newMigrationName = null;
            $this->ask('Enter migration name to create', $newMigrationName, true);

            $path = "$mkDir/$newMigrationName" . "Migration.php";
            $template = FS::fileGetContent(__dir__ . "/../templates/files/migration/migration.txt");
            $template = str_replace("{moduleName}", $moduleName, $template);
            $template = str_replace("{migrateName}", $newMigrationName, $template);
            FS::filePutContent($path, $template);

            $this->writeOutPut('\'' . $newMigrationName . '\' migration created in \'' . $moduleName . '\' module' . PHP_EOL);
            die();
        } elseif ($migrate) {
            $i = 1;
            $migrationPointer = [];
            foreach (glob($mkDir . '/*Migration.php') as $migrationFile) {
//                $migrationFile = last(explode('/',$migrationFile));
//                $migrationFile = substr($migrationFile,0,strpos($migrationFile,'.'));
                $migrationFile = basename($migrationFile, '.php');
                echo "[$i] $migrationFile" . PHP_EOL;
                $migrationPointer[$i] = $migrationFile;
                $i++;
            }
            $migrationNumber = null;
            $this->ask('Enter migration number to execute', $migrationNumber, true);
            if (in_array($migrationNumber, array_keys($migrationPointer))) {
                $namespace .= "\\$migrationPointer[$migrationNumber]";
                new $namespace;
                $this->writeOutPut(PHP_EOL);
            } else {
                $this->writeOutPut('your selected option is invalid');
            }
        }
    }

    public function backupFiles()
    {
        $this->writeError('Default loacation off backup file is < storage/files > directory');
        $name = strtolower($this->checkInputArguments('moduleName'));
        $comment = strtolower($this->checkInputArguments('cmt'));
        $password = strtolower($this->checkInputArguments('pass'));


        $cmt = $this->checkOptions('comment');
        $pass = $this->checkOptions('password');


        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $name, true);
        $this->checkModuleIsValid($name);
        if ($cmt) {
            $this->ask('Please enter comment', $comment, true);
        }
        if ($pass) {
            $this->ask('Please enter password', $password, true);
        }


        $comment = $comment ? $comment : null;
        $password = $password ? $password : null;


        $dir = FS::allFilesList(JK_SITE_PATH() . 'modules' . DS() . $name);
        $zip = JK_SITE_PATH() . 'storage/files/' . $name . '.zip';


        \Joonika\FS::createZipFile($zip, $dir, 'modules', $comment, $password);

        $this->writeSuccess('backup file created successfully in < ' . realpath($zip) . " > directory");
    }

    public function restoreBackupFile()
    {

        $this->writeError('We will try find a zip file like module name to restore,if you choose another name,please enter your specific name');

        $name = strtolower($this->checkInputArguments('moduleName'));

        $fn = strtolower($this->checkInputArguments('fn'));
        $ow = strtolower($this->checkInputArguments('ow'));

        $install = $this->checkOptions('install');
        $fileName = $this->checkOptions('fileName');
        $overWrite = $this->checkOptions('overWrite');

        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $name, true);

        $this->checkModuleIsValid($name);

        if ($fileName) {
            $this->ask('Please enter file name ( you can exit with  Ctrl + C  )', $fn, true);
        }
        $installDB = false;
        if ($install) {
            if ($overWrite) {
                $installDB = true;
            } else {
                $this->writeError('For install table\'s of ' . $name . ' module , --overWrite is required');
            }
        }
        $this->writeError('Default loacation off extract backup file is < storage/files > directory');

        $zipFileName = $fileName ? $fn . '.zip' : $name . '.zip';
        $filePath = JK_SITE_PATH() . 'storage/files/';
        $extractFilePath = $overWrite ? JK_SITE_PATH() . 'modules/' : $filePath;

        if (FS::isExistIsFile($filePath . $zipFileName)) {
            FS::extractZipFile($filePath . $zipFileName, $extractFilePath);
        }

        if ($installDB) {
            //----
        }

        $this->writeSuccess("Backup file restored successfully");
    }

    public function console()
    {
        $moduleName = strtolower($this->checkInputArguments('moduleName'));
        $name = strtolower($this->checkInputArguments('name'));


        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $moduleName, true);
        $this->checkModuleIsValid($moduleName);

        $finalName = $moduleName;

        $n = $this->checkOptions('name');
        if ($n) {
            $this->ask('Please enter name of Cli file', $name, true);
            $finalName = $name;
        }

        if ($name) {
            $finalName = $name;
        }

        $namespace = "Modules\\$moduleName\console";

        \Joonika\FS::mkDir(__DIR__ . "/../../../../../modules/" . $moduleName . "/console", 0777, true);

        $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/console";
        $getSample = FS::fileGetContent(__dir__ . "/../templates/files/console/console.txt");
        $getSample = str_replace("{namespace}", $namespace, $getSample);
        $getSample = str_replace("{name}", $finalName, $getSample);

        $filePath = $mkDir . '/' . $finalName . '.php';

        FS::filePutContent($filePath, $getSample);


        $this->writeSuccess("Cli file created successfully");

    }

    public function helper()
    {
        $moduleName = strtolower($this->checkInputArguments('moduleName'));

        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $moduleName, true);
        $this->checkModuleIsValid($moduleName);

        \Joonika\FS::mkDir(__DIR__ . "/../../../../../modules/" . $moduleName . "/inc", 0777, true);

        $mkDir = __DIR__ . "/../../../../../modules/" . $moduleName . "/inc";
        $filePath = $mkDir . '/helper.php';

        FS::filePutContent($filePath, '<?php ');
        $this->writeSuccess("Helper file created successfully");

    }

    public function remove()
    {
        $this->writeError('WARNING : If you delete the module, you will not be able to restore it.');

        $name = strtolower($this->checkInputArguments('moduleName'));

        $db = $this->checkOptions('db');
        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $name, true);

        $this->checkModuleIsValid($name);
        $dbRemove = false;
        if ($db) {
            if ($this->io->confirm("WARNING : If you delete the tables, you will not be able to restore it. continue ?", false)) {
                $dbRemove = true;
            }
        }

        $remove = false;
        if ($this->io->confirm("Are you sure ?", false)) {
            $remove = true;
        }

        if ($remove) {
            FS::removeDirectories(__DIR__ . '/../../../../../modules/' . $name, true);
            if ($dbRemove) {

            }
            $this->writeSuccess('Module : ' . $name . ' removed successfully');
        } else {
            $this->writeError('Remove module : ' . $name . ' canceled');
        }
    }

    private function cloneModule($name, $newName)
    {
        FS::mkDir(__DIR__ . " /../../../../../modules/" . $newName, 0777, true);
        $rootFiles = glob(__DIR__ . "/../../../../../modules/" . $name . DS() . '*.*');
        if (checkArraySize($rootFiles)) {
            foreach ($rootFiles as $rootFile) {
                $rContent = FS::fileGetContent($rootFile);

                $pattern = "Modules.*\\\\" . $name;
                preg_match_all("/" . $pattern . "/im", $rContent, $matches);
                if (checkArraySize($matches) && checkArraySize($matches[0])) {
                    foreach ($matches[0] as $match) {
                        $oldNameSpace = $matches[0];
                        $nameSpace = str_replace($name, $newName, $oldNameSpace);
                        $rContent = str_replace($oldNameSpace, $nameSpace, $rContent);
                    }
                }
                $dest = str_replace("/" . $name . "\\", '/' . $newName . '\\', $rootFile);
                $path = pathinfo($dest);
                FS::mkDir($path['dirname'], 0777, true);
                FS::fileWrite($dest, $rContent);
            }
        }

        foreach (['assets', 'components', 'langs'] as $item) {
            if (FS::isDir(__DIR__ . " /../../../../../modules/" . $name . DS() . $item)) {
                FS::copyDirectories(__DIR__ . " /../../../../../modules/" . $name . DS() . $item, 'modules/' . $newName . DS() . $item);
            }
        }
        foreach (['console', "Views", "Tests", "src", "Providers", 'Middlewares', "Models", "Listeners", "inc", "Events", "database", "Controllers", "console"] as $item) {
            $files = FS::allFilesList(__DIR__ . "/../../../../../modules/" . $name . DS() . $item);
            if (checkArraySize($files)) {
                foreach ($files as $file) {
                    $content = FS::fileGetContent($file);
                    $pattern = "Modules.*\\\\" . $name . "\\\.*;";
                    preg_match_all("/" . $pattern . "/im", $content, $matches);
                    if (checkArraySize($matches) && checkArraySize($matches[0])) {
                        foreach ($matches[0] as $match) {
                            $oldNameSpace = $matches[0];
                            $nameSpace = str_replace($name, $newName, $oldNameSpace);
                            $content = str_replace($oldNameSpace, $nameSpace, $content);
                        }
                    }
                    $dest = str_replace("\\" . $name . "\\", '\\' . $newName . '\\', $file);
                    $path = pathinfo($dest);
                    FS::mkDir($path['dirname'], 0777, true);
                    FS::fileWrite($dest, $content);
                }
            }
        }

    }

    public function clone()
    {
        $name = strtolower($this->checkInputArguments('moduleName'));
        $newName = strtolower($this->checkInputArguments('newName'));

        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $name, true);
        $this->checkModuleIsValid($name);
//        $db = $this->checkOptions('db');
        $this->ask('Please enter new name ( you can exit with  Ctrl + C  )', $newName, true);

        if ($name == $newName) {
            $newName = null;
            $this->ask('Please enter new name to clone module', $newName, true);
        }

        if ($name == $newName) {
            $this->writeError('The old name and the new name should not be the same.');
            return;
        }

        $this->writeInfo('Cloning module , please wait ... ');

        $this->cloneModule($name, $newName);

        $this->writeSuccess('Module has been clone successfully');
    }

    public function rename()
    {
        $name = strtolower($this->checkInputArguments('moduleName'));
        $newName = strtolower($this->checkInputArguments('newName'));

        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $name, true);
        $this->checkModuleIsValid($name);
//        $db = $this->checkOptions('db');
        $this->ask('Please enter new name ( you can exit with  Ctrl + C  )', $newName, true);

        if ($name == $newName) {
            $newName = null;
            $this->ask('Please enter new name to rename module', $newName, true);
        }

        if ($name == $newName) {
            $this->writeError('The old name and the new name should not be the same.');
            return;
        }

        $this->writeInfo('Renaming module , please wait ... ');

        $this->cloneModule($name, $newName);

        FS::removeDirectories(__DIR__ . " /../../../../../modules/" . $name);
        $this->writeSuccess('Module has been rename successfully');
    }

    public function config()
    {
        $name = strtolower($this->checkInputArguments('moduleName'));
        $this->ask('Please enter module name ( you can exit with  Ctrl + C  )', $name, true);
        $this->checkModuleIsValid($name);

        $mkDir = __DIR__ . "/../../../../../modules/" . $name . "/inc";

        if (!is_dir(JK_DIR_MODULES() . '/' . $name . '/inc')) {
            \Joonika\FS::mkDir($mkDir, 0777);
        }


        $namespace = "Modules\\$name\inc";
        $getSample = FS::fileGetContent(__dir__ . "/../templates/files/inc/Configs.txt");
        $getSample = str_replace("{namespace}", $namespace, $getSample);

        $filePath = $mkDir . '/Configs.php';

        FS::filePutContent($filePath, $getSample);

        $this->writeSuccess("Configs file created successfully");

    }

}