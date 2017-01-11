<?php
namespace Dala00\Upload\File;

class DefaultFileSystem implements FileSystemInterface
{

    /**
     * Call php copy function.
     *
     * @param string $source The path copied from
     * @param string $dest The path copy to
     * @return bool
     */
    public function copy($source, $dest)
    {
        return copy($source, $dest);
    }

    /**
     * Call php file_exists.
     *
     * @param string $path Path for check
     * @return bool
     */
    public function fileExists($path)
    {
        return file_exists($path);
    }

    /**
     * Call php mkdir and chmod
     *
     * @param string $path Path for make
     * @param int $mode directory initial permission
     * @param bool $recursive if create directories recursively
     * @return bool
     */
    public function mkdir($path, $mode = 0777, $recursive = false)
    {
        if (mkdir($path, $mode, $recursive)) {
            chmod($path, 0777);

            return true;
        }

        return false;
    }

    /**
     * Call php rename
     *
     * @param string $oldname old file name
     * @param string $newname new file name
     * @return bool
     */
    public function rename($oldname, $newname)
    {
        return rename($oldname, $newname);
    }

    /**
     * Call php chmod
     *
     * @param string $path change path
     * @param string $mode change mode
     * @return bool
     */
    public function chmod($path, $mode)
    {
        return chmod($path, $mode);
    }
}
