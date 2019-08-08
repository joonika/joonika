<?php

namespace Joonika;

use Includes\Views;

if (!defined('jk')) die('Access Not Allowed !');

class View
{
    public $file = '';
    public $head;
    public $head_file = "";
    public $foot_file = "";
    public $title = "";
    public $data = [];
    public $metaArray = [];
    public $header_styles;
    public $header_styles_files = [];
    public $header;
    public $footer;
    public $footer_js;
    public $footer_js_files = [];
    public $brandIconUrl = "";
    public $login_page = "";
    public $requestIP = "";

    public function __construct()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $this->requestIP = $ip;

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
            if (file_exists(JK_SITE_PATH . 'files/general/' . $file . '.png')) {
                $brandIconUrl = JK_DOMAIN . 'files/general/' . $file . '.png';
            }
        }
        return $brandIconUrl;
    }

    public function render()
    {
        global $Route;
        $themehead = JK_DIR_THEMES . $Route->theme . DS . 'inc' . DS . 'func.php';
        if (is_readable($themehead)) {
            require_once $themehead;
        }
        if (is_readable($this->file) && Route::$isTheme === false) {
            require $this->file;
        } elseif (is_readable($this->file) && Route::$isTheme === true) {
            $routeDir = JK_DIR_THEMES . $Route->theme;
            $routeTheme = ltrim(str_replace($routeDir, '', $this->file), '/');
            $substr = substr($routeTheme, -10);
            if ($substr == '.blade.php') {
                $templateFile = str_replace('.blade.php', '', $routeTheme);
                echo Views::bladeRender($templateFile, []);
            } else {
                require $this->file;
            }
        } else {
            Errors::errorHandler(0, "file \"{$this->file}\" not found", 0, 1);
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
        $scanned_directory = array_diff(scandir(JK_DIR_MODULES), array('..', '.'));
        if (sizeof($scanned_directory) >= 1) {
            foreach ($scanned_directory as $elem) {
                if (is_dir(JK_DIR_MODULES . $elem)) {
                    if (file_exists(JK_DIR_MODULES . $elem . DS . 'head.php')) {
                        require_once(JK_DIR_MODULES . $elem . DS . 'head.php');
                    }
                }
            }
        }

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