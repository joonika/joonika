<?php


namespace Joonika\dev;


use Joonika\Errors;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class jobs extends baseCommand
{
    private static $funcArray = [];

    public function __construct(AppCommand $app, $command = null)
    {
        parent::__construct($app, $command, true, true);
    }

    public static function commandsList()
    {
        return [
            "jobs:runAll" => [
                "title" => "Run all Cron jobs",
                "arguments" => ["siteDomain"],
            ],
        ];
    }

    public function runAll()
    {
        $database = $this->database;

        boom('runCronJobs');

        if (checkArraySize(listModules())) {
            foreach (listModules() as $module) {
                $class = "Modules\\" . $module . "\cronjobs\CronJobs";
                if (class_exists($class)) {
                    $cronJob = new $class();
                    $cronJob->init();
                }
            }
            $jobs = $database->select('cronjob_functions', '*', ['status' => 'active']);
            self::setCronToArray($jobs);
            self::cronRun();
        }
    }


    /*run cron job class methods*/
    public static function cronRun()
    {
        self::runCronArray();
    }
    /*insert all function to funcArray variable*/
//    public static function setCronToArray($jobs, $moduleName, $functionName = '', $cronTab = '* * * * *')
    public static function setCronToArray($jobs)
    {
        if (checkArraySize($jobs)) {
            foreach ($jobs as $job) {
                array_push(self::$funcArray, [
                    "moduleName" => $job['moduleName'],
                    "functionName" => $job['functionName'],
                    "cronTab" => $job['cronTab'],
                    "class" => $job['class'],
                    "id" => $job['id'],
                ]);
            }
        }
    }


    /*get all function from funcArray variable and run them*/
    public static function runCronArray()
    {
        $runs = [];
        $database = self::$Database;
        if (sizeof(self::$funcArray) >= 1) {
            foreach (self::$funcArray as $function) {
                if (isset($function['class']) && $function['class'] != '') {
                    if (class_exists($function['class'])) {
                        if (method_exists($function['class'], $function['functionName'])) {
                            // check cron tab
                            if (self::parse_crontab($function['cronTab'])) {
                                $runs[] = $function;
                            }

                        } else {
                            $database->update('cronjob_functions', ['status' => "remove"], [
                                "id" => $function['id']
                            ]);
                        }
                    } else {
                        $database->update('cronjob_functions', ['status' => "remove"], [
                            "class" => $function['class']
                        ]);
                    }
                } else {
                    if (function_exists($function['functionName'])) {
                        // check cron tab
                        if (self::parse_crontab($function['cronTab'])) {
                            $runs[] = $function;
                        }

                    } else {
                        $database->update('cronjob_functions', ['status' => "remove"], [
                            "id" => $function['id']
                        ]);
                    }
                }
            }
        }

        if (checkArraySize($runs)) {

            usort($runs, array(__CLASS__, 'cronSort'));

            foreach ($runs as $function) {
                try {
                    $start = timeCalcStart();
                    $st = self::cronTable($function);
                    echo $function['functionName'] . '-' . $function['class'] . '-' . $st . '<br/>';
                    $finish = timeCalcEnd();
                    $total_time = timeCalc($start, $finish);
                    if ($st) {
                        $database->update("cronjob_functions", [
                            "lastTry" => date("Y/m/d H:i:s"),
                            "lastDuration" => $total_time
                        ], [
                            "id" => $function['id']
                        ]);
                    }
                } catch (\Exception $exception) {
                    $database->update("cronjob_functions", [
                        "lastError" => Errors::exceptionString($exception),
                        "lastErrorDate" => date("Y-m-d H:i:s")
                    ], [
                        "id" => $function['id']
                    ]);
                }
            }
        }

    }


    //insert functions into database
    public static function setCronFunction($moduleName, $functionName = '', $cronTab = '* * * * *')
    {
        $database = self::$Database;
        $getFunction = $database->get('cronjob_functions', "*", ['functionName' => $functionName]);
        if (!$getFunction) {
            $database->insert('cronjob_functions', ['moduleName' => $moduleName, 'functionName' => $functionName, 'cronTab' => $cronTab]);
        } else {
            $database->update('cronjob_functions', ['cronTab' => $cronTab], ['id' => $getFunction['id']]);
        }
    }

    //cron job table
    public static function cronTable($function)
    {

        //finally run below function :)
        if (isset($function['class']) && $function['class'] != '') {
            $class = $function['class'];
            $method = $function['functionName'];
            $class = new $class();
            return $class->$method();
        } else {
            return call_user_func($function['functionName']);
        }

    }

    // Parse CRON frequency
    public static function parse_crontab($crontab)
    {
        // Get current minute, hour, day, month, weekday
        $time = explode(' ', date('i G j n w'));
        // Split crontab by space
        $crontab = explode(' ', $crontab);
        // Foreach part of crontab
        foreach ($crontab as $k => &$v) {
            // Remove leading zeros to prevent octal comparison, but not if number is already 1 digit
            $time[$k] = preg_replace('/^0+(?=\d)/', '', $time[$k]);
            // 5,10,15 each treated as seperate parts
            $v = explode(',', $v);
            // Foreach part we now have
            foreach ($v as &$v1) {
                // Do preg_replace with regular expression to create evaluations from crontab
                $v1 = preg_replace(
                // Regex
                    array(
                        // *
                        '/^\*$/',
                        // 5
                        '/^\d+$/',
                        // 5-10
                        '/^(\d+)\-(\d+)$/',
                        // */5
                        '/^\*\/(\d+)$/'
                    ),
                    // Evaluations
                    // trim leading 0 to prevent octal comparison
                    array(
                        // * is always true
                        'true',
                        // Check if it is currently that time,
                        $time[$k] . '===\0',
                        // Find if more than or equal lowest and lower or equal than highest
                        '(\1<=' . $time[$k] . ' and ' . $time[$k] . '<=\2)',
                        // Use modulus to find if true
                        $time[$k] . '%\1===0'
                    ),
                    // Subject we are working with
                    $v1
                );
            }
            // Join 5,10,15 with `or` conditional
            $v = '(' . implode(' or ', $v) . ')';
        }
        // Require each part is true with `and` conditional
        $crontab = implode(' and ', $crontab);
        // Evaluate total condition to find if true
        return eval('return ' . $crontab . ';');
    }

    public static function minute($function)
    {   //do job every 1 minute
        self::cronTable('*/1 * * * *', $function);
    }

    public static function hour($function)
    {
        //do job every 1 hour
        self::cronTable('* */1 * * *', $function);

    }

    public static function day($function)
    {
        //do job every 1 day
        self::cronTable('* * */1 * *', $function);

    }

    public static function dayInWeek($function)
    {
        //do job every 1 day in week
        self::cronTable('* * * */1 *', $function);

    }

    public static function month($function)
    {
        //do job every 1 month
        self::cronTable('* * * * */1', $function);

    }

    public static function cronSort($a, $b)
    {
        $a = array_reverse(explode(" ", $a["cronTab"]));
        $b = array_reverse(explode(" ", $b["cronTab"]));

        $f = false;
        foreach ($a as $k => $i) {

            if ($i != $b[$k]) $f = true;
            if ($f) break;
        }

        $a = implode("", $a);
        $b = implode("", $b);

        return strcmp($a, $b);
    }
}
