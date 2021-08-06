<?php


namespace Joonika\Validator;


class validator
{
    private $constraintsLists = [];
    private $inputs = null;
    public $validateAlerts = [];

    public function __construct($variables = [], $inputs = null)
    {
        if (!empty($variables)) {
            $this->inputs = $inputs;
            foreach ($variables as $variablekey=>$variableArray) {
                $title=null;
                if(is_int($variablekey)){
                    $variable=$variableArray;
                }else{
                    $variable=$variablekey;
                    $title=$variableArray;
                }
                $structure = explode('|', $variable);
                if (sizeof($structure) == 2) {
                    $field = $structure[0];
                    $this->userConstraintsLists[$field] = [];
                    $conditions = explode(',', $structure[1]);
                    if (!empty($conditions)) {
                        foreach ($conditions as $condition) {
                            $subConditions = explode(':', $condition);
                            $methodName = $subConditions[0];
                            $methodVar = !empty($subConditions[1]) ? $subConditions[1] : null;
                            if (method_exists($this, $methodName)) {
                                $this->$methodName($field, $methodVar,$title);
                            }
                        }
                    }
                } elseif (sizeof($structure) == 1) {
                    $this->required($variable, null,$title);
                }
            }
        }
    }


    public function required($key, $value = null,$title=null)
    {
        if (empty($this->inputs[$key])) {
            $this->validateAlert(sprintf(__("field `%s` is required"),!empty($title)?$title:$key), $key);
        }
    }

    private function validateAlert($alert, $key)
    {
        $this->validateAlerts = array_merge($this->validateAlerts, [[
            "source" => $key,
            "message" => $alert,
        ]]);
    }

    public function isset($key, $value = null,$title=null)
    {
        if (!isset($this->inputs[$key])) {
            $this->validateAlert(sprintf(__("field `%s` is not set"),!empty($title)?$title:$key), $key);
        }
    }

    public function min($key, $value = 0,$title=null)
    {
        if (isset($this->inputs[$key])) {
            $valueCheck = '';
            $valueCheck = is_string($this->inputs[$key]) ? (strlen($this->inputs[$key])) : (is_array($this->inputs[$key]) ? sizeof($this->inputs[$key], 0) : $this->inputs[$key]);
            if ($valueCheck < $value) {
                $this->validateAlert(sprintf(__("minimum of `%s` must be %s"), (!empty($title)?$title:$key),$value), $key);
            }
        }
    }

    public function max($key, $value = 0,$title=null)
    {
        $valueCheck = is_string($this->inputs[$key]) ? (strlen($this->inputs[$key])) : (is_array($this->inputs[$key]) ? sizeof($this->inputs[$key], 0) : $this->inputs[$key]);
        if (isset($this->inputs[$key])) {
            if ($valueCheck > $value) {
                $this->validateAlert(sprintf(__("minimum of `%s` must be %s"), (!empty($title)?$title:$key),$value), $key);
            }
        }
    }
}