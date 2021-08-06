<?php


namespace Joonika\dev;


use Joonika\FS;
use Joonika\ManageTables;
use Joonika\Traits\Repository;
use Symfony\Component\Yaml\Yaml;

class app extends baseCommand
{
    use Repository;

    public AppCommand $app;

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

    public function __construct(AppCommand $app, $command = null, $connectToDataBase = false, $configFileIsRequired = false)
    {
        parent::__construct($app, $command, $connectToDataBase, $configFileIsRequired);
        $this->app = $app;
    }

    public function init()
    {

        $step = 1;

        if ($this->io->confirm("Are you ready? \t", true)) {

            $this->writeInfo("****   Installing new app   ****" . "\n");

            $this->writeInfo(($step++) . "/5 - Create initial directories ...");
            FS::mkDir(JK_SITE_PATH() . "config/websites", 0777, true);
            FS::mkDir(JK_SITE_PATH() . "modules", 0777, true);
            FS::mkDir(JK_SITE_PATH() . "public", 0777, true);
            FS::mkDir(JK_SITE_PATH() . "storage/views", 0777, true);
            FS::mkDir(JK_SITE_PATH() . "storage/files", 0777, true);
            FS::mkDir(JK_SITE_PATH() . "storage/langs", 0777, true);
            FS::mkDir(JK_SITE_PATH() . "storage/logs", 0777, true);
            FS::mkDir(JK_SITE_PATH() . "themes/sample/Views", 0777, true);

            $this->writeInfo(($step++) . "/5 - create config ...");

            $configArray = [
                "domain" => "localhost",
                "protocol" => 'https://',
                "defaultLang" => "en",
                "debug" => true,
                "language" => [
                    "config" => "n",
                    "name" => "english",
                    "slug" => "en",
                    "direction" => "ltr",
                    "locale" => "en_us",
                    "timezone" => "UTC",
                ],
                "database" => [
                    "config" => "n",
                    "host" => "localhost",
                    "db" => "db",
                    "user" => "root",
                    "pass" => "password",
                    "port" => 3306,
                    "charset" => "utf8",
                    "driver" => "mysql",
                ],
            ];
            $config = [];
            $this->ask('domain: (default: localhost)', $config['domain']);
            $this->ask('protocol: (default: https://)', $config['protocol']);
            $this->ask('defaultLang: ', $config['defaultLang']);
            $this->ask('debug: [ n / y ]', $config['debug']);
            $this->ask('secondary language ?: [ n(default) / y ]', $config['language']['config']);
            if ($config['language']['config'] == 'y') {
                $this->ask('language name: (english)', $config['language']['name']);
                $this->ask('language slug: (en)', $config['language']['slug']);
                $this->ask('language direction: (ltr,rtl)', $config['language']['direction']);
                $this->ask('language locale: (en_us)', $config['language']['locale']);
                $this->ask('language timezone: (UTC)', $config['language']['tz']);
            }
            $this->ask('database connectivity ?: [ n(default) / y ]', $config['database']['config']);
            if ($config['database']['config'] == 'y') {
                $this->ask('database server: (localhost)', $config['database']['host']);
                $this->ask('database name: (db)', $config['database']['db']);
                $this->ask('database user: (root)', $config['database']['user']);
                $this->ask('database pass: (password)', $config['database']['pass']);
                $this->ask('database port: (3306)', $config['database']['port']);
                $this->ask('database charset: (utf8)', $config['database']['charset']);
                $this->ask('database driver: (mysql)', $config['database']['driver']);
            }
            $configSet = [];
            $configSet['id'] = 1;
            $configSet['type'] = 'test';
            $configSet['domain'] = !empty($config['domain']) ? $config['domain'] : $configArray['domain'];
            $configSet['protocol'] = !empty($config['protocol']) ? $config['protocol'] : $configArray['protocol'];
            $configSet['defaultLang'] = !empty($config['defaultLang']) ? $config['defaultLang'] : $configArray['defaultLang'];
            $configSet['debug'] = !empty($config['debug']) ? $config['debug'] : $configArray['debug'];
            $configSet['theme'] = 'sample';
            if ($config['language']['config'] == 'y') {
                $configSet['languages'] = [];
                $t = [];
                unset($config['language']['config']);
                foreach ($config['language'] as $key => $val) {
                    $t[$key] = !empty($val) ? $val : $configArray['language'][$key];
                }
                $configSet['languages'][$config['language']['slug']] = $t;
            }
            if (!isset($configSet['languages']['en'])) {
                unset($configArray['language']['config']);
                $configSet['languages']['en'] = $configArray['language'];
            }
            if ($config['database']['config'] == 'y') {
                $t = [];
                unset($config['database']['config']);
                foreach ($config['database'] as $key => $val) {
                    $t[$key] = !empty($val) ? $val : $configArray['database'][$key];
                }
                unset($configArray['database']['config']);
                $configSet['database'] = $t;
            } else {
                unset($configArray['database']['config']);
                $configSet['database'] = $configArray['database'];
            }
            $path = JK_SITE_PATH() . 'config/websites/dev.yaml';
            $new_yaml = Yaml::dump($configSet, 5);
            file_put_contents($path, $new_yaml);

            $this->writeInfo(($step++) . "/5 - migration ...");

            $allowAnswer = false;
            $installJKTables = null;
            while (!$allowAnswer) {
                $this->ask('migration up? [ n (default) / y ]', $installJKTables, false);
                switch (strtolower(trim($installJKTables))) {
                    case "n":
                    case "y":
                        $allowAnswer = true;
                        break;
                    case "";
                        $installJKTables = "n";
                        $allowAnswer = true;
                        break;
                }
            }

            if ($installJKTables == "y") {
                $this->writeInfo("Installing Joonika tables: ..." . "\n");
                $this->configFileIsRequired();
                $requiredYamlFile = JK_SITE_PATH() . 'config/websites/dev.yaml';
                if (file_exists($requiredYamlFile)) {
                    $this->configureFile = yaml_parse_file($requiredYamlFile);
                }
                try {
                    $newMigration = new \Joonika\dev\migration($this->app, 'migration:upAll');
                    $newMigration->upAll();
                }catch (\Exception $exception){
                    $this->writeInfo("\tmigration failed: debug->: php joonika app:migration:runAll" . "\n");
                }
            }

            $this->writeSuccess("\tresult : success" . "\n");


            $this->writeSuccess("4- Create initial files ..." . "\n");

            FS::copy(__DIR__ . '/temp/index.php', 'public/index.php');
            FS::copy(__DIR__ . '/temp/joonika', 'joonika');
            FS::copy(__DIR__ . '/temp/indexApp.php', 'index.php');
            FS::copy(__DIR__ . '/temp/indexTheme.php', 'themes/sample/Views/index.php');

            $this->writeSuccess("\tresult : success" . "\n");

            $this->update();

            $this->writeSuccess("\n===============================================");
            $this->writeSuccess("install process is finished");
            $this->writeSuccess("\n===============================================");
        } else {
            $this->writeError("canceled");
        }
    }

    public function update()
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
                if (FS::isExist(JK_SITE_PATH() . 'modules/' . $module . '/assets')) {
                    $this->writeInfo("\t1-" . $i . "- Copy " . $module . " assets to public folder ..." . "\n");
                    \Joonika\FS::copyDirectories(JK_SITE_PATH() . 'modules/' . $module . '/assets', 'public/modules/' . $module . '/assets');
                    $this->writeSuccess("\t\tresult : success" . "\n");
                    $i++;
                }
            }
        }

        $this->writeInfo("2- Copy themes assets to public folder ..." . "\n");

        $i = 1;
        if (!empty($themes)) {
            foreach ($themes as $theme) {
                if (FS::isExist(JK_SITE_PATH() . 'themes/' . $theme . '/assets')) {
                    $this->writeInfo("\t2-" . $i . "- Copy " . $theme . " assets to public folder ..." . "\n");
                    \Joonika\FS::copyDirectories(JK_SITE_PATH() . 'themes/' . $theme . '/assets', 'public/themes/' . $theme . '/assets');
                    $this->writeSuccess("\t\tresult : success" . "\n");
                    $i++;
                }
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
                    if (FS::isExist($moduleInVendor . '/src/assets')) {
                        \Joonika\FS::copyDirectories($moduleInVendor . '/src/assets', 'public/modules/' . $moduleCheckName . '/assets');
                    }
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