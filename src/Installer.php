<?php

namespace Joonika;

class Installer
{

    public static function joonikaInstaller()
    {
        self::copy_directory(__DIR__.'/idate/assets','public/assets/idate');
    }
    private static function copy_directory($src,$dst) {
        $dir = opendir($src);
        mkdir($dst, 0777, true);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
