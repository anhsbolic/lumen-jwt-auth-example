<?php

namespace App\Utils;

use Symfony\Component\Console\Output\ConsoleOutput;

class CliLog
{
    /**
     * Write Log on CLI / Shell / Terminal.
     * 
     * DEBUG
     *
     * @return void
     */
    public function info($msg)
    {
        if (env('APP_DEBUG')) {
            $output = new ConsoleOutput();
            $output->writeln("<info>".$msg."</info>");
        } 
    }
}
