<?php


namespace Joonika\templates\redirects;


class RedirectTemplate
{
    public $activeTheme = '';

    public function __construct()
    {
        $this->activeTheme = get_active_theme();
    }
}