<?php

namespace Joonika;


class Controller
{
    use \Joonika\Traits\Tokens;
    use \Joonika\Traits\Response;
    use \Joonika\Traits\Request;
    use \Joonika\Traits\Route;
    use \Joonika\Traits\View;

    public $module;
    protected $model;
    protected $id;
    protected $method;
    protected $args;
    protected $file;
    public $directory;
    static protected $DB;
    protected $dest;
    protected $continue = true;
    protected $methodInject = null;
    protected $timeStart;
    protected $timeStop;
    protected $executionTime;
    public $viewRender = null;
    public $inputs = [];
    public $database = false;
    public $foundMethod = false;

    protected function getError($msg)
    {
        return Errors::errorHandler(0, $msg, __FILE__, __LINE__);
    }

    private function checkIsCommandInRequests($cmd = 'cmd')
    {
        $input = $this->requests->isMethod('get') ? $this->requests->queryStrings() : $this->requests->all();
        if (is_array($input)) {
            if (array_key_exists($cmd, $input)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function runCommandMethodInRequests($cmd = 'cmd')
    {
        $cmd = $this->requests->isMethod('get') ? $this->requests->queryStrings($cmd) : $this->requests->Input($cmd);
        $cmd = "cmd_" . $cmd;
        if (method_exists($this, $cmd)) {
            $this->sendInputsToMethodAndRunIt($cmd);
        }
    }

    private function sendInputsToMethodAndRunIt($method = null)
    {
        if ($method) {
            $this->method = $method;
        }
        $i = 0;
        $r = new \ReflectionMethod($this, $this->method);
        $params = $r->getParameters();
        $inputs = [];
        $allow = true;
        $msgArray = [];
        if (in_array($this->dest, $this->Route->path)) {
            $m = array_slice($this->Route->path, array_search($this->dest, $this->Route->path) + 1);
            for ($i; $i < sizeof($params); $i++) {
                if ($params[$i]->isOptional()) {
                    if (isset($m[$i])) {
                        $inputs[$params[$i]->getName()] = $m[$i];
                    } elseif ($params[$i]->isDefaultValueAvailable()) {
                        $inputs[$params[$i]->getName()] = $params[$i]->getDefaultValue();
                    } else {
                        $inputs[$params[$i]->getName()] = null;
                    }
                } elseif (!$params[$i]->isOptional() && isset($m[$i])) {
                    $inputs[$params[$i]->getName()] = $m[$i];
                } else {
                    $allow = false;
                    $msgArray[] = $params[$i]->getName() . " is required";
                }
            }
        }

        if ($allow) {
//            if ($this->Route->oldVersion) {
//                if (method_exists($this, $this->method)) {
            call_user_func_array([$this, $this->method], $inputs);
//                }
//            }
        } else {
            $this->setResponseError($msgArray);
            $this->view_render(false);
        }

    }

    private function prepareForRunMethod()
    {

        if ($this->checkIsCommandInRequests()) {
            $this->runCommandMethodInRequests();
        } else if (isset($this->Route->path)) {
            if (method_exists($this, $this->method)) {
                $this->sendInputsToMethodAndRunIt();
            }
        }
    }

    public function view_render($input = true)
    {

        $this->Route->ViewRender = $input == false ? false : true;
    }

    public function getView($path = null, $args = [], $viewRender = null, $fullDirectory = null, $cache = false)
    {

        if ($path) {
            if ($fullDirectory) {
                $this->View->render($path, $args, $cache);
            } else {
                $this->View->render(JK_SITE_PATH() . $this->directory . $path, $args, $cache);
            }
            if ($viewRender == true) {
                $this->view_render(true);
            } elseif ($viewRender == false) {
                $this->view_render(false);
            }
        } else {
            return false;
        }
    }

    public function view($args = [], $path = null, $otherModule = null, $cache = false)
    {
        if ($this->Route->isApi) {
            $this->args = $this->Route->args;
            if (is_array($args) && sizeof($args) >= 1) {
                $this->args = array_merge($this->args, $args);
            }
            if (isset($this->args['response']['success'])) {
                $this->success = $this->args['response']['success'];
            } else {
                $this->success = true;
            }
            if (isset($this->args['response'])) {
                $this->data = $this->args['response'];
            }
            if (isset($this->args['response']['errors'])) {
                $this->errors = $this->args['response']['errors'];
            }
            if ($this->success) {
                $this->setResponseSuccess($this->data, $this->success);
            } else {
                if (empty($this->errors)) {
                    $this->errors[] = ['message' => __("not found")];
                }
                $this->setResponseError($this->errors, true, $this->status);
            }
            exit();
        }
        if ($otherModule && $path != null) {
            $path = JK_SITE_PATH() . "modules" . DS() . $otherModule . DS() . "Views" . DS() . $path;
            $this->View->render($path, $args, $cache);
        } elseif ($path != null) {
            $this->View->render(JK_SITE_PATH() . $this->directory . $path, $args, $cache);

        } elseif ($path == null) {
            $this->view_render(true, false);
            $this->View->render(null, $args, $cache);
        }
        if (is_bool($this->viewRender) && $this->viewRender) {
            $this->view_render(true);
        } else {
            $this->view_render(false);
        }
        return true;
    }

    public function getNormalMobilePhone($mobile)
    {
        if (substr($mobile, 0, 1) == "0") {
            $mobile = ltrim($mobile, '0');
        }
        return $mobile;
    }

    public function __construct(Route $Route = null, $sc = false)
    {
        $this->timeStart = microtime(true);

        $this->userId = JK_LOGINID();
        $this->module = $Route->module;

        if ($_SERVER['SCRIPT_NAME'] != "dev") {
            $this->Route = $Route;
            self::$route = $Route;
            $this->View = $this->Route->View;
            self::$DB = $this->Route->database;
            $this->database = self::$DB;
            $this->requests = $this->Route->requests;
            self::$req = $this->Route->requests;
            $routeObj = new \ArrayObject($this->Route->View);
            $this->directory = $routeObj->count() > 0 && $this->Route->View->directory != '' ? ltrim($this->Route->View->directory, './') : "";
            if (isset($this->Route->path['method'])) {
                $this->methodInject = $this->dest = $this->Route->path['method'];
                unset($this->Route->path['method']);
            } elseif ($sc) {
                $this->dest = isset($this->Route->path[2]) ? $this->Route->path[2] : 'index';
            } elseif (isset($this->Route->path[1])) {
                $this->dest = isset($this->Route->path[1]) ? $this->Route->path[1] : 'index';
            } else {
                $this->dest = "index";
            }
            $body = file_get_contents('php://input');
            $jsonInputs = is_json($body, true, true);
            $this->inputs = !empty($jsonInputs) ? $jsonInputs : $this->requests->all();
            $this->method = strtolower($this->Route->requests->requestMethod() . '_' . $this->dest);

            if (method_exists($this, 'init')) {
                $this->init();
                $this->method = strtolower($this->Route->requests->requestMethod() . '_' . $this->dest);
            }
            if ($this->continue) {
                $this->prepareForRunMethod();
            }
        }
    }

    public function aimIsCp()
    {
        return $this->Route->isCpRoute;
    }

    public function viewObject()
    {
        return $this->Route->View;
    }

    public function __destruct()
    {
        $this->prepareOutput();
        if ($this->export == 'json' && !empty($this->Route->isApi)) {
            echo json_encode($this->output, 256 | 128);
            $this->Route->ViewRender = false;
        }
    }

    public function hasPermission($permKey, $companyId = false)
    {
        $ACL = \Joonika\ACL::ACL();
        if (!$ACL->hasPermission($permKey, $companyId)) {
            $message = '';
            if (!JK_LOGINID()) {
                $message = __("you are not logged or session expired");
            }
            if (empty($message)) {
                $database = Database::connect();
                $langPerm = $database->get("jk_users_permissions", 'permName', [
                    "permKey" => $permKey
                ]);
                $lang = !empty($langPerm) ? __($langPerm) : "";
//                $lang="";
                if (!empty($lang)) {
                    $message = sprintf(__("you don`t have access: %s"), $lang);
                } else {
                    $message = __("access denied");
                }
            }
            if (JK_APP_DEBUG()) {
                $message .= ' - ' . $permKey;
            }
            if ($this->Route->isApi) {
                $this->setResponseError($message, true, 403);
            } else {
                error403(true, $message);
                die;
            }

        }
    }

    public function validate($variables = [], $inputs = [], $return = false)
    {
        $inputs = !empty($inputs) ? $inputs : $this->inputs;
        $variables = is_array($variables) ? $variables : [$variables];
        $validator = new Validator\validator($variables, $inputs);
        $alerts = [];
        if (!empty($validator->validateAlerts)) {
            foreach ($validator->validateAlerts as $alert) {
                if ($return) {
                    $alerts[] = $alert;
                } else {
                    $this->setResponseError($alert, false);
                }
            }
            if ($return) {
                return $alerts;
            } else {
                exit();
            }
        }
    }

    public function arrayBlacklistUnset($request, $keys = [])
    {

        if (!empty($keys) && !empty($request)) {
            foreach ($keys as $k => $v) {
                if (is_array($v)) {
                    if (isset($request[$k])) {
                        $request[$k] = $this->arrayBlacklistUnset($request[$k], $v);
                    }
                } else {
                    if (isset($request[$v])) {
                        unset($request[$v]);
                    } elseif (is_array($request)) {
                        foreach ($request as $rvK => $rv) {
                            $request[$rvK] = $this->arrayBlacklistUnset($request[$rvK], [$v]);
                        }
                    }

                }
            }
        }
        return $request;
    }

    public function inputs($variable)
    {
        return $this->inputs[$variable] ?? null;
    }

    public function returnResponseSuccess($data = [])
    {
        return [
            "success" => true,
            "data" => $data,
        ];
    }

    public function returnResponseErrors($errors = [])
    {
        $errors = is_array($errors) ? $errors : [$errors];
        $output = [];
        if (!empty($errors)) {
            foreach ($errors as $error) {
                if (!isset($error['message'])) {
                    $output[] = [
                        "message" => $error
                    ];
                } else {
                    $output[] = $error;
                }
            }
        }
        return [
            "success" => false,
            "errors" => $output,
        ];
    }

}

