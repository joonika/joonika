<?php

namespace Joonika\dev;

use Joonika\FS;
use Joonika\Route;
use Joonika\Traits\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joonika\ManageTables;
use function Matrix\trace;

class AppCommand extends Command
{
    use Database;

    public $commandName = 'app:install';
    public $commandDescription = "install joonika";
    private $commandLists = null;
    public $args = [];
    public $input;
    public $output;
    public $io;


    function __construct(array $commandLists, string $name = null)
    {
        $this->commandLists = $commandLists;
        $Route = Route::ROUTE(__DIR__ . '/../../../../../', 'dev');
        $this->database = $Route->database;
        $this->databaseInfo = $Route->database;
        self::$DatabaseInfo = $Route->database;
        parent::__construct($name);
    }

    public static function commandsList()
    {
//        return [
//            "public:update" => [
//                "title" => "update public folder",
//            ]
//        ];
    }

    public function configure()
    {
        $input = new \Symfony\Component\Console\Input\ArgvInput();
        $command = $input->getFirstArgument();
        $commandFounded = false;
        $arrayCommands = $this->commandLists;
        $output = new ConsoleOutput();
        if ($command) {
            foreach ($arrayCommands as $scommand => $value) {
                if (checkArraySize($value)) {
                    if (isset($value[$command])) {
                        $commandFounded = true;
                        $arrayCommandK = $command;
                        $arrayCommandV = $arrayCommands[$scommand][$command];
                        if (isset($arrayCommandV['arguments'])) {
                            $m = $this->setName($arrayCommandK)->setDescription($arrayCommandV['title']);
                            if (sizeof($arrayCommandV['arguments']) > 0) {
                                foreach ($arrayCommandV['arguments'] as $opt) {
                                    $m->addArgument(
                                        $opt,
                                        InputArgument::OPTIONAL
                                    );
                                }
                            }
                            if (isset($arrayCommandV['options'])) {
                                if (sizeof($arrayCommandV['options']) > 0) {
                                    foreach ($arrayCommandV['options'] as $key => $val) {
                                        $m->addOption($key, $key, null, $val['desc']);
                                    }
                                }
                            }
                        } else {
                            $this->setName($arrayCommandK)->setDescription($arrayCommandV['title']);
                        }
                    }
                }
            }
            if (!$commandFounded) {
                $output->write("\n\n <fg=red>******************************</> \n");
                $output->write("\n <fg=red>****  command not found   ****</> \n");
                $output->write("\n <fg=red>******************************</> \n");
                die('');
            }
        } else {
            $commandList = [];
            foreach ($arrayCommands as $command => $value) {
                $under = "";
                for ($i = 0; $i < 100 - strlen($command); $i++) {
                    $under .= "_";
                }
                $output->write("<fg=cyan;options=bold>\n\n\n" . ucfirst($command) . ' ' . $under . "\n \n</>");
                if (checkArraySize($value)) {
                    foreach ($value as $cItem => $cVal) {
                        if ($cItem != "class") {
                            $space = "";
                            for ($i = 0; $i < 50 - strlen($cItem); $i++) {
                                $space .= " ";
                            }
                            $output->write("<options=bold>   $cItem </>" . $space . " | <fg=cyan>" . $cVal['title'] . "</>\n");
                        }
                    }
                }
            }
            $output->write("\n\n                   <fg=white;options=bold> -------------------------------------------------</>\n");
            $output->write("                   <fg=white;options=bold>|   Example for command : php dev module:create   |</>");
            $output->write("\n                   <fg=white;options=bold> -------------------------------------------------</>\n\n\n");

            die();
        }
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $this->output->writeln("Joonika :");
        $text = $this->input->getArguments()['command'];
        $this->io = new \Joonika\SymfonyStyle($this->input, $this->output);
        $command = explode(':', $text);
        if (sizeof($command) == 2) {
            foreach ($this->commandLists as $scommand => $value) {
                if (checkArraySize($value)) {
                    $keys = array_keys($value);
                    if (in_array($text, $keys)) {
                        $nameSpace = $value['class'];
                        if (__CLASS__ == $nameSpace) {
                            $textMethodCallable = str_replace(':', '_', $text);
                            try {
                                if (method_exists($this, $textMethodCallable)) {
                                    $outputReturn = call_user_func(array($this, $textMethodCallable));
                                } else {
                                    $outputReturn = "command '$text' is not defined";
                                }
                                $this->output->writeln($outputReturn);
                                $this->output->writeln("Aid finish");
                            } catch (Exception $e) {
                                $this->io->error($e->getMessage());
                            }
                        } else if (class_exists($nameSpace)) {
                            $cmd = new $nameSpace($this, $command[1]);
                            $method = $command[1];
                            if (method_exists($cmd, $method)) {
                                $cmd->$method();
                            } else {
                                $textMethodCallable = str_replace(':', '_', $text);
                                try {
                                    if (method_exists($this, $textMethodCallable)) {
//                $outputReturn = call_user_func(array($this, $textMethodCallable), $this->input, $this->output, $io);
                                        $outputReturn = call_user_func(array($this, $textMethodCallable));
                                    } else {
                                        $outputReturn = "command '$text' is not defined";
                                    }
                                    $this->output->writeln($outputReturn);
                                    $this->output->writeln("Aid finish");
                                } catch (Exception $e) {
                                    $this->io->error($e->getMessage());
                                }
                            }
                        } else {
                            $textMethodCallable = str_replace(':', '_', $text);
                            try {
                                if (method_exists($this, $textMethodCallable)) {
//                $outputReturn = call_user_func(array($this, $textMethodCallable), $this->input, $this->output, $io);
                                    $outputReturn = call_user_func(array($this, $textMethodCallable));
                                } else {
                                    $outputReturn = "command '$text' is not defined";
                                }
                                $this->output->writeln($outputReturn);
                                $this->output->writeln("Aid finish");
                            } catch (Exception $e) {
                                $this->io->error($e->getMessage());
                            }
                        }
                    }
                }
            }
        }
        return 0;
    }

    public function checkInputArguments($name)
    {
        return strlen($this->input->getArguments()[$name]) > 0 ? $this->input->getArguments()[$name] : null;
    }

    public function checkOptions($name)
    {
        return isset($this->input->getOptions()[$name]) ? $this->input->getOptions()[$name] : false;
    }

}