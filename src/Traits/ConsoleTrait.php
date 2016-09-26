<?php

namespace OzdemirBurak\SkyScanner\Traits;

trait ConsoleTrait
{
    /**
     * Grey
     *
     * @var int
     */
    protected $commentColor = 8;

    /**
     * DarkBlue
     *
     * @var int
     */
    protected $defaultColor = 18;

    /**
     * Red
     *
     * @var int
     */
    protected $errorColor = 9;

    /**
     * DarkGreen
     *
     * @var int
     */
    protected $infoColor = 22;

    /**
     * Return the color number based on the type
     *
     * @param string $type
     *
     * @return int
     */
    protected function getMessageBackgroundColor($type)
    {
        if (in_array($type, ['info', 'error', 'comment'])) {
            return $this->{$type . 'Color'};
        }
        return $this->defaultColor;
    }

    /**
     * For the terminal colors, check the Xterm 256 color chart
     *
     * @link https://upload.wikimedia.org/wikipedia/en/1/15/Xterm_256color_chart.svg
     *
     * @param string    $string
     * @param int       $background
     * @param int       $foreground
     *
     * @return string
     */
    public function getMessageWithColor($string, $background = 0, $foreground = 255)
    {
        return "\033[38;5;{$foreground}m\033[48;5;{$background}m{$string}\033[0m";
    }

    /**
     * Print error message
     *
     * @param $joinString
     */
    public function printSuccessMessage($joinString)
    {
        $this->printMessage(join(': ', ['Success', $joinString]), 'info');
    }

    /**
     * Print error message
     *
     * @param $joinString
     */
    public function printErrorMessage($joinString)
    {
        $this->printMessage(join(': ', ['Failed', $joinString]), 'error');
    }

    /**
     * Pretty print information messages to console
     *
     * @param string $string
     * @param string $type
     */
    public function printMessage($string, $type = 'comment')
    {
        print $this->getMessageWithColor($string, $this->getMessageBackgroundColor($type));
    }
}
