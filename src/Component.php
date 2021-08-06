<?php


namespace Joonika;


class Component
{
    public static function loadFromModule($componnet, $inputs = null, $print = true)
    {
        if (FS::isExistIsFileIsReadable(JK_DIR_MODULES() . $componnet . ".php")) {
            $output = NULL;
            extract($inputs);
            ob_start();
            include JK_DIR_MODULES() . $componnet . ".php";
            $output = ob_get_clean();
            if ($print) {
                echo $output;
            } else {
                return $output;
            }
        } else {
            return __("componnet not found");
        }
    }

    public static function loadFromTheme($componnet, $inputs = null, $print = true)
    {
        if (FS::isExistIsFileIsReadable(JK_DIR_THEMES() . $componnet . ".php")) {
            $output = NULL;
            extract($inputs);
            ob_start();
            include JK_DIR_THEMES() . $componnet . ".php";
            $output = ob_get_clean();
            if ($print) {
                echo $output;
            } else {
                return $output;
            }
        } else {
            return __("componnet not found");
        }
    }
}