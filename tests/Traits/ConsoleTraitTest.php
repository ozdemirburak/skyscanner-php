<?php

namespace OzdemirBurak\SkyScanner\Tests\Traits;

use OzdemirBurak\SkyScanner\Traits\ConsoleTrait;

class ConsoleTraitTest extends \PHPUnit_Framework_TestCase
{
    use ConsoleTrait;

    /**
     * @group console-strings
     */
    public function testMessage()
    {
        $message = 'This is an ordinary message';
        $expected = $this->getMessageWithColor($message, $this->getMessageBackgroundColor('comment'));
        $this->printMessage($message);
        $this->expectOutputString($expected);
    }

    /**
     * @group console-strings
     */
    public function testSuccessMessage()
    {
        $message = 'This is a success message';
        $expected = $this->getMessageWithColor(join(' ', ['Success:', $message]), $this->getMessageBackgroundColor('info'));
        $this->printSuccessMessage($message);
        $this->expectOutputString($expected);
    }

    /**
     * @group console-strings
     */
    public function testErrorMessage()
    {
        $message = 'This is an error message';
        $expected = $this->getMessageWithColor(join(' ', ['Failed:', $message]), $this->getMessageBackgroundColor('error'));
        $this->printErrorMessage($message);
        $this->expectOutputString($expected);
    }

    /**
     * @group console-strings
     */
    public function testInvalidMessageType()
    {
        $message = 'This is a message';
        $expected = $this->getMessageWithColor($message, $this->getMessageBackgroundColor('invalid'));
        $this->printMessage($message, 'invalid');
        $this->expectOutputString($expected);
    }
}
