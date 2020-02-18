<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\Registry;

class FileHandler
{
    /**
     * Reads the whole content of the file into a string.
     *
     * @param $filename string - The file to be read
     * @param $trim boolean - Whether or not to trim whitespaces at the beginning and end
     * @return string
     */
    public function read($filename, $trim = true)
    {
        if ($trim)
            return \trim(\file_get_contents($filename));

        return \file_get_contents($filename);
    }

    /**
     * Creates the file and writes the content into it.
     *
     * @param $filename string
     * @param $content string
     * @return boolean
     */
    public function write($filename, $content)
    {
        if (Registry::get('FileSystemManager')->fileExists($filename))
            Registry::get('FileSystemManager')->removeFile($filename);

        Registry::get('FileSystemManager')->createFile($filename);
        return $this->appendToFile($filename, $content);
    }
    
    /**
     * Appends the content to the end of the file.
     *
     * @param $filename string -> The name of the file with absolute path
     * @param $content string -> The content to be appended
     * @param $newline boolean -> If true will append the content in a new line
     * @return boolean
     */
    public function appendToFile($filename, $content, $newline = false)
    {
        if (\is_array($content))
            $content = \implode('', $content);

        $vars = Registry::get('MemoryManager')->create(array(
            'result' => false,
            'resource' => \fopen($filename, 'a'),
            'content' => ($newline) ? PHP_EOL . $content : $content,
        ));

        if ($vars->get('resource')->getValue() !== false) {
            $vars->set(
                'result',
                \fwrite(
                    $vars->get('resource')->getValue(),
                    $vars->get('content')->getValue()
                )
            );
        }

        if ($vars->get('result')->getValue() !== false) {
            $vars->set(
                'result', 
                \fclose($vars->get('resource')->getValue())    
            );
        }

        if ($vars->get('result')->getValue() === false) {
            Registry::get('MemoryManager')->destroy($vars);
            unset($vars);
            return false;
        }

        Registry::get('MemoryManager')->destroy($vars);
        unset($vars);
        return true;
    }
}
