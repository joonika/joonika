<?php

namespace Joonika\Traits;


use Joonika\Browser;
use Joonika\Controller;
use Joonika\Database;
use Joonika\Errors;
use Joonika\Route;
use Joonika\Token;

trait Tokens
{
    public $userInfo = null;
    public $device = null;
    public $userId = 0;
    public static $JK_LOGIN_ID = 0;

    public function userApi()
    {
        if ($this->userId) {
            $apiID = Database::connect()->get('jk_users_tokens', 'apiId', ['userID' => $this->userId]);
            $api = Database::connect()->get('ws', 'key', ['id' => $apiID]);
            return $api;
        } else {
            return null;
        }
    }
}