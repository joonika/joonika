<?php

namespace Joonika;


class Joonika
{
    public $domain;
    public $path;
    public $model;
    public $method;
    public $params;
    public $get;
    public $post;

    /**
     * Joonika constructor.
     * @param $domain
     */
    public function __construct()
    {
        $config_path = "../config.ini";
        if(file_exists($config_path)){
            $config=file_get_contents($config_path);
            print_r($config);
        }
    }


}