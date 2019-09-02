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

        $list = \scandir($dir);

        return $this->removeDots($list);
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

}
