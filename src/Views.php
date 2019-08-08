<?php

namespace Includes;

use Philo\Blade\Blade;

class Views
{
    public static function bladeRender($template,$args=[])
    {
        global $Route;
        $viewsFile=realpath(JK_DIR_THEMES.$Route->theme);
        $cacheViewsFile=realpath(__DIR__."/../storage/views");
        $blade=new Blade($viewsFile,$cacheViewsFile);
        return $blade->view()->make($template,$args)->render();
    }
}