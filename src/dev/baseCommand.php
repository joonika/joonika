<?php


namespace Joonika\dev;


use Joonika\Database;
use Joonika\Route;
use mysql_xdevapi\Exception;

abstract class baseCommand
{
    use \Joonika\Traits\Database;

    public $command;
    public $cmd;
    public $io;
    public $input;
    public $output;
    public $configureFile = null;


    public function __construct(AppCommand $app, $command = null, $connectToDataBase = false, $configFileIsRequired = false)
    {
        $this->command = $app;
        $this->io = $app->io;
        $this->input = $app->input;
        $this->output = $app->output;

        if ($configFileIsRequired) {
            $this->configFileIsRequired();
        }
        if (!is_null($this->configureFile)) {
            if (!empty($this->configureFile['type'])) {
                if (!empty($this->configureFile['database'])) {
                    $app->database = $app->databaseInfo = $this->configureFile['database'];
                }
            }
        }

        $this->databaseInfo = $app->databaseInfo;
        self::$DatabaseInfo = $app->databaseInfo;

        if ($connectToDataBase) {
            $this->database = Database::connect($app->database);
            self::$Database = Database::connect($app->database);
        }
    }

    public function configFileIsRequired()
    {
        $requiredYamlFile = JK_SITE_PATH() . 'config/websites/dev.yaml';
        if (file_exists($requiredYamlFile)) {
            try {
                $this->configureFile = yaml_parse_file($requiredYamlFile);
            } catch (\Exception $exception) {
                throw new \Exception("invalid yaml file");
            }
        } else {
            $this->writeError('dev not configured');
            die("");
        }
    }

    public function checkModuleIsValid($module)
    {
        if (!in_array($module,Route::$instance->modules)) {
            $this->writeError("The module " . $module . " is not exist !");
            die('');
        }
    }

    //input and output
    public function writeOutPut($msg)
    {
        $this->output->writeln($msg);
    }

    //command
    public function checkInputArguments($input)
    {
        return $this->command->checkInputArguments($input);
    }

    public function checkOptions($input='')
    {
        return $this->command->checkOptions($input);
    }

    //io
    public function ask($ask, &$variable, $required = false)
    {
        if ($required) {
            while ($variable == '') {
                $variable = $this->io->ask($ask);
            }
        } else {
            $variable = $this->io->ask($ask);
        }
        return $variable;
    }

    public function writeInfo($str)
    {
        return $this->output->writeln("<fg=cyan>$str</>");
    }

    public function writeError($str)
    {
        return $this->output->writeln("<fg=red>$str</>");
    }

    public function writeSuccess($str)
    {
        return $this->output->writeln("<fg=green>$str</>");
    }
}