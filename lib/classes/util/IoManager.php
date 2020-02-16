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
     * @return boolean
     */
    protected function openStream($s, $mode = 'r+')
    {
        if (
            \gettype($s) !== 'string' ||
            !\array_key_exists($s, $this->streams)
        ) {
            return false;
        }

        if (\is_resource($this->streams[$s])) {
            return true;
        }

        switch ($s) {
            case 'stdin':
                $this->streams['stdin'] = \fopen('php://stdin', $mode);
                break;
            
            case 'stdout':
                $this->streams['stdout'] = \fopen('php://stdout', $mode);
        }

        return \is_resource($this->streams[$s]);
    }

    /**
     * Opens a stream to read from the standard input.
     *
     * @return boolean
     */
    protected function openStdin()
    {
        return $this->openStream('stdin', 'r');
    }

    /**
     * Opens a stream to write to the standard output.
     *
     * @return boolean
     */
    protected function openStdout()
    {
        return $this->openStream('stdout', 'w');
    }

    protected function closeStream($s)
    {
        if (
            \gettype($s) !== 'string' ||
            !\array_key_exists($s, $this->streams)
        ) {
            return false;
        }

        if (
            \is_resource($this->streams[$s]) &&
            \fclose($this->streams[$s])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Opens a stream to the standard input and reads the content
     *
     * @return string
     */
    protected function readFromStdin()
    {
        $this->openStdin();
        $content = \fgetc($this->streams['stdin']);

        //$this->closeStream('stdin');
        return $content;
    }

    /**
     * Clears the standard output.
     */
    public function clearStdout()
    {
        $this->openStdout();

        $spaces = ' ';
        for ($i = 0; $i < 100; $i++) {
            $spaces .= ' ';
        }
        
        \fwrite($this->streams['stdout'], "\r$spaces\r");
        $this->closeStream('stdout');
    }

    /**
     * Writes the specified content to stdout.
     *
     * @param string $content
     * @param boolean $clear
     * @return boolean
     */
    public function writeToStdout($content, $clear = false)
    {
        if ($clear) {
            $this->clearStdout();
        }

        $this->openStdout();
        if (!\fwrite($this->streams['stdout'], $content)) {
            return false;
        }

        return $this->closeStream('stdout');
    }

    public function getUserInput($message)
    {
        /*$this->writeToStdout($message, true);
        $content = '';
        do {
            $c = $this->readFromStdin();
            $content .= $c;
        } while ($c != PHP_EOL );

        $this->closeStream('stdin');

        if (substr($content, -1) == PHP_EOL) {
            return substr($content, 0, -1);
        }

        return $content;*/
        return \readline($message);
    }

    public function destroy()
    {
        foreach ($this->streams as $key => $stream) {
            if ($this->closeStream($key)) {
            }
            else if (
                \is_object($stream) 
             && \method_exists($stream, 'destroy')
            ) {
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
