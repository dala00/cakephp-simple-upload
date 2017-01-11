<?php
namespace Dala00\Upload\File;

/**
 * Dummy file system for test.
 */
class DebugFileSystem implements FileSystemInterface
{

    protected $files = [];

    /**
     * @param array $files Initialize exists files
     */
    function __construct($files = null)
    {
        if ($files) {
            $this->files = $files;
        }
    }

    /**
     * Add files.
     *
     * @param string|array $files
     * @return void
     */
    public function addFiles($files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }
        foreach ($files as $file) {
            if (!in_array($file, $this->files)) {
                $this->files[] = $file;
            }
        }
    }

    /**
     * Get files.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Call php copy function.
     *
     * @param string $source The path copied from
     * @param string $dest The path copy to
     * @return boolean
     */
    public function copy($source, $dest)
    {
        $this->addFiles($dest);

        return true;
    }

    /**
     * Call php file_exists.
     *
     * @param string $path Path for check
     * @return boolean
     */
    public function fileExists($path)
    {
        return in_array($path, $this->files);
    }

    /**
     * Call php mkdir and chmod
     *
     * @param string $path Path for make
     * @return boolean
     */
    public function mkdir($path, $mode = 0777, $recursive = false)
    {
        return true;
    }

    /**
     * Call php rename
     *
     * @param string $oldname
     * @param string $newname
     * @return boolean
     */
    public function rename($oldname, $newname)
    {
        $index = array_search($oldname, $this->files);
        if ($index === false) {
            return false;
        }
        unset($this->files[$index]);
        $this->addFiles($newname);

        return true;
    }

    /**
     * Call php chmod
     *
     * @param string $path
     * @param string $mode
     * @return boolean
     */
    public function chmod($path, $mode)
    {
        return true;
    }
}
