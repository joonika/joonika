<?php

namespace Joonika\Seeder;

use Faker\Factory;
use Joonika\Database;

class Faker
{
    protected static $instance = null;

    private function __construct()
    {
        self::$instance = Factory::create();
    }

    public static function build($info = null)
    {
        if (!self::$instance) {
            new Faker();
        }
        return self::$instance;
    }
}