<?php

namespace BeAmado\OjsMigrator\Util;

class IoManager
{
    protected $streams;

    public function __construct()
    {
        $this->streams = array(
            'stdin' => null,
            'stdout' => null,
            'stderr' => null,
        );
    }

    protected function getStream($s)
    {
        if (
            \gettype($s) !== 'string' ||
            !\array_key_exists($s, $this->streams)
        ) {
            unset($s);
            return;
        }

        return $this->streams[$s];
    }

    /**
     * Opens the specified stream
     *
     * @param string $s
     * @return void
     */
    protected function openStream($s)
    {
        if (
            \gettype($s) !== 'string' ||
            !\array_key_exists($s, $this->streams)
        ) {
            unset($s);
            return;
        }

        switch ($s) {
            case 'stdin':
                $this->openStdin();
                break;
        }
    }

    protected function openStdin()
    {
        if (!is_resource($this->streams['stdin'])) {
            $this->streams['stdin'] = \fopen('php://stdin', 'r');
        }
    }

    protected function closeStream($s)
    {
        if (
            \gettype($s) !== 'string' ||
            !\array_key_exists($s, $this->streams)
        ) {
            unset($s);
            return;
        }

        if (
            \is_resource($this->streams[$s]) &&
            \fclose($this->streams[$s])
        ) {
            unset($s);
            return true;
        }

        unset($s);
        return false;
    }

    /**
     * Opens a stream to the standard input and reads the content
     *
     * @return string
     */
    protected function readFromStdin()
    {
        /*if ($this->getStream('stdin') === null) {
            $this->openStream('stdin');
        }

        return \fgets($this->getStream('stdin'));*/

    }

    protected function writeToStdout()
    {
        /*if ($this->getStream('stdout') === null) {
            $this->openStream('stdout');
        }*/
    }

    public function getUserInput($args = array())
    {

    }

    public function destroy()
    {
        foreach ($this->streams as $key => $stream) {
            if ($this->closeStream($key)) {
            }
            else if (\is_object($stream) && \method_exists($stream, 'destroy')) {
                $stream->destroy();
            }
            else if (\is_resource($stream)) {
                \fclose($stream);
            }
            $this->streams[$key] = null;
            unset($this->streams[$key]);
        }
        
        unset($key);
        unset($stream);
        unset($this->streams);
    }
}
