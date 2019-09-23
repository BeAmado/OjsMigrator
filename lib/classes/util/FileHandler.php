<?php

namespace BeAmado\OjsMigrator\Util;

class FileHandler
{
    public function read($filename)
    {
        return \file_get_contents($filename);
    }

    public function write($filename, $content)
    {
        if ((new FileSystemManager())->fileExists($filename)) {
            (new FileSystemManager())->removeFile($filename);
        }

        (new FileSystemManager())->createFile($filename);
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
        $vars = (new MemoryManager())->create(array(
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
            (new MemoryManager())->destroy($vars);
            unset($vars);
            return false;
        }

        (new MemoryManager())->destroy($vars);
        unset($vars);
        return true;
    }
}
