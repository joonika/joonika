<?php

namespace Joonika\boom;

use Doctrine\Tests\Common\Annotations\Fixtures\ClassDDC1660;
use Joonika\Database;
use Joonika\Route;

abstract class boom
{

    protected $input;
    protected $id;
    protected $isApi;
    protected $return = false;

    abstract public static function define();

    public function __construct($input, $id, $return = null)
    {
        $this->input = $input;
        $this->id = $id;
        $this->return = $return;
        $this->isApi = Route::$instance->isApi;
    }

    public function successful()
    {

        Database::update("jk_listeners_queue", [
            "result" => '1',
            "errors" => null,
            "lastErrorDate" => null,
        ], ['id' => $this->id]);
        $this->runIfSuccessful();
    }

    public function runIfSuccessful()
    {

    }

    public function failed($message = '')
    {
        Database::update("jk_listeners_queue", [
            "result" => '0',
        ], ['id' => $this->id]);
        $this->runIfFailed($message);
    }

    public function runIfFailed($message = '')
    {
        $message = !empty($message) ? $message : __("failed");
        if ($this->isApi) {
            header('HTTP/1.1 ' . 400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'errors' => [[
                    'message' => $message,
                ]],
            ], 256 | 128);
        } else {
            echo alertDanger($message);
        }
        if ($this->return) {
            return [
                'code' => 400,
                'message' => $message
            ];
        } else {
            exit();
        }
    }

    public function reBoom($after = null)
    {
        $boom = Database::get('jk_listeners_queue', '*', ['id' => $this->id]);
        if ($boom) {
            Database::insert('jk_listeners_queue', [
                "event" => $boom['event'],
                "listener" => $boom['listener'],
                "module" => $boom['module'],
                "result" => 0,
                "inputs" => json_encode($this->input, JSON_UNESCAPED_UNICODE),
                "registerTime" => now(),
                "executeAt" => $after ? date('Y/m/d H:i:s', time() + $after) : null,
            ]);
            $id = Database::id();
            if (!$after && $id) {
                $this->id = $id;
                $method = $boom['listener'];
                $this->$method();
            }
        }
    }

}