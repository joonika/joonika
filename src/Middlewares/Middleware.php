<?php


namespace Joonika\Middlewares;


use Joonika\Request;
use Joonika\Route;


abstract class Middleware
{

    use \Joonika\Traits\Request;
    use \Joonika\Traits\Response;
    use \Joonika\Traits\Tokens;
    use \Joonika\Traits\Route;
    use \Joonika\Traits\View;


    public $modules;
    protected $directory;

    public function __construct($userId, Route $Route, $directory = null)
    {
        $this->requests = $Route->requests;
        if ($userId) {
            $this->userId = $userId;
            $this->requests->userId = $userId;
            self::$JK_LOGIN_ID = $userId;
        } elseif (JK_LOGINID()) {
            $this->userId = JK_LOGINID();
            $this->requests->userId = JK_LOGINID();
        }
        $this->Route = $Route;
        $this->View = $Route->View;
        $this->directory = $directory ? $directory : "";
    }

    public function view_render($input = true)
    {
        $this->Route->ViewRender = $input == false ? false : true;
    }

    public function get_view($path = null, $viewRender = true, $args = [])
    {
        if ($path) {
            $this->View->render($this->directory . $path, $args);
            $this->view_render($viewRender);
        } else {
            return false;
        }
    }

}