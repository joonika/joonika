<?php

namespace Joonika\boom;

use Doctrine\Tests\Common\Annotations\Fixtures\ClassDDC1660;
use Joonika\Database;
use Joonika\Errors;

class events
{

    protected $sortedListeners = [];
    protected $priorities = [];
    protected $listeners = [];
    protected $inputs;
    protected $input;
    protected $after = 1;
    protected $tryAfter = 1;
    protected $runByCronJob = false;
    public $returnErrors = [];
    public $event;
    public $return = false;


    public function __construct($action = null, $data = null, $return = null)
    {


        $this->inputs = $data;
        $this->event = $action;
        $this->return = $return;

        if ($action == "runCronJobs") {
            $this->runByCronJob = true;
        }

        $this->priorities = method_exists("Config\sortBooms", 'sort') ? \Config\sortBooms::sort() : [];

        if (checkArraySize($this->priorities)) {
            $diff = array_diff(listModules(), $this->priorities);
            if (checkArraySize($diff)) {
                foreach ($diff as $single) {
                    $this->priorities[] = $single;
                }
            }
        } else {
            $this->priorities = listModules();
        }

        if ($this->runByCronJob) {
            $listeners = Database::query("SELECT * FROM jk_listeners_queue WHERE (executeAt is null && result=0 ) || ( executeAt<now() && result=0)")->fetchAll(\PDO::FETCH_ASSOC);
            if (checkArraySize($listeners)) {
                foreach ($this->priorities as $priority) {
                    foreach ($listeners as $listener) {
                        if ($priority == $listener['module']) {
                            $this->sortedListeners[$priority][$listener['listener']] = "Modules\\" . $priority . "\boom\\" . $listener['event'] . "|||" . $listener['id'];
                        }
                    }
                }
            }
        } else {
            foreach (listModules() as $module) {
                $class = "Modules\\" . $module . "\boom\\" . $action;
                if (method_exists($class, 'define')) {
                    $defines = $class::define();
                    if (checkArraySize($defines)) {
                        foreach ($defines as $key => $value) {
                            $this->listeners[$value] = $class;
                        }
                    }
                }
            }
            foreach ($this->priorities as $priority) {
                foreach ($this->listeners as $method => $class) {
                    $classMain = explode('\boom\\', $class);
                    if (sizeof($classMain, 0) == 2) {
                        $className = str_replace('Modules\\', '', $classMain[0]);
                        if ($priority == $className) {
                            $this->sortedListeners[$priority][$method] = $class;
                        }
                    }

                }
            }
        }
    }

    public function after($after)
    {
        $this->after = $after;
        return $this;
    }

    public function afterMin($min)
    {
        $this->after = $min * 60;
        return $this;
    }

    public function afterHour($hour)
    {
        $this->after = $hour * 3600;
        return $this;
    }

    public function tryAfterMin($min)
    {
        $this->tryAfter = $min * 60;
        return $this;
    }

    public function tryAfterHour($hour)
    {
        $this->tryAfter = $hour * 3600;
        return $this;
    }

    public function __destruct()
    {
        $this->fire();
    }

    final  private function fire()
    {
        $inputs = json_encode($this->inputs, JSON_UNESCAPED_UNICODE);
        if (checkArraySize($this->sortedListeners)) {
            foreach ($this->sortedListeners as $module => $sortedListener) {
                if (checkArraySize($sortedListener)) {
                    foreach ($sortedListener as $method => $class) {
                        $classExplode = explode('|||', $class);
                        $class = $classExplode[0];
                        $listenerID = sizeof($classExplode) == 2 ? $classExplode[1] : null;

                        if (method_exists($class, $method)) {

                            if ($listenerID) {
                                $listenerConditions = [
                                    "id" => $listenerID
                                ];
                            } else {
                                $listenerConditions = [
                                    "AND" => [
                                        'listener' => $method,
                                        'inputs' => $inputs
                                    ]
                                ];
                            }

                            $existListener = Database::get('jk_listeners_queue', '*', $listenerConditions);
                            $listenerID = $existListener ? $existListener['id'] : null;
                            if (!$this->runByCronJob) {
                                $inputsConditions = [
                                    "listener" => $method,
                                    "event" => $this->event,
                                    "module" => $module,
                                    "result" => false,
                                    "inputs" => $inputs,
                                    "registerTime" => now(),
                                    "return" => $this->return,
                                ];
                                if ($this->after > 1) {
                                    $inputsConditions['executeAt'] = date('Y/m/d H:i:s', time() + $this->after);
                                }

                                Database::insert('jk_listeners_queue', $inputsConditions);
                                $listenerID = Database::id();
                            }

                            if ($this->after <= 1 || $this->runByCronJob) {
                                if ($listenerID) {
                                    $oldListener = Database::get('jk_listeners_queue', '*', [
                                        "AND" => [
                                            'id' => $listenerID
                                        ]
                                    ]);

                                    if ($oldListener) {
                                        $executionTimeStart = microtime(TRUE);
                                        try {
                                            Database::update('jk_listeners_queue', [
                                                'lastTryTime' => now()
                                            ], ['id' => $oldListener['id']]);

                                            if ($this->runByCronJob) {
                                                $this->return = $oldListener['return'];
                                            }

                                            $instance = new $class($this->inputs, $listenerID, $this->return);
                                            $result = $instance->$method();
                                            if ($this->return) {
                                                if (isset($result['code']) && $result['code'] == 400) {
                                                    $this->returnErrors[$oldListener['id']] = [
                                                        'listener' => $oldListener['listener'],
                                                        'module' => $oldListener['module'],
                                                        'class' => "Modules\\" . $oldListener['module'] . "\boom\\" . $oldListener['listener'],
                                                        'message' => $result['message'] ?? null,
                                                        'result' => $oldListener['result'],
                                                    ];
                                                }
                                            }

                                            Database::update('jk_listeners_queue', [
                                                "executionTime" => microtime(TRUE) - $executionTimeStart
                                            ], ['id' => $oldListener['id']]);

                                        } catch (\Exception $exception) {
                                            Database::update("jk_listeners_queue", [
                                                "errors" => Errors::exceptionString($exception),
                                                "lastErrorDate" => date("Y-m-d H:i:s"),
                                                "executionTime" => microtime(TRUE) - $executionTimeStart
                                            ], [
                                                "id" => $listenerID
                                            ]);

                                            if ($this->return) {
                                                $this->returnErrors[$oldListener['id']] = [
                                                    'listener' => $oldListener['listener'],
                                                    'module' => $oldListener['module'],
                                                    'class' => "Modules\\" . $oldListener['module'] . "\boom\\" . $oldListener['listener'],
                                                    'message' => Errors::exceptionString($exception)
                                                ];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($this->return) {
            if (checkArraySize($this->returnErrors)) {
                return [
                    'success' => false,
                    'errors' => $this->returnErrors,
                ];
            } else {
                return [
                    'success' => true
                ];
            }
        }
    }
}