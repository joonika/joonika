<?php


require __DIR__ . '/vendor/autoload.php';

use Joonika\Route;

$Route = Route::ROUTE(__DIR__ . '/');

$Route->View->render();

