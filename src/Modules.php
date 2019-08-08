<?php

namespace Joonika;
if (!defined('jk')) die('Access Not Allowed !');

class Modules
{
    public $modulesLoad = [];
    public function __construct()
    {
        $foundedModules = [];
        $scanned_directory = array_diff(scandir(JK_DIR_MODULES), array('..', '.'));
        if (sizeof($scanned_directory) >= 1) {
            foreach ($scanned_directory as $elem) {
                if (is_dir(JK_DIR_MODULES . $elem)) {
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
            $loader->loadPSRDir(JK_DIR_MODULES, $autoload, true);
        }
    }
}

function listModules()
{
    $scanned_directory = array_diff(scandir(JK_DIR_MODULES), array('..', '.'));
    $back = [];
    if (sizeof($scanned_directory) >= 1) {
        foreach ($scanned_directory as $elem) {
            if (is_dir(JK_DIR_MODULES . $elem)) {
                array_push($back, $elem);
            }
        }
    }
    return $back;
}

function listModulesReadFiles($file)
{
    $modules = listModules();
    if (sizeof($modules) >= 1) {
        foreach ($modules as $mod) {
//            echo JK_SITE_PATH . 'modules' . DS . $mod . DS . $file.'<br/>';
            if (file_exists(JK_SITE_PATH . 'modules' . DS . $mod . DS . $file)) {
                include_once JK_SITE_PATH . 'modules' . DS . $mod . DS . $file;
            }
        }
    }
}