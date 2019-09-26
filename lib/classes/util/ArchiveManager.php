<?php

namespace BeAmado\OjsMigrator\Util;

class ArchiveManager
{
    /**
     * Discriminates the parameters passed to the tar function.
     *
     * @param string $flags
     * @return array
     */
    protected function getTarParams($flags)
    {
        $params = array();

        for ($i = 0; $i < \strlen($flags); $i++) {
            $params[] = \substr($flags, $i, 1);
        }

        return $params;
    }

    /**
     * Creates a tarball of the specified directory.
     *
     * @param string $filename - The name the tarball will have
     * @param string $directory - The directory to be compressed into a file.
     * @return boolean
     */
    protected function createTar($filename, $directory)
    {
        if (!(new FileSystemManager())->dirExists($directory)) {
            return false;
        }

        try {
            (new \PharData($filename))->buildFromDirectory($directory);
        } catch (\Exception $e) {
            if (\is_a($e, \BadMethodCallException::class)) {
                // TODO
                //treat the exception
            } else if (\is_a($e, \PharException::class)) {
                // TODO
                //treat the exception
            }

            return false;
        }

        return true;

    }
    
    /**
     * Extracts contents of the tarball to the specified directory.
     *
     * @param string $filename -> The tar file to extract the contents from
     * @param string $pathTo -> The directory in which to put the extracted 
     * content
     * @param string|array $files -> The only files to be extracted
     * @return boolean
     */
    protected function extractTar($filename, $pathTo, $files = null)
    {
        if (
            !(new FileSystemManager())->fileExists($filename) ||
            !(new FileSystemManager())->dirExists($pathTo)
        ) {
            return false;
        }

        try {
            (new \PharData($filename))->extractTo($pathTo, $files);
        } catch (\Exception $e) {
            var_dump($e);
            if (\is_a($e, \BadMethodCallException::class)) {
                // TODO
                //treat the exception
            } else if (\is_a($e, \PharException::class)) {
                // TODO
                //treat the exception
            }

            return false;
        }

        return true;
    }

    /**
     * Tarball manager method.
     * It can perform the following actions:
     * - Create a tarball -> flag c
     * 
     *
     * @param string $flags
     * @param string $filename
     * @param string $directory
     * @return mixed
     */
    public function tar($flags, $filename, $directory)
    {
        $vars = (new MemoryManager())->create(array(
            'params' => $this->getTarParams($flags),
        ));

        if ($vars->get('params')->get(0)->getValue() === 'c') {
            $vars->set(
                'result', 
                $this->createTar($filename, $directory)
            );
        } else if ($vars->get('params')->get(0)->getValue() === 'x') {
            $vars->set(
                'result',
                $this->extractTar($filename, $directory)
            );
        }

        if ($vars->get('result')->getValue() === true) {
            (new MemoryManager())->destroy($vars);
            unset($vars);

            return true;
        } else if ($vars->get('result')->getValue() === false) {
            (new MemoryManager())->destroy($vars);
            unset($vars);

            return false;
        }

        (new MemoryManager())->destroy($vars);
        unset($vars);
    }
}
