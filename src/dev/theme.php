<?php


namespace Joonika\dev;


class theme extends baseCommand
{
    public static function commandsList()
    {
        return [
            "theme:update" => [
                "title" => "Install themes assets",
                "arguments" => ["theme"],
            ],
        ];
    }

    public function update()
    {
        $name = $this->checkInputArguments('theme');
        $this->ask("enter name of theme", $name, true);
        if ($name != '') {
            $this->io->title("install theme : for $name: ");
            \Joonika\FS::copyDirectories(__DIR__ . '/../../../../../themes/' . $name . '/assets', 'public/themes/' . $name . '/assets');
//            \Joonika\FS::copyDirectories(__DIR__ . '/../../../../../vendor/joonika/joonika/src/assets', 'public/assets');
            $this->writeOutPut("all files installed");
        } else {
            $this->writeOutPut("theme not selected-> php dev theme:update {name}");
        }
    }
}