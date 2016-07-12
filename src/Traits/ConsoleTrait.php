<?php

namespace OzdemirBurak\SkyScanner\Traits;

use Carbon\Carbon;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

trait ConsoleTrait
{
    /**
     * Print error message
     *
     * @param $joinString
     */
    protected function printSuccessMessage($joinString)
    {
        $this->printInfoMessage(join(': ', ['Success', $joinString]), 'info');
    }

    /**
     * Print error message
     *
     * @param $joinString
     */
    protected function printErrorMessage($joinString)
    {
        $this->printInfoMessage(join(': ', ['Failed', $joinString]), 'error');
    }

    /**
     * Pretty print information messages to console, don't print if it's a phpunit test
     *
     * @param string $string
     * @param string $type
     */
    protected function printInfoMessage($string, $type = 'comment')
    {
        if (strpos($_SERVER['argv'][0], 'phpunit') === false) {
            $output = new ConsoleOutput();
            $output->setFormatter(new OutputFormatter(true));
            $output->writeln(
                "<$type>" .
                $string . " => " .
                Carbon::now()->toDateTimeString() .
                "</$type>"
            );
        }
    }
}
