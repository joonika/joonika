<?php

namespace Joonika\Migration;

use Joonika\Database;
use Joonika\Migration\Migration;

class joonika extends Migration
{
    public function init()
    {
        return [
            "logs.errors_log" => [
                "version" => 1,
                "columns" => [
                    "id" => $this->id_structure(true),
                    "date" => $this->date(),
                    "datetime" => $this->datetime(),
                    "file" => $this->varchar(),
                    "line" => $this->varchar(),
                    "message" => $this->text(),
                    "trace" => $this->text(),
                    "status" => $this->varchar(30, false, "'new'"),
                    "request" => $this->text(),
                    "count" => $this->int(1),
                    "userID" => $this->int(0),
                    "lastOccurred" => $this->datetime(),
                    "description" => $this->text(),
                    "ownerID" => $this->int(),
                    "website" => $this->varchar(),
                ],
                'indexes' => [
                    "file,line",
                    "trace",
                ]
            ],
            "jk_data" => [
                "version" => 1,
                "columns" => [
                    "id" => $this->id_structure(),
                    "title" => $this->varchar(),
                    "slug" => $this->varchar(100),
                    "description" => $this->text(),
                    "text" => $this->longtext(),
                    "datetime" => $this->datetime(),
                    "datetime_s" => $this->datetime(),
                    "thumbnail" => $this->text(),
                    "creatorID" => $this->int(),
                    "views" => $this->int(0),
                    "parent" => $this->int(0),
                    "websiteID" => $this->int(),
                    "lang" => $this->varchar(3),
                    "module" => $this->varchar(20),
                    "htmlMode" => $this->varchar(10),
                    "dataScripts" => $this->text(),
                    "dataStyles" => $this->text(),
                    "template" => $this->varchar(),
                    "sort" => $this->int(1),
                    "status" => $this->varchar(20, false, "'active'"),
                ],
                "indexes" => [
                    "slug",
                    "status",
                    "status,slug",
                ],
            ],
            "jk_options" => [
                "version" => 1,
                "columns" => [
                    "id" => $this->id_structure(true),
                    "name" => $this->varchar(),
                    "value" => $this->text(),
                ],
                "indexes" => [
                    "name(unique)",
                ],
            ],
            "jk_translate" => [
                "version" => 1,
                "columns" => [
                    "id" => $this->id_structure(true),
                    "var" => $this->varchar(),
                    "lang" => $this->varchar(3),
                    "text" => $this->varchar(),
                    "status" => $this->varchar(20, true, "'active'"),
                    "dest" => $this->varchar(40),
                    "type" => $this->varchar(40),
                ],
                "indexes" => [
                    "var,lang,dest,type(unique)",
                    "var,lang,status,dest",
                    "var,lang,status",
                    "var,lang",
                ],
            ],
            "jk_listeners_queue" => [
                "version" => 1,
                "columns" => [
                    "id" => $this->id_structure(true),
                    "event"=>$this->varchar(),
                    "listener"=>$this->varchar(),
                    "module"=>$this->varchar(),
                    "result"=>$this->int(),
                    "inputs"=>$this->text(),
                    "errors"=>$this->text(),
                    "expire"=>$this->varchar(),
                    "registerTime"=>$this->datetime(),
                    "lastErrorDate"=>$this->datetime(false),
                    "lastTryTime"=>$this->datetime(false),
                    "executionTime"=>$this->float(),
                    "executeAt"=>$this->datetime(false),
                    "outputReturn"=>$this->int(0,1),
                    "cronTask"=>$this->int(-1,2),
                ],
            ],
            "cronjob_functions" => [
                "version" => 1,
                "columns" => [
                    "id" => $this->id_structure(true),
                    "moduleName"=>$this->varchar(),
                    "functionName"=>$this->varchar(),
                    "cronTab"=>$this->varchar(30,true,"'* * * * *'"),
                    "parent"=>$this->int(),
                    "sort"=>$this->int(),
                    "status"=>$this->varchar(15,true,"'active'"),
                    "lastTry"=>$this->datetime(false),
                    "lastDuration"=>$this->varchar(10),
                    "lastError"=>$this->text(),
                    "lastErrorDate"=>$this->datetime(false),
                    "class"=>$this->varchar(),
                ],
            ],
        ];
    }
}