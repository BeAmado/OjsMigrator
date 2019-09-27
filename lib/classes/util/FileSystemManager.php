<?php

namespace BeAmado\OjsMigrator\Util;

class FileSystemManager
{
    /**
     * Removes all the slashes that might be at the end
     *
     * @param string $str
     * @return string
     */
    protected function removeTrailingSlashes($str)
    {
        if (substr($str, -1) !== '/') {
            return $str;
        }

        return $this->removeTrailingSlashes(substr($str, 0, -1));
    }
    
    /**
     * Removes all the slashes that might be at the beginning
     *
     * @param string $str
     * @return string
     */
    protected function removeBeginningSlashes($str)
    {
        if (substr($str, 0, 1) !== '/') {
            return $str;
        }

        return $this->removeBeginningSlashes(substr($str, 1));
    }

    /**
     * Removes the '.' and '..' from the list.
     *
     * @param array $list
     * @return array
     */
    protected function removeDots($list)
    {
        //removing the dot (.)
        do {
            $indexDot = \array_search('.', $list);

            if ($indexDot !== false) {
                \array_splice($list, $indexDot, 1);
            }
        } while ($indexDot !== false);

        //removing the double dot (..)
        do {
            $indexDdot = \array_search('..', $list);

            if ($indexDdot !== false) {
                \array_splice($list, $indexDdot, 1);
            }
        } while ($indexDdot !== false);

        return $list;
    }

    /**
     * Lists the specified directory's content. Same as the linux 'ls' function.
     *
     * @param string $dir - The fullpath of the directory
     * @return array
     */
    public function listdir($dir = null)
    {
        if ($dir === null) {
            $dir = \getcwd();
        }

        $dir = $this->removeTrailingSlashes($dir);

        $list = \scandir($dir);

        $list = $this->removeDots($list);

        // putting all the items as absolute path
        foreach ($list as $key => $value) {
            $list[$key] = $dir
                . \BeAmado\OjsMigrator\DIR_SEPARATOR
                . $this->removeTrailingSlashes(
                      $this->removeBeginningSlashes($value)
                  );
        }

        unset($dir);
        return $list;
    }

    /**
     * Returns a string which is the location of the parent directory (the same as ..)
     *
     * @param string $dir
     * @return string
     */
    public function parentDir($dir = null)
    {
        if ($dir === null) {
            $dir = \getcwd();
        }

        return \dirname($dir);
    }

    /**
     * Go up the specified amount of levels starting from the specified directory.
     *
     * @param string $dir - The directory from which to go up.
     * @param integer $levels - The amount of levels to go up.
     * @return string
     */
    public function goUp($dir = null, $levels = 1)
    {
        for ($i = 0; $i < $levels; $i++) {
            $dir = $this->parentDir($dir);
        }

        return $dir;
    }

    /**
     * Forms the path with the directory separator used in the program.
     *
     * @param array $parts
     * @return string
     */
    public function formPath($parts)
    {
        return \implode(\BeAmado\OjsMigrator\DIR_SEPARATOR, $parts);
    }

    /**
     * Returns the fullpath including he base direcotory.
     * Something like /home/user/ojsmigrator/path/to/dir/file
     *
     * @param array $parts
     */
    public function formPathFromBaseDir($parts)
    {
        //insert at the beginning fo the array
        if (!\is_array($parts)) {
            $parts = array($parts);
        }

        return $this->formPath(
            \array_merge(
                array(\BeAmado\OjsMigrator\BASE_DIR),
                $parts
            )
        );
    }

    /**
     * Tests if the resource exists and if is of the specified type.
     * This method might be used to test if the following resources exist:
     * - directory -> passing the type as "dir"
     * - file -> passing the type as "file"
     * - link -> passing the type as "link"
     *
     * @param $thing string
     * @param $type string
     * @return boolean
     */
    protected function itExists($thing, $type)
    {
        if (\is_array($thing)) {
            $thing = $this->formPath($thing);
        }

        switch($type) {
            case 'dir':
                return \is_dir($thing);

            case 'file':
                return \is_file($thing);

            case 'link':
                return \is_link($thing);
        }
    }

    /**
     * Tests if adirectory exists
     *
     * @param mixed $dirname - A string or an array
     * @return boolean
     */
    public function dirExists($dir)
    {
        return $this->itExists($dir, 'dir');
    }

    public function fileExists($filename)
    {
        return $this->itExists($filename, 'file');
    }

    /**
     * Creates the specified directory
     *
     * @param mixed $dir
     * @return boolean
     */
    public function createDir($dir)
    {
        if (\is_array($dir)) {
            $dir = $this->formPath($dir);
        }

        return \mkdir($dir, 0755, true);
    }

    /**
     * Removes the specified directory if it is empty
     *
     * @param mixed $dir
     * @return boolean
     */
    public function removeDir($dir)
    {
        if (\is_array($dir)) {
            $dir = $this->formPath($dir);
        }

        return \rmdir($dir);
    }

    /**
     * Creates the specified file
     *
     * @param string $filename
     * @return boolean
     */
    public function createFile($filename)
    {
        return \touch($filename);
    }

    /**
     * Removes the specified file.
     *
     * @param string $filename
     * @return boolean
     */
    public function removeFile($filename)
    {
        return \unlink($filename);
    }

    /**
     * Removes the sspecified files
     *
     * @param array $files
     * @return void
     */
    public function removeFiles($files)
    {
        if (!\is_array($files)) {
            return;
        }

        foreach ($files as $filename) {
            $this->removeFile($filename);
        }

        unset($filename);
        unset($files);
    }

    /**
     * Removes the directory recursively.
     *
     * @param string $dir
     * @return boolean
     */
    public function removeWholeDir($dir)
    {
        $vars = (new MemoryManager())->create(array(
            'dir' => $dir,
            'ls' => $this->listdir($dir),
        ));

        unset($dir);
        
        if (!\is_array($vars->get('ls')->listValues())) {
            (new MemoryManager())->destroy($vars);
            unset($vars);
            return false;
        }

        /** @var $item MyObject */
        $vars->get('ls')->forEachValue(function($item) {
            if (\is_file($item->getValue()) || \is_link($item->getValue())) {
                (new FileSystemManager())->removeFile($item->getValue());
            } else if (\is_dir($item->getValue())) {
                (new FileSystemManager())->removeWholeDir($item->getValue());
            }

            unset($item);
        });

        $vars->set(
            'ls', 
            $this->listdir($vars->get('dir')->getValue())
        );

        if (
            !empty($vars->get('ls')->listValues()) ||
            !$this->removeDir($vars->get('dir')->getValue())
        ) {
            (new MemoryManager())->destroy($vars);
            unset($vars);
            return false;
        }

        (new MemoryManager())->destroy($vars);
        unset($vars);
        return true;
    }

    /**
     * Copies the specifies file to the name and path chosen
     *
     * @param string $originalFilename
     * @param string $newFilename
     * @return boolean
     */
    public function copyFile($originalFilename, $newFilename)
    {
        if (!$this->fileExists($originalFilename)) {
            return false;
        }

        return \copy($originalFilename, $newFilename);
    }
}
