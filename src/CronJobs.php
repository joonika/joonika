<?php


namespace Joonika;


class CronJobs
{
    public $toDoList = [];

    public function init()
    {
    }

    public static function setCronFunction($moduleName, $functionName = '', $cronTab = '* * * * *', $class = null)
    {
        $database = Database::connect();
        $conditions = ['functionName' => $functionName];
        if ($class) {
            $conditions = [
                "AND" => [
                    'functionName' => $functionName,
                    'class' => $class,
                ]
            ];
        }
        $getFunction = $database->get('cronjob_functions', "*", $conditions);

        if (!$getFunction) {
            $data = ['moduleName' => $moduleName, 'functionName' => $functionName, 'cronTab' => $cronTab];
            if ($class) {
                $data['class'] = $class;
            }
            $database->insert('cronjob_functions', $data);
        } else {
            $database->update('cronjob_functions', ['cronTab' => $cronTab], ['id' => $getFunction['id']]);
        }
    }


    public function run()
    {
        if (checkArraySize($this->toDoList)) {
            foreach ($this->toDoList as $function) {
                if (method_exists($this, $function)) {
                    $this->$function();
                }
            }
        }

    }
}