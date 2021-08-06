<?php

namespace Joonika;


use Joonika\Modules\Users\Company;
use Joonika\Modules\Users\UserInfo;
use Joonika\Modules\Users\Users;

class ACL
{
    public $perms = array();        //Array : Stores the permissions for the user
    public $userID = 0;            //Integer : Stores the ID of the current user
    public $userRoles = array();    //Array : Stores the roles of the current user
    private static $instance = null;
    private $database;
    private static $inputUserId = null;

    private function __construct($userID = '')
    {
        if ($userID != '') {
            $this->userID = floatval($userID);
            self::$inputUserId = $this->userID;
        } elseif (Controller::$JK_LOGIN_ID > 0) {
            $this->userID = floatval(Controller::$JK_LOGIN_ID);
            self::$inputUserId = $this->userID;
        } elseif (isset($_SESSION[JK_DOMAIN_WOP()]['userID'])) {
            $this->userID = $_SESSION[JK_DOMAIN_WOP()]['userID'];
            self::$inputUserId = null;
        } elseif (Route::JK_LOGINID()) {
            $this->userID = Route::JK_LOGINID();
            self::$inputUserId = null;
        } else {
            $this->userID = 0;
            self::$inputUserId = null;
        }
        $this->database = Database::connect();
    }

    public function hasPermission($permKey, $companyID = false)
    {
        $userID = $this->userID;
        $database = $this->database;
        $perm = false;
        $realPerm = false;
        $permArray = [];
        $checkedCompany = [];
        $isSuperAdmin = UserInfo::getUserInfo($userID, 'superAdmin');
        if(is_string($companyID)){
            $companyID = (int)$companyID;
        }
        $type = gettype($companyID);
        if ($isSuperAdmin == 1) {
            $realPerm = true;
            $permArray = 'all';
        } else {
            $permID = $database->cache()->get(\Modules\users\inc\Configs::$databaseInfo['permissions'], 'id', [
                "permKey" => $permKey,
            ]);
            if ($permID) {
                $condition = [
                    "AND" => [
                        'permissionID' => $permID,
                        'userID' => $userID,
                        'companyID' => null
                    ]
                ];
                $conditionRole = [
                    "AND" => [
                        "jur.userID" => $userID,
                        'jurp.permissionID' => $permID,
                        'jur.companyID' => null,
                        'jur.status' => 'active'
                    ]
                ];
                $directPermission = $database->cache()->get(\Modules\users\inc\Configs::$databaseInfo['rolePermissions'],
                    'value',
                    $condition
                );
                if (is_null($directPermission)) {
                    $permissions = $database->cache()->select('jk_users_roles(jur)',
                        ['[><]jk_users_roles_permissions(jurp)' => ['jur.roleID' => 'roleID']],
                        [
                            'jurp.id',
                        ],
                        $conditionRole);
                    $realPerm = checkArraySize($permissions) ? true : false;
                } else {
                    $realPerm = $directPermission == 1 ? true : false;
                }
                unset($condition['AND']['companyID']);
                unset($conditionRole['AND']['jur.companyID']);
                switch ($type) {
                    case 'boolean':
                        if ($companyID) {
                            $condition ["AND"]['companyID[!]'] = null;
                        } else {
                            return $realPerm;
                        }
                        break;
                    case 'integer':
                        $companyIDArray = [$companyID];
                        $condition ["AND"]['companyID'] = $companyID;
                        break;
                    case 'array':
                        $companyIDArray = $companyID;
                        $condition ["AND"]['companyID'] = $companyID;
                        break;
                }

                $directPermissions = $database->cache()->select(\Modules\users\inc\Configs::$databaseInfo['rolePermissions'],
                    [
                        'value',
                        'companyID'
                    ],
                    $condition
                );
                foreach ($directPermissions as $directPermission) {
                    $checkedCompany[] = $directPermission['companyID'];
                    if ($directPermission['value'] == 1) {
                        $permArray[] = $directPermission['companyID'];
                    }
                }
                if ($type == 'integer' || $type == 'array') {
                    $companyIDRemaining = array_diff($companyIDArray, $checkedCompany);
                    if (checkArraySize($companyIDRemaining)) {
                        $conditionRole ["AND"]['jur.companyID'] = $companyIDRemaining;
                    } else {
                        if ($type == 'integer') {
                            if (checkArraySize($permArray)) {
                                return true;
                            } else {
                                return false;
                            }
                        } else {
                            return [
                                'realpermission' => $realPerm,
                                'companyID' => $permArray
                            ];
                        }
                    }
                } else {
                    if (checkArraySize($checkedCompany)) {
                        $conditionRole ["AND"]['jur.companyID[!]'] = $checkedCompany;
                    } else {
                        $conditionRole ["AND"]['jur.companyID[!]'] = null;
                    }
                }
                $permissions = $database->cache()->select('jk_users_roles(jur)',
                    ['[><]jk_users_roles_permissions(jurp)' => ['jur.roleID' => 'roleID']],
                    [
                        'jur.id',
                        'jur.companyID',
                    ],
                    $conditionRole
                );
                foreach ($permissions as $permission) {
                    $permArray[] = $permission['companyID'];
                }
                if ($realPerm && !checkArraySize($permArray)) {
                    if ($permKey != 'operator') {
                        if ($this->hasPermission('operator')) {
                            $permArray = 'all';
                        }
                    }
                }
            }
        }
        switch ($type) {
            case 'boolean':
                if ($companyID) {
                    if ($realPerm || $permArray) {
                        return [
                            'realpermission' => $realPerm,
                            'companyID' => $permArray
                        ];
                    } else {
                        return false;
                    }
                }else{
                    return $realPerm;
                }
                break;
            case 'integer':
                if ($permArray) {
                    return true;
                } else {
                    return false;
                }
            case 'array':
                if ($permArray) {
                    return [
                        'realpermission' => $realPerm,
                        'companyID' => $permArray
                    ];
                } else {
                    return false;
                }
        }
    }

    public function hasPermissionByID($permID, $companyID = false)
    {
        $perm = false;
        $permKey = Database::connect()->get(\Modules\users\inc\Configs::$databaseInfo['permissions'], 'permKey', [
            "id" => $permID,
        ]);
        if ($permKey) {
            $perm = $this->hasPermission($permKey, $companyID);
        }
        return $perm;
    }

    public function hasPermissionLogin($permKey, $companyID = false)
    {
        $perm = false;
        Users::loggedCheck();
        $perm = $this->hasPermission($permKey, $companyID);
        return $perm;
    }

    public static function aclNameById($permID)
    {
        $get = Database::connect()->get(\Modules\users\inc\Configs::$databaseInfo['permissions'], ['permName'], [
            "id" => $permID
        ]);
        if (isset($get['permName'])) {
            return $get['permName'];
        } else {
            return null;
        }
    }

    public function checkValueHtmlFa($value, $type = 1)
    {
        if (($type == 10 && $value == 10) || ($type == 1 && $value == 1) || ($type == 'active' && $value == "active")) {
            return '<i class="fa fa-check text-success"></i>';
        } else {
            return '<i class="fa fa-times text-danger"></i>';
        }
    }

    public static function permlist($module)
    {
        $permIDs = Database::connect()->select(\Modules\users\inc\Configs::$databaseInfo['permissions'], '*', [
            "module" => $module
        ]);
        return $permIDs;
    }

    public static function ACL($input = null)
    {
        if (!is_null(self::$instance) && $input && $input != self::$inputUserId) {
            self::$instance = null;
        }

        if (self::$instance == null) {
            self::$instance = new ACL($input);
        }
        if (!is_null(self::$instance)) {
            $m = self::$instance;
            return $m;
        } else {
            return false;
        }
    }
}


