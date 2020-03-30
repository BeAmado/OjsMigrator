<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\Registry;

class ArchiveManager
{
    /**
     * Gets the name of the file without the .tar or .gz extensions
     *
     * @param string $name
     * @return string
     */
    protected function getFilename($name)
    {
        if (\substr($name, -3) === '.gz') {
            $name = \substr($name, 0, -3);
        }

        if (\substr($name, -4) === '.tar') {
            $name = \substr($name, 0, -4);
        }

        return $name;
    }

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

    protected function hasParamCreate($flags)
    {
        if (\strlen($flags) < 1) {
            return false;
        }

        return \strtolower($this->getTarParams($flags)[0]) === 'c';
    }

    protected function hasParamExtract($flags)
    {
        if (\strlen($flags) < 1) {
            return false;
        }

        return \strtolower($this->getTarParams($flags)[0]) === 'x';
    }

    protected function hasParamZip($flags)
    {
        if (\strlen($flags) < 2) {
            return false;
        }

        return \strtolower($this->getTarParams($flags)[1]) === 'z';
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
        if (!Registry::get('FileSystemManager')->dirExists($directory)) {
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

            //rethrow
            throw $e;

            return false;
        }

        return true;

    }

    /**
     * Creates a tarball of the given directory and zips it using gzip.
     *
     * @param string $filename
     * @param string $directory
     * @return boolean
     */
    protected function createTarAndZipIt($filename, $directory)
    {
        $vars = Registry::get('MemoryManager')->create();
        $vars->set(
            'success',
            $this->createTar($filename, $directory)
        );

        if ($vars->get('success')->getValue()) {
            $vars->set(
                'success',
                Registry::get('ZipHandler')->gzip($filename)
            );
        }

        if ($vars->get('success')->getValue()) {
            Registry::get('MemoryManager')->destroy($vars);
            unset($vars);
            return true;
        }

        Registry::get('MemoryManager')->destroy($vars);
        unset($vars);
        return false;
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
        if (!Registry::get('FileSystemManager')->dirExists(
            Registry::get('FileSystemManager')->parentDir($pathTo)
        )) {
            return false;
        }

        if (!Registry::get('FileSystemManager')->dirExists($pathTo)) {
            Registry::get('FileSystemManager')->createDir($pathTo);
        }

        if (!Registry::get('FileSystemManager')->fileExists($filename)) {
            return false;
        }

        try {
            (new \PharData($filename))->extractTo($pathTo, $files);
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
     * Uncompresses the tarball and extracts its content
     *
     * @param string $filename
     * @param string $pathTo
     * @param array|string $files
     * @return boolean
     */
    protected function unzipAndExtractTar($filename, $pathTo, $files = null)
    {
        $vars = Registry::get('MemoryManager')->create();

        $vars->set(
            'success',
            Registry::get('ZipHandler')->gunzip($filename)
        );

        if ($vars->get('success')->getValue()) {
            $vars->set(
                'success',
                $this->extractTar($filename, $pathTo, $files)
            );
        }

        $vars->set(
            'file', 
             $this->getFilename($filename) . '.tar'
        );

        Registry::get('FileSystemManager')->removeFile($vars->get('file')->getValue());

        if ($vars->get('success')->getValue()) {
            Registry::get('MemoryManager')->destroy($vars);
            unset($vars);
            return true;
        }

        Registry::get('MemoryManager')->destroy($vars);
        unset($vars);
        return false;
    }

    /**
     * Tarball manager method.
     * It can perform the following actions:
     * - Create a tarball -> flag c
     * - Extract content from tarball -> flag x
     * - Perform zip compression/decompression -> flag z
     *
     * @param string $flags
     * @param string $filename
     * @param string $directory
     * @return mixed
     */
    public function tar($flags, $filename, $directory = null)
    {
        if (
            \substr($filename, -4) !== '.tar' && 
            \substr($filename, -3) !== '.gz'
        ) {
            $filename .= '.tar';
        }

        if ($this->hasParamCreate($flags)) {
            if ($this->hasParamZip($flags)) {
                return $this->createTarAndZipIt($filename, $directory);
            }

            return $this->createTar($filename, $directory);

        } else if ($this->hasParamExtract($flags)) {
            if ($directory === null) {
                $pos = \strpos($filename, '.tar');
                $directory = substr($filename, 0, $pos);
            }

            if ($this->hasParamZip($flags)) {
                return $this->unzipAndExtractTar($filename, $directory);
            }

            return $this->extractTar($filename, $directory);
        }

        return false;
    }
}
