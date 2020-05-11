<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\Registry;

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

        if (!$this->dirExists($dir))
            return;

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
     * Tests if a directory exists
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
        if (!$this->dirExists(\dirname($filename)))
            $this->createDir(\dirname($filename));

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
        if (!$this->dirExists($dir))
            return;

        $vars = Registry::get('MemoryManager')->create(array(
            'dir' => $dir,
            'ls' => $this->listdir($dir),
        ));

        unset($dir);
        
        if (!\is_array($vars->get('ls')->listValues())) {
            Registry::get('MemoryManager')->destroy($vars);
            unset($vars);
            return false;
        }

        /** @var $item MyObject */
        $vars->get('ls')->forEachValue(function($item) {
            if (\is_file($item->getValue()) || \is_link($item->getValue())) {
                Registry::get('FileSystemManager')->removeFile(
                    $item->getValue()
                );
            } else if (\is_dir($item->getValue())) {
                Registry::get('FileSystemManager')->removeWholeDir(
                    $item->getValue()
                );
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
            Registry::get('MemoryManager')->destroy($vars);
            unset($vars);
            return false;
        }

        Registry::get('MemoryManager')->destroy($vars);
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

        if (!$this->dirExists($this->parentDir($newFilename)))
            $this->createDir($this->parentDir($newFilename));

        return \copy($originalFilename, $newFilename);
    }

    /**
     * Removes the files and directories specified.
     *
     * @param array $items
     * @return void
     */
    protected function removeItems($items)
    {
        if (!\is_array($items))
            return;

        foreach ($items as $item) {
            if (\is_file($item))
                $this->removeFile($item);

            elseif (\is_dir($item))
                $this->removeWholeDir($item);
        }
    }

    /**
     * Move the contents of a directory into another.
     *
     * @param string $old
     * @param string $new
     * @return boolean
     */
    protected function moveContent($old, $new)
    {
        if (
            !$this->fileExists($old) &&
            !$this->dirExists($old)
        )
            return false;

        if (
            \is_file($old) || 
            (\is_dir($old) && !$this->dirExists($new))
        )
            return \rename($old, $new);

        $movedItems = array();
        foreach (\array_map('basename', $this->listdir($old)) as $name) {
            if ($this->moveContent(
                $this->formPath(array($old, $name)),
                $this->formPath(array($new, $name))
            ))
                $movedItems[] = $this->formPath(array($new, $name));
            else {
                $this->removeItems($movedItems);
                return false;
            }
        }

        Registry::get('MemoryManager')->destroy($movedItems);
        unset($movedItems);

        $this->removeWholeDir($old);

        return true;
    }

    /**
     * Move the file or directory.
     *
     * @param string $old
     * @param string $new
     * @param boolean $merge
     * @return boolean
     */
    public function move($old, $new, $merge = true)
    {
        if (
            !$this->fileExists($old) &&
            !$this->dirExists($old)
        )
            return false;

        if (\is_dir($old) && $this->dirExists($new))
            return $this->moveContent($old, $new);

        if (!$this->dirExists($this->parentDir($new)))
            $this->createDir($this->parentDir($new));

        return \rename($old, $new);
    }

    protected function copyContent($old, $new, $overwrite = false, $depth = 0)
    {
        if (
            $depth > 1 ||
            (!$overwrite && $this->dirExists($old) && $this->fileExists($new))
        )
            return false;

        if ($this->dirExists($old) && $this->fileExists($new))
            $this->removeFile($new);

        if ($this->dirExists($old) && !$this->dirExists($new))
            return $this->copyDir($old, $new, $overwrite, $depth + 1);

        foreach ($this->listdir($old) as $item) {
            if (!\is_file($item) && !\is_dir($item))
                continue;

            $this->{\is_dir($item) ? 'copyDir' : 'copyFile'}(
                $item,
                $this->formPath(array($new, \basename($item)))
            );
        }
    }

    /**
     * Copies the directory to the new location.
     *
     * @param string $old
     * @param string $new
     * @param boolean $overwrite
     * @return boolean
     */
    public function copyDir($old, $new, $overwrite = false, $depth = 0)
    {
        if ($depth > 1)
            return false;

        if (!$this->dirExists($old))
            return false;
        
        if (\is_dir($old) && $this->dirExists($new))
            return $this->copyContent($old, $new, $overwrite, $depth);

        $res = $this->createDir($new);

        foreach ($this->listdir($old) as $item) {
            if ($res == false)
                break;

            if (!\is_dir($item) && !\is_file($item))
                continue;

            $res = $res && $this->{\is_dir($item) ? 'copyDir' : 'copyFile'}(
                $item, 
                $this->formPath(array($new, \basename($item)))
            );
        }

        return $res;
    }
}
