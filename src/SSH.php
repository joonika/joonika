<?php


namespace Joonika;

use phpseclib\Net\SFTP;

class SSH
{
    public static function getSshConfig()
    {
        $mainConfig = array_column(JK_WEBSITES(), 'ssh');
        if (checkArraySize($mainConfig)) {
            $mainConfig = reset($mainConfig);
            $mainSsh = [
                'name' => $mainConfig['name'],
                'host' => $mainConfig['host'],
                'port' => $mainConfig['port'],
                'user' => $mainConfig['user'],
                'pass' => $mainConfig['pass'],
            ];

            $sshList = [];
            $sshList['main'] = $mainSsh;
            if (isset($mainConfig['other'])) {
                $others = $mainConfig['other'];
                $entire = [];
                foreach ($others as $other) {
                    if (gettype($other) == 'array') {
                        $entire = array_merge($mainSsh, $other);
                        $sshList[$other['name']] = $entire;
                    } else {
                        $entire['db'] = $other;
                        $entire = array_merge($mainSsh, $entire);
                        $sshList[$other] = $entire;
                    }
                }
            }
            return $sshList;
        }
        return 'bad config';
    }

}