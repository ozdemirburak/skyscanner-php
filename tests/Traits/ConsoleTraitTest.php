<?php

namespace OzdemirBurak\SkyScanner\Tests\Traits;

use OzdemirBurak\SkyScanner\Traits\ConsoleTrait;
use PHPUnit\Framework\TestCase;

class ConsoleTraitTest extends TestCase
{
    use ConsoleTrait;

    /**
     * @group console-strings
     */
    public function testMessage()
    {
        $message = 'This is an ordinary message';
        $this->printMessage($message);
        $this->expectOutputString($this->getMessageWithColor($message, $this->commentColor) . "\n");
    }

    /**
     * @group console-strings
     */
    public function testSuccessMessage()
    {
        $message = 'This is a success message';
        $this->printSuccessMessage($message);
        $this->expectOutputString($this->getMessageWithColor(implode(' ', ['Success:', $message]), $this->infoColor) . "\n");
    }

    /**
     * @group console-strings
     */
    public function testErrorMessage()
    {
        $message = 'This is an error message';
        $this->printErrorMessage($message);
        $this->expectOutputString($this->getMessageWithColor(implode(' ', ['Failed:', $message]), $this->errorColor) . "\n");
    }

    /**
     * @group console-strings
     */
    public function testInvalidMessageType()
    {
        $message = 'This is a message';
        $this->printMessage($message, 'invalid');
        $this->expectOutputString($this->getMessageWithColor($message, $this->defaultColor) . "\n");
    }
}
