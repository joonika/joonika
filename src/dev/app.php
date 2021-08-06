<?php


namespace Joonika\dev;


use Joonika\FS;
use Joonika\ManageTables;
use Joonika\Traits\Repository;

class app extends baseCommand
{
    use Repository;

    public static function commandsList()
    {
        return [
            "app:init" => [
                "title" => "install joonika",
                "arguments" => ['installModuleTable'],
            ],
            "app:public" => [
                "title" => "update public",
            ],
        ];
    }

    public function init()
    {
        $defaultDirectories = [
            'config' => ['websites'],
            'modules',
            'public',
            'storage' => [
                'views',
                'files',
                'langs',
                'logs'
            ],
            'themes' => ['sample/Views'],
        ];

        if ($this->io->confirm("Are you ready? \t", true)) {

            $this->writeInfo("****   Installing new app   ****" . "\n");

            $allowAnswer = false;
            $installJKTables = null;
            while (!$allowAnswer) {
                $this->ask('install tables of joonika? [ no (default) / yes ]', $installJKTables, false);
                switch (strtolower(trim($installJKTables))) {
                    case "yes":
                        $allowAnswer = true;
                        break;
                    case "no";
                        $allowAnswer = true;
                        break;
                    case "";
                        $installJKTables = "no";
                        $allowAnswer = true;
                        break;
                }
            }

            if ($installJKTables == "yes") {
                $this->writeInfo("1- Installing Joonika tables: ..." . "\n");
                $this->configFileIsRequired();
                if (!is_null($this->configureFile)) {
                    $installTable = false;
                    if (!empty($this->configureFile['database'])) {
//                        $installTable = ManageTables::createAllJkTables($this->configureFile['database']);
                    } else {
                        $this->writeError("\tdatabase configuration not found");
                    }
                }
                if ($installTable) {
                    $this->writeSuccess("\tresult : success" . "\n");
                } else {
                    $this->writeError("\tresult : failed" . "\n");
                }
            } else {
                $this->writeInfo("1- Install Joonika tables canceled" . "\n");
            }


            $this->writeInfo("2- Installing Modules tables: ..." . "\n");

            $installModulesTables = strtolower($this->checkInputArguments('installModuleTable'));

            $allowAnswer = false;
            $installModulesTables = null;
            while (!$allowAnswer) {
                $this->ask('install tables of modules? [ no (default) / yes ]', $installModulesTables, false);
                switch (strtolower(trim($installModulesTables))) {
                    case "yes":
                        $allowAnswer = true;
                        break;
                    case "no";
                        $allowAnswer = true;
                        break;
                    case "";
                        $installModulesTables = "no";
                        $allowAnswer = true;
                        break;
                }
            }

            if ($installModulesTables == "yes") {
                $this->configFileIsRequired();
                if (!is_null($this->configureFile)) {
                    if (!empty($this->configureFile['database'])) {
                        $databaseInfo = $this->configureFile['database'];
                        if (checkArraySize(listModules())) {
                            $i = 1;
                            foreach (listModules() as $module) {
                                $this->writeInfo("\t2-" . $i . "- installing tables of " . $module . " ..." . "\n");
                                $createTable = ManageTables::moduleTablesExecute($module, $databaseInfo, 0);
                                if ($createTable['status']) {
                                    $this->writeSuccess("\t\tresult : success" . "\n");
                                } else {
                                    $this->writeError("\t\tresult : failed - " . $createTable['msg'] . "\n");
                                }
                                $i++;
                            }
                        } else {
                            $this->writeError("\tresult : failed - not found any module" . "\n");
                        }
                    } else {
                        $this->writeError("\tdatabase configuration not found");
                    }
                }
            } else {
                $this->writeError("\tYou did not want to install tables of modules." . "\n");
            }

            $this->writeInfo("3- Create initial directories ...");
            foreach ($defaultDirectories as $key => $val) {
                if (!is_array($defaultDirectories[$key])) {
                    $key = $val;
                }
                if (!FS::isExist(JK_SITE_PATH() . $key)) {
                    FS::mkDir(JK_SITE_PATH() . $key, 0777, true);
                }
                if (isset($defaultDirectories[$key]) && checkArraySize($defaultDirectories[$key])) {
                    foreach ($defaultDirectories[$key] as $subDir) {
                        if (!FS::isExist(JK_SITE_PATH() . $key . "/" . $subDir)) {
                            FS::mkDir(JK_SITE_PATH() . $key . "/" . $subDir, 0777, true);
                        }
                    }
                }
            }
            $this->writeSuccess("\tresult : success" . "\n");

            FS::removeDirectories(JK_SITE_PATH() . "public", false);

            $this->writeSuccess("4- Create initial files ..." . "\n");
            FS::copy(__DIR__ . '/temp/index.php', 'public/index.php');
            FS::copy(__DIR__ . '/temp/dev', 'dev');
            FS::copy(__DIR__ . '/temp/indexApp.php', 'index.php');
            if (!FS::isExistIsFile(JK_SITE_PATH() . "themes/sample/Views/index.php")) {
                FS::filePutContent(JK_SITE_PATH() . "themes/sample/Views/index.php", "<?php echo 'test'; ");
            }
            $this->writeSuccess("\tresult : success" . "\n");


            $this->writeInfo("5- Copy modules assets to public folder ..." . "\n");
            $i = 1;
            foreach (listModules() as $module) {
                $this->writeInfo("\t5-" . $i . "- Copy " . $module . " assets to public folder ..." . "\n");
                FS::copyDirectories(JK_SITE_PATH() . "modules" . $module . '/assets', 'public/modules/' . $module . '/assets');
                $this->writeSuccess("\t\tresult : success" . "\n");
                $i++;
            }

            $this->writeInfo("6- Copy Joonika assets to public folder ..." . "\n");
            FS::copyDirectories(JK_SITE_PATH() . 'vendor/joonika/joonika/src/assets', 'public/assets');
            $this->writeSuccess("\tresult : success" . "\n");

            $this->writeInfo("7- Copy themes assets to public folder ..." . "\n");

            $themes = FS::getDirectories(JK_SITE_PATH() . 'themes');
            $i = 1;
            foreach ($themes as $theme) {
                $this->writeInfo("\t8-" . $i . "- Copy " . $theme . " assets to public folder ..." . "\n");
                \Joonika\FS::copyDirectories(JK_SITE_PATH() . 'themes/' . $theme . '/assets', 'public/themes/' . $theme . '/assets');
                $this->writeSuccess("\t\tresult : success" . "\n");
                $i++;
            }


            if (!FS::isExistIsFile(JK_SITE_PATH() . "config/websites/sample.yaml.sample")) {
                $this->writeInfo("9- Create site config ...");
                FS::copy(__DIR__ . '/temp/SiteConfigs.txt', 'config/websites/sample.yaml.sample');
                $this->writeSuccess("\tresult : success" . "\n");
            }

            $this->writeSuccess("\n===============================================");
            $this->writeSuccess("install process is finished");
            $this->writeSuccess("\n===============================================");
        } else {
            $this->writeError("canceled");
        }
    }

    public function public()
    {
        $this->io->title("The public folder is updating ...");
        $publicFileExist = FS::isExist(JK_SITE_PATH() . "public");
        if ($publicFileExist) {
            FS::removeDirectories(JK_SITE_PATH() . "public", true);
        }

        FS::mkDir(JK_SITE_PATH() . "public", 0777, true);


        $modules = FS::getDirectories(JK_SITE_PATH() . 'modules');
        $themes = FS::getDirectories(JK_SITE_PATH() . 'themes');

        $this->writeInfo("1- Copy modules assets to public folder ..." . "\n");
        $i = 1;
        if (!empty($modules)) {
            foreach ($modules as $module) {
                $this->writeInfo("\t1-" . $i . "- Copy " . $module . " assets to public folder ..." . "\n");
                \Joonika\FS::copyDirectories(JK_SITE_PATH() . 'modules/' . $module . '/assets', 'public/modules/' . $module . '/assets');
                $this->writeSuccess("\t\tresult : success" . "\n");
                $i++;
            }
        }

        $this->writeInfo("2- Copy themes assets to public folder ..." . "\n");

        $i = 1;
        if (!empty($themes)) {
            foreach ($themes as $theme) {
                $this->writeInfo("\t2-" . $i . "- Copy " . $theme . " assets to public folder ..." . "\n");
                \Joonika\FS::copyDirectories(JK_SITE_PATH() . 'themes/' . $theme . '/assets', 'public/themes/' . $theme . '/assets');
                $this->writeSuccess("\t\tresult : success" . "\n");
                $i++;
            }
        }

        $this->writeInfo("3- Copy Joonika assets to public folder ..." . "\n");
        \Joonika\FS::copyDirectories(JK_SITE_PATH() . 'vendor/joonika/joonika/src/assets', 'public/assets');
        $this->writeSuccess("\tresult : success" . "\n");

        $this->writeInfo("4- Copy Joonika vendor modules assets to public folder ..." . "\n");
        $modulesInVendor = glob(JK_SITE_PATH() . 'vendor/joonika/module-*');
        if (!empty($modulesInVendor)) {
            foreach ($modulesInVendor as $moduleInVendor) {
                $moduleName = explode('-', $moduleInVendor);
                if (sizeof($moduleName) == 2) {
                    $moduleCheckName = $moduleName[1];
                    \Joonika\FS::copyDirectories($moduleInVendor . '/src/assets', 'public/modules/' . $moduleCheckName . '/assets');
                }
            }
        }
        $this->writeSuccess("\tresult : success" . "\n");


        $this->writeInfo("5- Create index.php ..." . "\n");
        if (!FS::isExistIsFile(JK_SITE_PATH() . "public/index.php")) {
            $content = "<?php\nrequire __DIR__ . '/../index.php';\n";
            FS::filePutContent(JK_SITE_PATH() . "public" . DS() . "index.php", $content);
        }
        $this->writeSuccess("\tresult : success" . "\n");

        $this->writeInfo("6- Create htaccess  ..." . "\n");
        if (!FS::isExistIsFile(JK_SITE_PATH() . "public/.htaccess")) {
            FS::filePutContent(JK_SITE_PATH() . "public" . DS() . ".htaccess", FS::fileGetContent(__dir__ . "/temp/.htaccess"));
        }
        $this->writeSuccess("\tresult : success" . "\n");


        $this->writeSuccess("\n===============================================");
        $this->writeSuccess("\n" . "Public directory is updated");
        $this->writeSuccess("\n===============================================");
        $this->output->writeln("update finished");
        return 0;

    }

}