<?php

namespace Joonika;

class SymfonyStyle extends \Symfony\Component\Console\Style\SymfonyStyle
{
    public function error($message)
    {
        $this->block($message, 'ERROR', 'fg=black;bg=red;', ' ', true);
    }

}