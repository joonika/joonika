<?php
namespace Joonika;
if(!defined('jk')) die('Access Not Allowed !');

class Translate
{

    public function __construct()
    {
        global $database;
        global $translate;
        $entries=$database->select('jk_translate',["var","text"],[
            "lang"=>JK_LANG
        ]);
        if(sizeof($entries)>=1){
            foreach ($entries as $entrie){
                $translate[$entrie['var']]=$entrie['text'];
            }
        }
    }
}