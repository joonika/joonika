<?php

namespace Joonika\Controller;


use Joonika\Controller;
use Joonika\FS;
use Joonika\Route;
use Theme\sample\Controllers\ControllerTwo;

class ThemeController extends Controller
{
    protected static $instance = null;
    public static $header_styles_files = [];
    public static $footer_js_files = [];
    public static $foot_file = "";
    public static $head_file = "";
    public static $VIEW;
    public static $title = null;
    public static $description = null;
    public static $keywords = null;

    public function __construct(Route $Route, $path = null)
    {
        self::$VIEW = $Route->View;
        $this->specificHeader($path);
        $this->specificFooter($path);
        parent::__construct($Route);
    }

    protected function specificFooter($path)
    {
        if (FS::isExistIsFileIsReadable($path . "footer.php")) {
            self::$foot_file = $path . "footer.php";
        }
        return $this;
    }

    protected function specificHeader($path)
    {
        if (FS::isExistIsFileIsReadable($path . "header.php")) {
            self::$head_file = $path . "header.php";
        }
        return $this;
    }

    public static function getTitle()
    {
        return self::$title;
    }

    public static function setTitle($title)
    {
        self::$title = $title;
    }

    public static function getDescription()
    {
        return self::$description;
    }

    public static function setDescription($description)
    {
        self::$description = $description;
    }

    public static function getKeywords()
    {
        return self::$keywords;
    }

    public static function setKeywords($keywords)
    {
        self::$keywords = $keywords;
    }
}