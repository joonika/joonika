<?php


namespace Joonika\UAC;


use Joonika\ACL;
use Joonika\Database;
use Joonika\Redirect;

class UAC
{
    protected static $result;
    protected static $ACL;
    protected static $hasPermission = false;
    protected static $legalInfo = null;

    public function __construct()
    {

    }

    private static function getAclResult($aclResult, $continue = true)
    {
        if ($continue) {
            if (is_array($aclResult)) {
                if ($aclResult[0]) {
                    static::$hasPermission = true;
                    return true;
                } else {
                    return false;
                }
            } elseif (is_bool($aclResult)) {
                if ($aclResult) {
                    static::$hasPermission = true;
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            if (is_array($aclResult)) {
                self::$legalInfo = $aclResult[1];
                return true;
            } elseif (is_bool($aclResult)) {
                return true;
            }
        }
    }

    public static function hasPermission($permission, $companyID = false)
    {
        self::$ACL = ACL::ACL();
        if (self::getAclResult(self::$ACL->hasPermission($permission, $companyID))) {
            return self::getAclResult(self::$ACL->hasPermission($permission, $companyID), false);
        } else {
            return false;
        }
    }

    public static function checkHasPermission($permission, $companyID = false)
    {
        static::$ACL = ACL::ACL();
        if (static::getAclResult(self::$ACL->hasPermission($permission, $companyID))) {
            self::getAclResult(self::$ACL->hasPermission($permission, $companyID), false);
        }
        return new static;
    }

    public static function checkhasPermissionLogin($permission, $companyID = false)
    {
        static::$ACL = ACL::ACL();
        static::$ACL->hasPermissionLogin($permission, $companyID);
    }

    public static function redirectIfFailed($to)
    {
        if (!self::$hasPermission) {
            if (is_string($to)) {
                redirect_to(url($to));
            } elseif (is_integer($to)) {
                $method = 'error' . $to;
                if (method_exists(Redirect::class, $method)) {
                    Redirect::$method();
                }
            }
        }
    }

    public static function redirectIfSuccesses($to)
    {
        if (self::$hasPermission) {
            if (is_string($to)) {
                redirect_to(url($to));
            } elseif (is_integer($to)) {
                $method = 'error' . $to;
                if (method_exists(Redirect::class, $method)) {
                    Redirect::$method();
                }
            }
        }
    }

    public static function callbackIfFailed($callback)
    {
        if (!self::$hasPermission) {
            $callback();
        }
    }

    public static function callbackIfSuccesses($callback)
    {
        if (self::$hasPermission) {
            $callback();
        }
    }

    public static function runMethodIfFailed($class, $method, $input = null)
    {
        if (!self::$hasPermission && class_exists($class) && method_exists($class, $method)) {
            $instance = new $class();
            $instance->$method($input);
        }
    }

    public static function runMethodIfSuccesses($class, $method, $input = null)
    {
        if (self::$hasPermission && class_exists($class) && method_exists($class, $method)) {
            $instance = new $class();
            $instance->$method($input);
        }
    }

    public static function legalInfo()
    {
        if (self::$hasPermission && !is_null(self::$legalInfo)) {
            return self::$legalInfo;
        } else {
            return false;
        }
    }

    public static function Gate($gate, ...$inputs)
    {
        $Gates = GateProvider::getGetes();
        if (isset($Gates[$gate])) {
            if (checkArraySize($Gates[$gate]) && sizeof($Gates[$gate]) == 2) {
                $class = $Gates[$gate][0];
                $method = $Gates[$gate][1];
            } else {
                $class = $Gates[$gate];
                $method = $gate;
            }
            if (class_exists($class) && method_exists($class, $method)) {
                return $class::$method($inputs);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}