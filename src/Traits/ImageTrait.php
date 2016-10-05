<?php

namespace OzdemirBurak\SkyScanner\Traits;

trait ImageTrait
{
    /**
     * Save image to specified path and return the full path with image name and extension
     *
     * @param $imagePath
     * @param $saveDirPath
     *
     * @return string
     */
    public function saveImage($imagePath, $saveDirPath)
    {
        $image = str_replace(' ', '%20', $imagePath);
        if (strpos($image, DIRECTORY_SEPARATOR) !== false) {
            $filename = str_replace('%20', '-', pathinfo($image, PATHINFO_BASENAME));
            if (!empty($filename)) {
                $savePath = preg_replace('~/+~', DIRECTORY_SEPARATOR, join(DIRECTORY_SEPARATOR, [$saveDirPath, $filename]));
                if (!file_exists($savePath)) {
                    try {
                        $this->makeDirRecursive($saveDirPath);
                        copy($image, $savePath);
                        return $savePath;
                    } catch (\Exception $e) {
                        echo 'Failed to download image, located at ' . $imagePath . ', Error: ' . $e->getMessage();
                    }
                } else {
                    return $savePath;
                }
            }
        }
        return '';
    }

    /**
     * Create directory recursively if not exists
     *
     * @param     $path
     * @param int $mode
     *
     * @return bool
     */
    protected function makeDirRecursive($path, $mode = 0777)
    {
        return is_dir($path) || mkdir($path, $mode, true);
    }
}
