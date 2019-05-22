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
        if (\in_array($type, ['info', 'error', 'comment'], true)) {
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
    public function getMessageWithColor($string, $background = 0, $foreground = 255): string
    {
        return "\033[38;5;{$foreground}m\033[48;5;{$background}m{$string}\033[0m";
    }

    /**
     * Print error message
     *
     * @param      $joinString
     * @param bool $force
     */
    public function printSuccessMessage($joinString, $force = true): void
    {
        if ($this->isPrintable($force)) {
            $this->printMessage(implode(': ', ['Success', $joinString]), 'info');
        }
    }

    /**
     * Print error message
     *
     * @param      $joinString
     * @param bool $force
     */
    public function printErrorMessage($joinString, $force = true): void
    {
        if ($this->isPrintable($force)) {
            $this->printMessage(implode(': ', ['Failed', $joinString]), 'error');
        }
    }

    /**
     * Pretty print information messages to console
     *
     * @param string $string
     * @param string $type
     * @param bool   $force
     */
    public function printMessage($string, $type = 'comment', $force = true): void
    {
        if ($this->isPrintable($force)) {
            echo $this->getMessageWithColor($string, $this->getMessageBackgroundColor($type));
            echo "\n";
        }
    }

    /**
     * @param bool $force
     *
     * @return bool
     */
    private function isPrintable($force = true): bool
    {
        return !($force === false && strpos(($_SERVER['argv'][0]) ?? null, 'phpunit') !== false);
    }
}
