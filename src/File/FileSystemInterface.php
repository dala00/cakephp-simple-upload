<?php
namespace Dala00\Upload\File;

interface FileSystemInterface
{

    /**
     * Call php copy function.
     *
     * @param string $source The path copied from
     * @param string $dest The path copy to
     * @return boolean
     */
    public function copy($source, $dest);

    /**
     * Call php file_exists.
     *
     * @param string $path Path for check
     * @return boolean
     */
    public function fileExists($path);

    /**
     * Call php mkdir and chmod
     *
     * @param string $path Path for make
     * @return boolean
     */
    public function mkdir($path, $mode = 0777, $recursive = false);

    /**
     * Call php rename
     *
     * @param string $oldname
     * @param string $newname
     * @return boolean
     */
    public function rename($oldname, $newname);

    /**
     * Call php chmod
     *
     * @param string $path
     * @param string $mode
     * @return boolean
     */
    public function chmod($path, $mode);
}
