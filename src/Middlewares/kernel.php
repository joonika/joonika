<?php


namespace Joonika\Middlewares;


use Config\SortMiddleWare;
use Joonika\configs\MiddleWareSort;
use Joonika\FS;
use Joonika\Route;
use Modules\acc\inc\Configs;


class kernel extends Middleware
{

    private $sortList = [];
    public $kernelList = [
        'Auth' => 'Joonika\Middlewares\\AUTH',
    ];

    private function runGlobalMiddleWares($modules)
    {
        foreach ($modules as $module) {
            $files = [];
            $globalMiddlewares = glob(JK_SITE_PATH() . "modules/" . $module . "/Middlewares/*_global.php");
            if (empty($globalMiddlewares)) {
                $globalMiddlewares = glob(JK_SITE_PATH() . "vendor/joonika/module-" . $module . "/src/Middlewares/*_global.php");
            }
            if (!empty($globalMiddlewares)) {
                foreach ($globalMiddlewares as $file) {
                    $bn = basename($file, '.php');
                    $this->kernelList[$module . "***" . $bn]['namespace'] = "Modules\\" . lcfirst($module) . "\Middlewares\\" . $bn;
                    $this->kernelList[$module . "***" . $bn]['directory'] = "./modules/" . lcfirst($module) . "/Middlewares/";
                }
            }
        }
    }

    public function __construct($userID, Route $Route)
    {
        parent::__construct($userID, $Route);
        $middleWareSortClass = "Config\\SortMiddleWare";
        $modulesArrange = [];
        if (class_exists($middleWareSortClass)) {
            $middleWareSort = new $middleWareSortClass();
            $modulesArrange = $middleWareSort->getMiddleWaresPriorities();
            /* if (false) {
                 $this->runGlobalMiddleWares($this->sortList);
             } else {
                 $this->runGlobalMiddleWares($Route->modules);
             }*/
        }
        $array_dif = array_diff($Route->modules, $modulesArrange);
        if (!empty($array_dif)) {
            foreach ($array_dif as $ar) {
                array_push($modulesArrange, $ar);
            }
        }
        $this->runGlobalMiddleWares($modulesArrange);
    }


    public function dispatch()
    {
        if (isset($this->Route->path[0]) && in_array($this->Route->path[0], $this->Route->modules)) {
            $localMiddlewares = glob(JK_SITE_PATH() . "modules/" . $this->Route->path[0] . "/Middlewares/*[!_global].php");
            if (empty($localMiddlewares)) {
                $localMiddlewares = glob(JK_SITE_PATH() . "vendor/joonika/module-" . $this->Route->path[0] . "/src/Middlewares/*[!_global].php");
            }
            foreach ($localMiddlewares as $file) {
                $bn = basename($file, '.php');
                $this->kernelList[lcfirst($this->Route->path[0]) . "***" . $bn]['namespace'] = "Modules\\" . $this->Route->path[0] . "\Middlewares\\" . $bn;
                $this->kernelList[lcfirst($this->Route->path[0]) . "***" . $bn]['directory'] = "./modules/" . lcfirst($this->Route->path[0]) . "/Middlewares/";
            }
        }
        foreach ($this->kernelList as $middlewareKey => $middlewareValue) {
            if (is_array($middlewareValue)) {
                if (class_exists($middlewareValue['namespace'])) {
                    $midd = new $middlewareValue['namespace']($this->userId, $this->Route, $middlewareValue['directory']);
                }
            } else {
                if (class_exists($middlewareValue)) {
                    $midd = new $middlewareValue($this->userId, $this->Route);
                }
            }
            $midd->run();
        }

        foreach ($this->Route->modules as $module) {
            $class = "Modules\\" . $module . "\inc\Configs";
            if (class_exists($class) && method_exists($class, 'menus')) {
                $menus = $class::menus();
                if (checkArraySize($menus) && isset($menus['title'])) {
                    array_push($this->Route->sidebarMenus, $menus);
                } elseif (checkArraySize($menus)) {
                    foreach ($menus as $singleMenu) {
                        array_push($this->Route->sidebarMenus, $singleMenu);
                    }
                }

            } elseif (FS::isExistIsFileIsReadable(JK_SITE_PATH() . 'modules' . DS() . $module . DS() . 'inc' . DS() . 'menus.php')) {
                $menus = include_once JK_SITE_PATH() . 'modules' . DS() . $module . DS() . 'inc' . DS() . 'menus.php';
                if (checkArraySize($menus) && isset($menus['title'])) {
                    array_push($this->Route->sidebarMenus, $menus);
                } elseif (checkArraySize($menus)) {
                    foreach ($menus as $singleMenu) {
                        array_push($this->Route->sidebarMenus, $singleMenu);
                    }
                }
            }
        }
    }
}
