<?php

namespace OzdemirBurak\SkyScanner\Traits;

trait ImageTrait
{
    use ConsoleTrait;

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
        $image = str_replace(" ", "%20", $imagePath);
        if (strpos($image, "/") !== false) {
            $split = explode("/", $image);
            $fileName = str_replace("%20", "-", end($split));
            if (!empty($fileName)) {
                $savePath = preg_replace('~/+~', '/', join("/", [$saveDirPath, $fileName]));
                if (!file_exists($savePath)) {
                    try {
                        $this->makeDirRecursive($saveDirPath);
                        copy($image, $savePath);
                        return $savePath;
                    } catch (\Exception $e) {
                        $this->printErrorMessage("Failed to download image, located at " . $imagePath);
                    }
                } else {
                    return $savePath;
                }
            }
        }
        return "";
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
