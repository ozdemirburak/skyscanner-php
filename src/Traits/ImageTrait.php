<?php

namespace OzdemirBurak\SkyScanner\Traits;

trait ImageTrait
{
    /**
     * Image save path for agents and carriers, optional
     *
     * @var string
     */
    protected $savePath = '/tmp/images/';

    /**
     * Save image to specified path and return the full path with image name and extension
     *
     * @param        $imagePath
     * @param        $saveDirPath
     * @param string $separator
     *
     * @return string
     */
    public function saveImage($imagePath, $saveDirPath, $separator = '-'): string
    {
        if (strpos($image = str_replace(' ', '%20', $imagePath), DIRECTORY_SEPARATOR) !== false) {
            $filename = str_replace('%20', $separator, pathinfo($image, PATHINFO_BASENAME));
            if (!empty($filename)) {
                $savePath = preg_replace(
                    '~/+~',
                    DIRECTORY_SEPARATOR,
                    implode(DIRECTORY_SEPARATOR, [$saveDirPath, $filename])
                );
                if (!file_exists($savePath)) {
                    try {
                        is_dir($saveDirPath) || mkdir($saveDirPath, 0777, true);
                        copy($image, $savePath);
                        return $savePath;
                    } catch (\Exception $e) {
                        if (strpos($_SERVER['argv'][0], 'phpunit') === false) {
                            echo 'Failed to download image, located at ' . $imagePath . ', Error: ' . $e->getMessage();
                            echo "\n";
                        }
                    }
                } else {
                    return $savePath;
                }
            }
        }
        return '';
    }
}
