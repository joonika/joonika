<?php

namespace Joonika;

class Modules
{
    public $modulesLoad = [];

    public function __construct()
    {

        $foundedModules = [];
        $scanned_directory = array_diff(scandir(JK_DIR_MODULES()), array('..', '.'));
        if (sizeof($scanned_directory) >= 1) {
            foreach ($scanned_directory as $elem) {
                if (is_dir(JK_DIR_MODULES() . $elem)) {
                    array_push($foundedModules, $elem);
                }
            }
        }
        $autoload['autoload']['psr-4'] = [];
        if (sizeof($foundedModules) >= 1) {
            foreach ($foundedModules as $foundedModule) {
                $autoload['Joonika\\Modules\\' . ucfirst($foundedModule) . '\\'][] = $foundedModule . '/src/';
            }
            global $loader;
            $loader->loadPSRDir(JK_DIR_MODULES(), $autoload, true);
        }
    }

    public static function listModulesReadFiles($files, $once = true, $return = false)
    {
        $modules = listModules();
        $result = [];
        if (is_array($files)) {
            foreach ($files as $file) {
                if (sizeof($modules) >= 1) {
                    foreach ($modules as $mod) {
                        if (FS::isExistIsFileIsReadable(JK_SITE_PATH() . 'modules' . DS() . $mod . DS() . "Views" . DS() . $file)) {
                            $file = JK_SITE_PATH() . 'modules' . DS() . $mod . DS() . "Views" . DS() . $file;
                            if ($once) {
                                if ($return) {
                                    $result[] = include_once $file;
                                } else {
                                    include_once $file;
                                }
                            } else {
                                if ($return) {
                                    $result[] = include $file;
                                } else {
                                    include $file;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if (sizeof($modules) >= 1) {
                foreach ($modules as $mod) {
                    if (FS::isExistIsFileIsReadable(JK_SITE_PATH() . 'modules' . DS() . $mod . DS() . "Views" . DS() . $files)) {
                        $file = JK_SITE_PATH() . 'modules' . DS() . $mod . DS() . "Views" . DS() . $files;
                        if ($once) {
                            if ($return) {
                                $result[] = include_once $file;
                            } else {
                                include_once $file;
                            }
                        } else {
                            if ($return) {
                                $result[] = include $file;
                            } else {
                                include $file;
                            }
                        }
                    }
                }
            }
        }
        if ($return) {
            return $result;
        }
    }

    public static function listModulesHasFile($file)
    {
        $modules = listModules();
        $modulesHasFile = [];
        if (sizeof($modules) >= 1) {
            foreach ($modules as $mod) {
                if (FS::isExistIsFileIsReadable(JK_SITE_PATH() . 'modules' . DS() . $mod . DS() . $file)) {
                    array_push($modulesHasFile, $mod);
                }
            }
        }
        return $modulesHasFile;
    }

    public static function listModules()
    {
        $scanned_directory = array_diff(scandir(JK_DIR_MODULES()), array('..', '.'));
        $back = [];
        if (sizeof($scanned_directory) >= 1) {
            foreach ($scanned_directory as $elem) {
                if (is_dir(JK_DIR_MODULES() . $elem)) {
                    array_push($back, $elem);
                }
            }
        }
        return $back;
    }

    public static function cloneModule($name, $newName)
    {

        FS::mkDir(__DIR__ . "/../../../../modules/" . $newName, 0777, true);
        $rootFiles = glob(__DIR__ . "/../../../../modules/" . $name . DS() . '*.*');
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
                $dest = str_replace("/" . $name . "/", '/' . $newName . '/', $rootFile);
                $path = pathinfo($dest);
                FS::mkDir(JK_SITE_PATH() . "modules" . DS() . $newName, 0777, true);
                FS::fileWrite($dest, $rContent);
            }
        }

        foreach (['assets', 'components', 'langs'] as $item) {
            if (FS::isDir(__DIR__ . "/../../../../modules/" . $name . DS() . $item)) {
                FS::copyDirectories(JK_SITE_PATH() . "modules/" . $name . DS() . $item, JK_SITE_PATH() . 'modules/' . $newName . DS() . $item);
            }
        }

        foreach (['console', "Views", "Tests", "src", "Providers", 'Middlewares', "Models", "Listeners", "inc", "Events", "database", "Controllers", "console"] as $item) {
            $files = FS::allFilesList(__DIR__ . "/../../../../modules/" . $name . DS() . $item);
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
                    $dest = str_replace("/" . $name . "/", '/' . $newName . '/', $file);
                    $path = pathinfo($dest);
                    FS::mkDir($path['dirname'], 0777, true);
                    FS::fileWrite($dest, $content);
                }
            }
        }

        if (FS::isExistIsFile(JK_SITE_PATH() . "modules" . DS() . $newName . DS() . 'Controllers' . DS() . $name . 'Controller.php')) {
            $content = FS::fileGetContent(JK_SITE_PATH() . "modules" . DS() . $newName . DS() . 'Controllers' . DS() . $name . 'Controller.php');
            $content = str_replace('class ' . $name . 'Controller extends Controller', 'class ' . $newName . 'Controller extends Controller', $content);
            FS::fileWrite(JK_SITE_PATH() . "modules" . DS() . $newName . DS() . 'Controllers' . DS() . $newName . 'Controller.php', $content);
            FS::fileRemove(JK_SITE_PATH() . "modules" . DS() . $newName . DS() . 'Controllers' . DS() . $name . 'Controller.php');
        }
    }
}


