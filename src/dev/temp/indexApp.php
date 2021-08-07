<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * @OA\Info(
 *     title="barpin",
 *     description = "API for barpin",
 *     version="0.1")
 */
require __DIR__ . '/vendor/autoload.php';

use Joonika\Route;

$Route = Route::ROUTE(__DIR__ . '/');

$Route->View->render();
