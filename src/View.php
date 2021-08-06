<?php

namespace Joonika;

use Includes\Views;


class View
{
    use \Joonika\Traits\Route;
    use \Joonika\Traits\Request;

    public $file = '';
    public $head_file = "";
    public $foot_file = "";
    public $error404 = "";
    public $error403 = "";
    public $title = "";
    public $data = [];
    public $metaArray = [];
    public $header_styles;
    public $header_styles_files = [];
    public $header;
    public $footer;
    public $footer_js;
    public $footer_js_files = [];
    public $pathModule = [];
    public $login_page = "";
    public $siteTitle = "";
    public $siteDescription = "";
    public $siteKeywords = "";
    public $args = [];
    public $directory = '';
    public $brandIconUrl = "";

    public function __construct(Route $Route)
    {
        $this->Route = $Route;
        $this->requests = $Route->requests;
        if (!is_null($this->Route->database)) {
            $this->siteTitle = jk_options_get('siteTitle_websiteID_' . JK_WEBSITE_ID() . '_' . JK_LANG());
            $this->siteDescription = jk_options_get('siteDescription_websiteID_' . JK_WEBSITE_ID() . '_' . JK_LANG());
            $this->siteKeywords = jk_options_get('siteKeywords_websiteID_' . JK_WEBSITE_ID() . '_' . JK_LANG());
        }
    }


    /**
     * @param string $brandIconUrl
     */
    public function setBrandIconUrl($brandIconUrl)
    {
        $this->brandIconUrl = $brandIconUrl;
    }

    /**
     * @return mixed
     */
    public function getBrandIconUrl()
    {
        $brandIconUrl = $this->brandIconUrl;
        if ($brandIconUrl == "" || $brandIconUrl == 'inverse') {
            $file = 'logo';
            if ($brandIconUrl == 'inverse') {
                $file .= '-inverse';
            }
            if (file_exists(JK_SITE_PATH() . 'files/general/' . $file . '.png')) {
                $brandIconUrl = JK_DOMAIN() . 'files/general/' . $file . '.png';
            }
        }
        return $brandIconUrl;
    }


    public function render($file = null, $args = [], $cache = false)
    {
        if ($this->Route->ViewRender) {
            if ($file) {
                if (is_readable($file)) {
                    $this->Route->found = $file;
                }
            }

            $this->args = $this->Route->args;

            if (sizeof($args) >= 1) {
                $this->args = array_merge($this->args, $args);
            }
            $this->args['View'] = $this->Route->View;
            if ($this->Route->found) {
                $pathInfo = pathinfo($this->Route->found);
                if ($pathInfo['extension'] == 'twig') {

                    unset($this->Route->View->requests);
                    unset($this->Route->View->Route->requests);
                    unset($this->Route->View->Route->User);
                    unset($this->Route->View->Route->ViewRender);
                    unset($this->Route->View->Route->render);

                    $JK_SITE_PATH = JK_SITE_PATH();
                    $loader = new \Twig\Loader\FilesystemLoader($JK_SITE_PATH);
                    $configs = ['cache' => false];
                    if ($cache) {
                        $configs['cache'] = JK_SITE_PATH() . 'storage/views';
                    }
                    $debug = JK_APP_DEBUG();
                    if ($debug) {
                        $configs['debug'] = true;
                    }

                    $twig = new \Twig\Environment($loader, $configs);
                    if ($debug) {
                        $twig->addExtension(new \Twig\Extension\DebugExtension());
                    }

                    $twig->addGlobal('app', app());

                    foreach (listModules() as $module) {
                        $moduleViews = $JK_SITE_PATH . 'modules/' . $module . '/Views';
                        if(!empty($this->pathModule[$module])){
                            $loader->addPath($this->pathModule[$module], $module);
                        }
                        if(is_dir($moduleViews)){
                            $loader->addPath($moduleViews, $module);
                        }
                        $class = "Modules\\" . $module . "\src\Twig";
                        if (method_exists($class, 'registerFunctions')) {
                            $functions = $class::registerFunctions($this->Route);
                            if (checkArraySize($functions)) {
                                foreach ($functions as $function) {
                                    $twig->addFunction($function);
                                }
                            } elseif (is_object($functions)) {
                                $twig->addFunction($functions);
                            }
                        }

                        if (method_exists($class, 'addGlobal')) {
                            $globals = $class::addGlobal($this->Route);
                            if (checkArraySize($globals)) {
                                foreach ($globals as $globalK => $globalV) {
                                    $twig->addGlobal($globalK, $globalV);
                                }
                            }
                        }

                        $moduleUc = ucfirst($module);
                        $classSt = "\\Joonika\\Modules\\" . $moduleUc . '\\' . $moduleUc;
                        if (class_exists($classSt)) {
                            $unNeed = '\\Joonika\\Modules\\' . $moduleUc . '\\';
                            $shortClass = str_replace($unNeed, '', $classSt);
                            $twigFunction = new \Twig\TwigFunction($shortClass, function ($method, ...$args) use ($classSt) {
                                return $classSt::$method(...$args);
                            });
                            $twig->addFunction($twigFunction);
                        }

                    }


                    $file = str_replace($JK_SITE_PATH, '', $this->Route->found);
                    try {
                        $template = $twig->load($file);
                        echo $template->render($this->args);
                    }catch (\Exception $exception){
//                        $findCpRewrite='Unable to find template "@cp/';
//                        if(substr($exception->getMessage(),0,strlen($findCpRewrite))==$findCpRewrite){
//                            $moduleViews = $JK_SITE_PATH . 'modules/cp/Views';
//                            $loader->addPath($moduleViews,'cp');
//                            $template = $twig->load($file);
//                            echo $template->render($this->args);
                            jdie($exception->getMessage());
//                        }
                    }
                } else {
                    $this->args['Requests'] = $this->Route->requests;
                    extract($this->args);
                    require_once $this->Route->found;
                }
            } else {
                Errors::errorHandler(0, __("file not found"), 0, 1,404);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * @param mixed $head
     */
    public function setHead($head)
    {
        $this->head = $head;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return array
     */
    public function getMetaArray()
    {
        return $this->metaArray;
    }

    /**
     * @param array $metaArray
     */
    public function setMetaArray($metaArray)
    {
        $this->metaArray = $metaArray;
    }

    /**
     * @return mixed
     */
    public function getHeadStyles()
    {
        return $this->header_styles;
    }

    /**
     * @param mixed $head_styles
     */
    public function setHeadStyles($head_styles)
    {
        $this->header_styles = $head_styles;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param mixed $header
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * @return mixed
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @param mixed $footer
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    /**
     * @return mixed
     */
    public function getHeadFile()
    {
        return $this->head_file;
    }

    /**
     * @param mixed $head_file
     */
    public function setHeadFile($head_file)
    {
        $this->head_file = $head_file;
    }

    /**
     * @return mixed
     */
    public function getFooterJs()
    {
        return $this->footer_js;
    }

    /**
     * @param mixed $footer_js
     */
    public function setFooterJs($footer_js)
    {
        $this->footer_js = $footer_js;
    }

    /**
     *
     */
    public function head()
    {
        /*        if (sizeof($Route->modules) >= 1) {
                    foreach ($Route->modules as $elem) {
                        if (is_dir(JK_SITE_PATH() . 'modules' . DS() . $elem)) {
                            if (file_exists(JK_SITE_PATH() . 'modules' . DS() . $elem . DS() . 'head.php')) {
                                require_once(JK_SITE_PATH() . 'modules' . DS() . $elem . DS() . 'head.php');
                            }
                        }
                    }
                }*/

        if ($this->head_file != "") {
            include_once $this->head_file;
        }
    }

    public function foot()
    {
        if ($this->foot_file != "") {
            require_once $this->foot_file;
        }
    }

    public function footer_js($javascript = "")
    {
        if ($javascript != "") {
            $this->footer_js .= $javascript;
        }
    }

    public function header_styles($style = "")
    {
        if ($style != "") {
            $this->header_styles .= $style;
        }
    }

    public function footer_js_empty($javascript = "")
    {
        $this->footer_js = "";
    }

    public function footer_js_files($fileUrl = "")
    {
        if (!in_array($fileUrl, $this->footer_js_files)) {
            array_push($this->footer_js_files, $fileUrl);
        }
    }

    public function header_styles_files($fileUrl = "")
    {
        if (!in_array($fileUrl, $this->header_styles_files)) {
            array_push($this->header_styles_files, $fileUrl);
        }
    }


    public function getFooterJsFiles()
    {
        $back = "";
        if (sizeof($this->footer_js_files) >= 1) {
            foreach ($this->footer_js_files as $footer_js_file) {
                $back .= '<script  type="text/javascript" src="' . $footer_js_file . '"></script>';
            }
        }
        return $back;
    }

    public function getHeaderStylesFiles()
    {
        $back = "";
        if (sizeof($this->header_styles_files) >= 1) {
            foreach ($this->header_styles_files as $header_styles_file) {
                $back .= '<link rel="stylesheet" href="' . $header_styles_file . '">';
            }
        }
        return $back;
    }

    public function getHeaderStyles()
    {
        $back = "";
        $back .= $this->header_styles;
        return $back;
    }

}