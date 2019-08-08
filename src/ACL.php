<?php

namespace Joonika;
use Joonika\Modules\Ws\Ws;

if(!defined('jk')) die('Access Not Allowed !');

class ACL
{
    var $perms = array();        //Array : Stores the permissions for the user
    var $userID = 0;            //Integer : Stores the ID of the current user
    var $userRoles = array();    //Array : Stores the roles of the current user

    function __construct($userID = '')
    {
        if ($userID != '') {
            $this->userID = floatval($userID);
        } elseif (isset($_SESSION[JK_DOMAIN_WOP]['userID'])) {
            $this->userID = $_SESSION[JK_DOMAIN_WOP]['userID'];
        } else {
            $this->userID = 0;
        }
    }

    function hasPermission($permKey)
    {
        global $database;
        $issuperadmin=$database->has('jk_users',[
            "AND"=>[
                "id"=>$this->userID,
                "superAdmin"=>1,
            ]
        ]);
        $perm=false;
        if($issuperadmin){
            $perm=true;
        }else{
            global $database;

            $permID=$database->get('jk_users_permissions','ID',[
                "permKey"=>$permKey,
            ]);
            if(isset($permID) && $permID!=''){
                $permIDs=$database->has('jk_users_perms_users',[
                    "AND"=>[
                        "permID"=>$permID,
                        "userID"=>$this->userID,
                        "value"=>1
                    ]
                ]);
                if($permIDs){
                    $perm=true;
                }
                if($this->userID!=0){
                    $rolegrs=$database->select('jk_users_groups_rel','*',[
                        "AND"=>[
                            "userID"=>$this->userID,
                            "status"=>'active',
                            ]
                    ]);
                    if(sizeof($rolegrs)>=1){
                        foreach ($rolegrs as $rolegr){
                            $groups=[$rolegr['groupID']];
                            $groups = \Joonika\Modules\Users\groupsParentGroups($rolegr['groupID'],$groups);
                            $permIDs_role=$database->has('jk_users_perms_groups',[
                                "AND"=>[
                                    "permID"=>$permID,
                                    "OR"=>[
                                        "AND"=>[
                                            "groupID"=>$groups,
                                            "intermittent"=>1,
                                        ],
                                        "groupID"=>$rolegr['groupID']
                                    ],
                                    "roleID"=>[$rolegr['roleID'],0],
                                    "value"=>1
                                ]
                            ]);


                            if($permIDs_role){
                                $perm=true;
                            }
                        }
                    }
                }

                $permIDs=$database->has('jk_ccs_perms_users',[
                    "AND"=>[
                        "permID"=>$permID,
                        "userID"=>$this->userID,
                        "value"=>0
                    ]
                ]);
                if($permIDs){
                    $perm=false;
                }
            }
        }

        return $perm;
    }
    function hasPermissionLogin($permKey)
    {
        $perm=false;
        global $Users;
        global $View;
        $View->login_page="";
        $Users->loggedCheck();
        $perm=$this->hasPermission($permKey);
        return $perm;

    }

    function permlist($module)
    {
        global $database;
        $permIDs=$database->select('jk_users_permissions','*',[
            "module"=>$module
        ]);
        return $permIDs;
    }

}
function aclNameById($permID){
    global $database;
    $get=$database->get('jk_users_permissions',['permName'],[
        "id"=>$permID
    ]);
    if(isset($get['permName'])){
        return $get['permName'];
    }else{
        return null;
    }
}

function checkValueHtmlFa($value,$type=1){
    if(($type==10 && $value==10) || ($type==1 && $value==1) || ($type=='active' && $value=="active")){
        return '<i class="fa fa-check text-success"></i>';
    }else{
        return '<i class="fa fa-times text-danger"></i>';
    }
}