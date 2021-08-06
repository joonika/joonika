<?php


namespace Joonika\Gates;


use phpDocumentor\Reflection\Types\Self_;

abstract class Gates
{
    private static $Gates = [];
    private $Instance = null;

    public static function getGetes()
    {
        return self::$Gates;
    }

    public static function getGete($gate)
    {
        return self::$Gates;
    }

    public static function Define($name, $callback)
    {
        self::$Gates[$name] = $callback;
    }

    abstract public static function registerGate();

    public static function __callStatic(string $name, array $arguments)
    {
        if (!empty(self::getGetes()[$name])) {
            $gate = self::getGetes()[$name];
            if (checkArraySize($gate)) {
                $class = $gate[0];
                if (sizeof($gate) == 2) {
                    if (method_exists($class, $gate[1])) {
                        $method = $gate[1];
                        $MethodChecker = new \ReflectionMethod($class, $method);
                        if ($MethodChecker->isStatic()) {
                            return $class::$method($arguments);
                        } else {
                            $instance = new $class();
                            return $instance->$method($arguments);
                        }
                    }
                } elseif (sizeof($gate) == 1) {
                    if (class_exists($class)) {
                        return new $class($arguments);
                    } elseif (function_exists($class)) {
                        return $class($arguments);
                    } else {
                        return __("invalid function or class");
                    }
                }
            }
        }
    }
}