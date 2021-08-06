<?php


namespace Joonika\dev;


use Joonika\FS;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;


class commandProvider
{

    private $commandsList = [];
    private $commandsFiles = [];
    private $foundedModules = [];
    private $foundedThemes = [];
    private $application = null;


    private function getConsoleFilesNameSpace($foundedFiles, $path, $type)
    {
        foreach ($foundedFiles as $file) {
            if (FS::isDir($path . DS() . $file . DS() . 'console')) {
                $consoleFiles = glob($path . DS() . $file . DS() . 'console' . DS() . '*.php');
                if (checkArraySize($consoleFiles)) {
                    foreach ($consoleFiles as $cfile) {
                        $this->commandsFiles[] = [
                            'class' => $type . "\\" . $file . "\console\\" . pathinfo($cfile)['filename'],
                            'file' => pathinfo($cfile)['filename'],
                        ];
                    }
                }
            }
        }
    }

    public function __construct()
    {

        $rootPath = __DIR__ . DS() . ".." . DS() . ".." . DS() . ".." . DS() . ".." . DS() . ".." . DS();
        $modulePath = $rootPath . "modules";
        $themesPath = $rootPath . "themes";

        if (FS::isDir($modulePath)) {
            $this->foundedModules = getSubFolders($modulePath);
        }
        if (FS::isDir($themesPath)) {
            $this->foundedThemes = getSubFolders($themesPath);
        }


        $consoleFilesInJoonika = glob(__DIR__ . DS() . '*.php');
        if (checkArraySize($consoleFilesInJoonika)) {
            foreach ($consoleFilesInJoonika as $cfile) {
                $this->commandsFiles[] = [
                    'class' => "Joonika\dev\\" . pathinfo($cfile)['filename'],
                    'file' => pathinfo($cfile)['filename'],
                ];
            }
        }

        if (checkArraySize($this->foundedModules)) {
            $this->getConsoleFilesNameSpace($this->foundedModules, $modulePath, 'Modules');
        }
        if (checkArraySize($this->foundedModules)) {
            $this->getConsoleFilesNameSpace($this->foundedThemes, $themesPath, 'Themes');
        }

        foreach ($this->commandsFiles as $file) {
            if (class_exists($file['class']) && method_exists($file['class'], 'commandsList')) {
                $cl = $file['class']::commandsList();
                $this->commandsList[$file['file']] = $cl;
                $this->commandsList[$file['file']]['class'] = $file['class'];
            }
        }

        $this->application = new Application();
        $input = new ArgvInput;
        $this->application->add(new AppCommand($this->commandsList));

        try {
            $this->application->run();
        } catch (Exception $e) {

        }
    }
}
