<?php


namespace Joonika;


class Token
{

    public static function tokenGenerate($userID, $mobile, $source = 'app', $notRemoveOldSession = false, $platForm = null, $apiId = null, $expired = 172800)
    {
        $database = Database::connect();
        $stringToEncrypt = time() . $mobile;
        $userAgentDetect = new Browser();
        $userAgent = empty($platForm['userAgent']) ? $userAgentDetect->getUserAgent() : $platForm['userAgent'];
        $browser = empty($platForm['browser']) ? $userAgentDetect->getBrowser() : $platForm['browser'];
        $userPlatform = empty($platForm['userPlatform']) ? $userAgentDetect->getPlatform() : $platForm['userPlatform'];
        if ($platForm) {
            foreach (['os', 'brand', 'model'] as $item) {
                if (isset($platForm[$item]) && !is_null($platForm[$item]) && $platForm[$item] != "") {
                    $stringToEncrypt .= "__" . $platForm[$item];
                } elseif (!isset($platForm[$item]) || is_null($platForm[$item]) || $platForm[$item] == '') {
                    $stringToEncrypt .= "__ ";
                }
            }
        }

        $token = self::encryptAndDecryptString($stringToEncrypt, 'e');

        $hastDupToken = $database->get('jk_users_tokens', 'id', [
            "AND" => [
                "token" => $token,
                "userAgent" => $userAgent,
            ]
        ]);
        if (empty($hastDupToken)) {
            $users_allowActiveSessionCount = jk_options_get('users_allowActiveSessionCount');
            if (!empty($users_allowActiveSessionCount)) {
                $actives = $database->select("jk_users_tokens", 'id', [
                    "userID" => $userID,
                    "ORDER" => ["id" => "DESC"],
                    "LIMIT" => ($users_allowActiveSessionCount-1),
                ]);
                $actives = !empty($actives) ? $actives : 0;
                $database->update('jk_users_tokens', [
                    "status" => "inactive",
                    "expired" => now(),
                ], [
                    "userID" => $userID,
                    "id[!]" => $actives
                ]);
            } elseif (!$notRemoveOldSession) {
                $database->update('jk_users_tokens', [
                    "status" => "inactive",
                    "expired" => now(),
                ], [
                    "AND" => [
                        "userID" => $userID,
                        "source" => $source,
                    ]
                ]);
            }
            $database->insert('jk_users_tokens', [
                "userID" => $userID,
                "mobile" => $mobile,
                "token" => $token,
                "datetime" => now(),
                "source" => $source,
                "apiId" => $apiId,
                "userAgent" => $userAgent,
                "browser" => $browser,
                "platform" => $userPlatform,
                "expired" => date("Y/m/d H:i:s", time() + $expired),
            ]);
            $insertId = $database->id();
            return ['status' => 200, 'token' => $token, 'id' => $insertId];
        } else {
            return self::tokenGenerate($userID, $mobile, $source, $notRemoveOldSession, $platForm, $apiId);
        }
    }

    public static function encryptAndDecryptString($string, $action = 'e')
    {
        // you may change these values to your own
        $secret_key = 'dfsf8ojpij4330jcvxc';
        $secret_iv = 'dfsf8ojpij4330jcvxc';
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'e') {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        } else if ($action == 'd') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
}