<?php
/**
 * Created by PhpStorm.
 * User: m.jooibar
 * Date: 9/14/2019
 * Time: 7:45 AM
 */

namespace Joonika;


use Joonika\UAC\UAC;

class AutomaticRouter
{
    use \Joonika\Traits\Route;

    public $isCpRoute = false;
    private $url;
    private $inTheme = false;
    private $ext = null;
    private $tmp1;
    private $file;

    /**
     * Test constructor.
     * @param $path_t
     * @param bool $found_page
     * @param $themeRoute
     * @param $fileDIR
     */
    public function __construct(Route $Route)
    {
        if ($_SERVER['SCRIPT_NAME'] != 'dev') {
            if ($Route->isCpRoute) {
                $this->isCpRoute = true;
            } elseif (sizeof(explode('/', $_SERVER['REQUEST_URI'])) > 2) {
                $this->url = explode('/', $_SERVER['REQUEST_URI']);
                array_shift($this->url);
                array_shift($this->url);
                $this->url[] = "index";
                $this->url = implode('/', $this->url);
            }
        }
        $this->Route = $Route;
        $this->exexDispath();
    }

    public function exexDispath()
    {
        if (checkArraySize($this->Route->path)) {
            $this->searchInModules();
            if (!$this->Route->found) {
                if (!is_null($this->Route->database)) {
                    $this->searchInData();
                }
                if (JK_THEME()) {
                    if (!$this->Route->found) {
                        $this->searchInTheme();
                    }
                }
            }
        } else {
            if (JK_THEME()) {
                if (is_readable(JK_SITE_PATH() . 'themes' . DS() . get_active_theme() . DS() . 'Views' . DS() . 'index.php')) {
                    $this->Route->found = JK_SITE_PATH() . 'themes' . DS() . get_active_theme() . DS() . 'Views' . DS() . 'index.php';
                    $this->Route->themeRoute = true;
                } elseif (is_readable(JK_SITE_PATH() . 'themes' . DS() . get_active_theme() . DS() . 'Views' . DS() . 'index.twig')) {
                    $this->Route->found = JK_SITE_PATH() . 'themes' . DS() . get_active_theme() . DS() . 'Views' . DS() . 'index.twig';
                    $this->Route->themeRoute = true;
                }
            }
        }
    }

    private function findRoute($dir, $type, $path)
    {
        $pathsize = sizeof($path);
        if ($pathsize >= 1) {
            for ($i = $pathsize - 1; $i >= 0; $i--) {
                $this->tmp1 = implode(DS(), $path);
                if (stripos($this->tmp1, '?') != false) {
                    $this->tmp1 = explode('?', $this->tmp1)[0];
                }
                if (in_array($type[1], $this->Route->modulesInVendor)) {
                    $checkDir = JK_SITE_PATH() . 'vendor' . DS() . 'joonika' . DS() . 'module-' . $type[1] . DS() . 'src' . DS() . 'Views' . DS() . $this->tmp1;
                } else {
                    $checkDir = $dir . $type[1] . DS() . 'Views' . DS() . $this->tmp1;
                }
                if (!in_array('_layouts', $path)) {
                    if ($type[0] == "module") {
                        if (is_dir($checkDir)) {

                            $count = explode('/', $this->tmp1);
//                        if (is_readable($checkDir . DS() . 'index.php')) {
//                            $this->Route->found = $checkDir . DS() . 'index.php';
//                            return $this->Route->found;
//                        } elseif (is_readable($checkDir . DS() . 'index.twig')) {
//                            $this->Route->found = $checkDir . DS() . 'index.twig';
//                            return $this->Route->found;
//                        } else
                            if (is_readable($checkDir . '.php')) {
                                $this->Route->found = $checkDir . '.php';
                                return $this->Route->found;
                            } elseif (is_readable($checkDir . '.twig')) {
                                $this->Route->found = $checkDir . '.twig';
                                return $this->Route->found;
                            } elseif (sizeof($count) == 1 && FS::isExistIsFile($checkDir . '.php')) {
                                $this->Route->found = $checkDir . '.php';
                                return $this->Route->found;
                            } elseif (sizeof($count) == 1 && FS::isExistIsFile($checkDir . '.twig')) {
                                $this->Route->found = $checkDir . '.twig';
                                return $this->Route->found;
                            } else {
                                $path = array_values($path);
                                unset($path[sizeof($path) - 1]);
                                $path = array_values($path);
                                if (in_array('mod-' . $this->Route->mainModule, $path) && sizeof($path) == 1) {
                                    $path = explode('/', $this->url);
                                    array_shift($path);
                                    if ((!sizeof($path) > 0 || sizeof($path) == 1) && !in_array('index', $path)) {
                                        $path[] = 'index';
                                    }
                                    return $this->findRoute($dir, $type, $path);
                                } else {
                                    return $this->findRoute($dir, $type, $path);
                                }
                            }
                        } else {
                            $count = explode('/', $this->tmp1);

                            if (is_readable($checkDir . '.php')) {
                                $this->Route->found = $checkDir . '.php';
                                return $this->Route->found;
                            } elseif (is_readable($checkDir . '.twig')) {
                                $this->Route->found = $checkDir . '.twig';
                                return $this->Route->found;
                            } else {
                                $path = array_values($path);
                                unset($path[sizeof($path) - 1]);
                                $path = array_values($path);
                                if (in_array('mod-' . $this->Route->mainModule, $path) && sizeof($path) == 1) {
                                    $path = explode('/', $this->url);
                                    array_shift($path);
                                    if ((!sizeof($path) > 0 || sizeof($path) == 1) && !in_array('index', $path)) {
                                        $path[] = 'index';
                                    }
                                    return $this->findRoute($dir, $type, $path);
                                } else {
                                    return $this->findRoute($dir, $type, $path);
                                }
                            }
                        }
                    } elseif ($type[0] == "theme") {

                        $this->tmp1 = !empty($type[1]) ? $type[1] : $this->tmp1;
                        $checkDir = $dir . get_active_theme() . DS() . 'Views' . DS() . $this->tmp1;
                        $this->Route->themeRoute = true;
                        if (is_dir($checkDir)) {
                            if (is_readable($checkDir . DS() . 'index.php')) {
                                $this->Route->found = $checkDir . DS() . 'index.php';
                                return $this->Route->found;
                            } elseif (is_readable($checkDir . DS() . 'index.twig')) {
                                $this->Route->found = $checkDir . DS() . 'index.twig';
                                return $this->Route->found;
                            } else {
                                $path = array_values($path);
                                unset($path[sizeof($path) - 1]);
                                $path = array_values($path);
                                return $this->findRoute($dir, $type, $path);
                            }
                        } elseif (FS::isExistIsFile($checkDir . '.php')) {
                            if (is_readable($checkDir . '.php')) {
                                $this->Route->found = $checkDir . '.php';
                                return $this->Route->found;
                            } else {
                                $path = array_values($path);
                                unset($path[sizeof($path) - 1]);
                                $path = array_values($path);
                                return $this->findRoute($dir, $type, $path);
                            }
                        } elseif (FS::isExistIsFile($checkDir . '.twig')) {
                            if (is_readable($checkDir . '.twig')) {
                                $this->Route->found = $checkDir . '.twig';
                                return $this->Route->found;
                            } else {
                                $path = array_values($path);
                                unset($path[sizeof($path) - 1]);
                                $path = array_values($path);
                                return $this->findRoute($dir, $type, $path);
                            }
                        } elseif (is_dir($dir . get_active_theme() . DS() . 'Views' . DS() . $this->tmp1)) {

                            if (is_readable($dir . get_active_theme() . DS() . 'Views' . DS() . $this->tmp1 . DS() . 'index.php')) {
                                $this->Route->found = $dir . get_active_theme() . DS() . 'Views' . DS() . $this->tmp1 . DS() . 'index.php';
                                return $this->Route->found;
                            } elseif (is_readable($dir . get_active_theme() . DS() . 'Views' . DS() . $this->tmp1 . DS() . 'index.twig')) {
                                $this->Route->found = $dir . get_active_theme() . DS() . 'Views' . DS() . $this->tmp1 . DS() . 'index.twig';
                                return $this->Route->found;
                            } else {
                                $path = array_values($path);
                                unset($path[sizeof($path) - 1]);
                                $path = array_values($path);
                                return $this->findRoute($dir, $type, $path);
                            }
                        } else {

                            if (!is_null($this->url)) {
                                $this->tmp1 = $this->url;
                                $this->url = null;
                            }
                            $count = explode('/', $this->tmp1);

                            if (is_readable($dir . get_active_theme() . DS() . 'Views' . DS() . $this->tmp1 . '.php')) {
                                $this->Route->found = $dir . get_active_theme() . DS() . 'Views' . DS() . $this->tmp1 . '.php';
                                return $this->Route->found;
                            } elseif (is_readable($dir . get_active_theme() . DS() . 'Views' . DS() . $this->tmp1 . '.twig')) {
                                $this->Route->found = $dir . get_active_theme() . DS() . 'Views' . DS() . $this->tmp1 . '.twig';
                                return $this->Route->found;
                            } elseif (sizeof($count) == 1 && is_readable($dir . get_active_theme() . DS() . 'Views' . DS() . 'index.php')) {
                                $this->Route->found = $dir . get_active_theme() . DS() . 'Views' . DS() . 'index.php';
                                //test
                                $this->Route->found = false;
                                return $this->Route->found;
                            } elseif (sizeof($count) == 1 && is_readable($dir . get_active_theme() . DS() . 'Views' . DS() . 'index.twig')) {
                                $this->Route->found = $dir . get_active_theme() . DS() . 'Views' . DS() . 'index.twig';
                                //test
                                $this->Route->found = false;
                                return $this->Route->found;
                            } else {
                                $path = array_values(explode('/', $this->tmp1));
                                unset($path[sizeof($path) - 1]);
                                $path = array_values($path);
                                return $this->findRoute($dir, $type, $path);
                            }
                        }
                    }
                }
            }
        } else {
            return false;
        }
    }

    private function searchInModules()
    {
        $temp = $this->Route->path;
        $mod_temp = $temp[0];
        unset($temp[0]);
        $temp = array_values($temp);
        if ((!sizeof($temp) > 0 || sizeof($temp) == 1) && !in_array('index', $temp)) {
            $temp[] = $mod_temp;
            $temp[] = 'index';
        }
        if ($this->isCpRoute) {
            array_unshift($temp, 'mod-' . $this->Route->mainModule);
            $this->url = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $this->Route->mainModule) + (strlen($this->Route->mainModule) + 1));
        }
        $type = ['module', $mod_temp];
        $this->Route->found = $this->findRoute(JK_SITE_PATH() . 'modules' . DS(), $type, $temp);
    }

    private function searchInTheme()
    {
        $temp = $this->Route->path;
        $mod_temp = $temp[0];
        $this->file = $mod_temp;
        unset($temp[0]);
        $temp = array_values($temp);
        if (!sizeof($temp) > 0 || sizeof($temp) == 1) {
            $temp[] = 'index';
        }
        $type = ['theme', $mod_temp];
        $this->Route->found = $this->findRoute(JK_SITE_PATH() . 'themes' . DS(), $type, $temp);
    }

    private function searchInData()
    {
        if (!$this->Route->found && isset($this->Route->path[0])) {
            $temp = $this->Route->path;
            $data = Database::connect()->get('jk_data', '*', [
                "slug" => rawurldecode($this->Route->path[0])
            ]);
            if ($data && isset($data['module']) && in_array($data['module'], JK_MODULES())) {
                $type = ['module', $data['module']];
                $this->Route->found = $this->findRoute(JK_SITE_PATH() . 'modules' . DS(), $type, $temp);
            } elseif ($data && isset($data['id'])) {
                $mod_temp = $temp[0];
                $type = ['theme', $data['module'], $data];
                $this->Route->jk_data = $data;
                $this->Route->found = $this->findRoute(JK_SITE_PATH() . 'themes' . DS(), $type, $temp);

                if ($this->Route->found) {
                    $this->Route->path = array_merge([$data['module'], 'view', $data['id']], $this->Route->path);
                }
            }
        }
    }
}