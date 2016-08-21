<?php

namespace OzdemirBurak\SkyScanner\Tests\Traits;

use OzdemirBurak\SkyScanner\Traits\ImageTrait;

class ImageTraitTest extends \PHPUnit_Framework_TestCase
{
    use ImageTrait;

    /**
     * @group invalid-image
     */
    public function testInvalidRemoteImageNotSaved()
    {
        $this->saveImage("http://www.qwertyasdfgzxcv.com/dummy.jpg", "/tmp/images/");
        $this->assertFileNotExists("/tmp/images/dummy.jpg");
    }

    /**
     * @group invalid-image
     */
    public function testImagePathIsReturnedFromInvalidRemoteImage()
    {
        $image = $this->saveImage("http://www.qwertyasdfgzxcv.com/dummy.jpg", "/tmp/images/");
        $this->assertEmpty($image);
    }

    /**
     * @group valid-image
     */
    public function testValidRemoteImageIsSaved()
    {
        $this->saveImage("https://upload.wikimedia.org/wikipedia/commons/a/af/Oludeniz.jpg", "/tmp/images/");
        $this->assertFileExists("/tmp/images/Oludeniz.jpg");
    }

    /**
     * @group valid-image
     */
    public function testImagePathIsReturnedFromValidRemoteImage()
    {
        $image = $this->saveImage("https://upload.wikimedia.org/wikipedia/commons/a/af/Oludeniz.jpg", "/tmp/images/");
        $this->assertEquals("/tmp/images/Oludeniz.jpg", $image);
    }
}
