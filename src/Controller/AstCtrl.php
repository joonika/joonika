<?php


namespace Joonika\Controller;


class AstCtrl
{
    public static $footer_js_files = [
        /*   "jquery-3.4.1.min.js" => [
               "assets/js/jquery-3.4.1.min.js",
               false,
               "default" => true
           ],*/
    ];
    private static $header_styles_files = [];
    private static $final_js_files = [];
    private static $final_style_files = [];
    private static $header_style;
    private static $footer_script;
    protected static $cdn = "";
    public static $meta = [
        'name="viewport"' => 'width=device-width, initial-scale=1',
        'http-equiv="Content-Type"' => 'text/html;charset=UTF-8',
    ];
    public static $title;
    public static $scriptOnHead = [];
    public static $jk_server_type = JK_SERVER_TYPE;
    public static $VIEW;

    public static function ADD_FOOTER_JS_FILES($files = null, $cdn = null)
    {

        if ($files) {
            if (is_array($files)) {
                foreach ($files as $name => $file) {
                    $name = pathinfo($file)['basename'];
                    if (!in_array($file[0], self::$footer_js_files)) {
                        self::$footer_js_files[$name][] = $file[0];
                        if ($cdn) {
                            self::$footer_js_files[$name][] = $file[1];
                        }
                    }
                }
            } else {
                $name = pathinfo($files)['basename'];
                if (!in_array($files, self::$footer_js_files)) {
                    self::$footer_js_files[$name][] = $files;
                    if ($cdn) {
                        self::$footer_js_files[$name][] = $cdn;
                    }
                }
            }
        }
    }

    public static function FOOTER_JS_FILES($includeThemes = true,$return=false)
    {
        if ($includeThemes) {
            $controller = "Themes\\" . JK_THEME() . "\Controllers\Controller";
            if (class_exists($controller)) {
                if (sizeof($controller::$footer_js_files) > 0) {
                    $arr = [];
                    foreach (self::$footer_js_files as $name => $file) {
                        $arr[] = $file[0];
                    }
                    foreach ($controller::$footer_js_files as $name => $file) {
                        if (!in_array($file[0], $arr)) {
                            self::$final_js_files[$name][] = $file[0];
                            self::$final_js_files[$name][] = $file[1];
                        }
                    }
                }
            }
        }
        foreach (self::$footer_js_files as $name => $file) {
            $url = '';
            if (isset($file['default'])) {
                $url .= $file['default'] ? JK_DOMAIN() : '';
            }
            $url .= $file[0];
            self::$final_js_files[$name][] = $url;
            if (!empty($file[1])) {
                self::$final_js_files[$name][] = $file[1];
            }
        }
        $html='';
        foreach (self::$final_js_files as $name => $file) {
            $html.=!empty($file[1]) ? '<script src="' . self::$cdn . $file[0] . '" type="text/javascript"></script>' : '<script src="' . $file[0] . '" type="text/javascript"></script>
';
        }
        if($return)
            return $html;
        else
            echo $html;

    }

    public static function ADD_FOOTER_SCRIPTS($scripts)
    {
        if (is_array($scripts)) {
            foreach ($scripts as $script) {
                self::$footer_script .= $script;
            }
        } else {
            self::$footer_script .= $scripts;
        }
    }

    public static function FOOTER_SCRIPTS($scripts = null,$return=false)
    {
        if ($scripts) {
            if (is_array($scripts)) {
                foreach ($scripts as $script) {
                    self::$footer_script .= $script;
                }
            } else {
                self::$footer_script .= $scripts;
            }
        }
        self::$footer_script=html_entity_decode(self::$footer_script,ENT_QUOTES);
        $html=self::$footer_script;
        if($return)
            return $html;
        else
            echo $html;
    }

    public static function ADD_HEADER_STYLES_FILES($files = null, $cdn = null)
    {
        if ($files) {
            if (is_array($files)) {
                foreach ($files as $name => $file) {
                    $name = pathinfo($file)['basename'];
                    if (!in_array($file[0], self::$header_styles_files)) {
                        self::$header_styles_files[$name][] = $file[0];
                        if ($cdn) {
                            self::$header_styles_files[$name][] = $file[1];
                        }
                    }
                }
            } else {
                $name = pathinfo($files)['basename'];
                if (!in_array($files, self::$header_styles_files)) {
                    self::$header_styles_files[$name][] = $files;
                    if ($cdn) {
                        self::$header_styles_files[$name][] = $cdn;
                    }
                }
            }
        }
    }

    public static function HEADER_STYLES_FILES($includeThemes = true,$return=false)
    {
        if ($includeThemes) {
            $controller = "Themes\\" . JK_THEME() . "\Controllers\Controller";
            if (sizeof($controller::$header_styles_files) > 0) {
                $arr = [];
                foreach (self::$header_styles_files as $name => $file) {
                    $arr[] = $file[0];
                }
                foreach ($controller::$header_styles_files as $name => $file) {
                    if (!in_array($file[0], $arr)) {
                        self::$final_style_files[$name][] = $file[0];
                        self::$final_style_files[$name][] = $file[1];
                    }
                }
            }
        }

        foreach (self::$header_styles_files as $name => $file) {
            $url = '';
            if (isset($file['default'])) {
                $url .= $file['default'] ? JK_DOMAIN() : '';
            }
            $url .= $file[0];
            self::$final_style_files[$name][] = $url;
            if (!empty($file[1])) {
                self::$final_style_files[$name][] = $file[1];
            }
        }
        $html='';
        foreach (self::$final_style_files as $name => $file) {
            $html.=!empty($file[1]) ? '<link rel="stylesheet" href="' . self::$cdn . $file[0] . '">' : '<link rel="stylesheet" href="' . $file[0] . '">';
        }
        if($return)
            return $html;
        else
            echo $html;
    }

    public static function ADD_HEADER_STYLES($styles)
    {
        if (is_array($styles)) {
            foreach ($styles as $style) {
                $style=str_replace('<style>','',$style);
                $style=str_replace('</style>','',$style);
                self::$header_style .= $style;
            }
        } else {
            $styles=str_replace('<style>','',$styles);
            $styles=str_replace('</style>','',$styles);
            self::$header_style .= $styles;
        }
    }

    public static function HEADER_STYLES($styles = null,$return=false)
    {
        if ($styles) {
            self::ADD_HEADER_STYLES($styles);
        }
        $html=!empty(self::$header_style)?('<style>' . self::$header_style . '</style>'):'';
        if($return)
            return $html;
        else
            echo $html;
    }

    public static function META_TAGS($meta = null,$return=false)
    {
        if ($meta) {
            foreach ($meta as $item => $value) {
                if (!array_key_exists($item, self::$meta))
                    self::$meta[$item] = $value;
            }
        }
        $html='';
        foreach (self::$meta as $item => $value) {
            $html.= '<meta ' . $item . ' content="' . $value . '">';
        }
        if($return)
            return $html;
        else
            echo $html;
    }

    public static function ADD_META_TAGS($meta)
    {
        if ($meta) {
            foreach ($meta as $item => $value) {
                if (!array_key_exists($item, self::$meta))
                    self::$meta[$item] = $value;
            }
        }
    }

    public static function TITLE($title = null)
    {
        if ($title) {
            self::$title = $title;
        }
        return self::$title;
    }

    public static function LANG($lang = null)
    {
        $defLang = JK_LANG();
        if ($lang) {
            $defLang = $lang;
        }
        return $defLang;
    }

    public static function DIR($dir = null)
    {
        $direction = JK_DIRECTION();
        if ($dir) {
            $direction = $dir;
        }
        return $direction;
    }

    public static function ADD_SCRIPT_ON_HEAD($scripts)
    {
        foreach ($scripts as $item => $value) {
            self::$scriptOnHead[$item] = $value;
        }
    }

    public static function SCRIPT_ON_HEAD($scripts = null,$return=false)
    {
        if ($scripts) {
            foreach ($scripts as $item => $value) {
                self::$scriptOnHead[$item] = $value;
            }
        }
        $html='';
        foreach (self::$scriptOnHead as $item => $value) {
            $html.= '<script type="' . $item . '">' . $value . '</script>';
        }
        if($return)
            return $html;
        else
            echo $html;
    }

    public static function SET_CDN($cdn)
    {
        self::$cdn = $cdn;
    }

    public static function THEME_CDN()
    {
        if (self::$jk_server_type == 'test') {
            self::$cdn = "";
        }
        return self::$cdn;
    }

    public static function SITE_KEY_WORDS($keyword = null, $echo = null)
    {
        self::$VIEW = ThemeController::$VIEW;
        if ($keyword) {
            self::$VIEW->siteKeywords = join(',', $keyword);
        }
        if ($echo) {
            echo self::$VIEW->siteKeywords;
        } else {
            return self::$VIEW->siteKeywords;
        }
    }

    public static function SITE_DESCRIPTION($description = null, $echo = null)
    {
        self::$VIEW = ThemeController::$VIEW;
        if ($description) {
            self::$VIEW->siteDescription = $description;
        }
        if ($echo) {
            echo self::$VIEW->siteDescription;
        } else {
            return self::$VIEW->siteDescription;
        }
    }

}