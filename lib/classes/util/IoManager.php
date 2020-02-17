<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\Registry;

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

    protected function closeStdin()
    {
        return $this->closeStream('stdin');
    }

    protected function closeStdout()
    {
        return $this->closeStream('stdout');
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

    public function getUserInput($message, $options = array())
    {
        $beginning = Registry::get('TimeKeeper')->now();
        $maxtime = 60000; // 1 minute
        if (!\array_key_exists('maxtime', $options))
            $options['maxtime'] = 20000; // 20 seconds

        if (!\array_key_exists('timelapse', $options))
            $options['timelapse'] = 5000; // 5 seconds

        if (!\array_key_exists('removeTimelapseAfterFirstKeystroke', $options))
            $options['removeTimelapseAfterFirstKeystroke'] = true;

        if ($options['maxtime'] < $maxtime)
            $maxtime = (int) $options['maxtime'];

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
        //return \readline($message);
        $this->writeToStdout($message);
        $content = '';
        $this->openStdin();
        $stdin = $this->getStream('stdin');
        $read = array(&$stdin);
        $write = null;
        $expect = null;
        \readline_callback_handler_install('', function(){});
        do {
            $input = null;

            $beginWait = Registry::get('TimeKeeper')->now();

            $keypressed = \stream_select($read, $write, $expect, 0, 
                $options['timelapse'] * 1000
            );

            if (Registry::get('TimeKeeper')
                        ->elapsedTime($beginWait) > $options['timelapse'])
                break;

            if ($keypressed)
                $input = \stream_get_contents($this->getStream('stdin'), 1);

            $this->writeToStdout($input);

            if ($input == PHP_EOL)
                break;

            if (\is_string($input))
                $content .= $input;

            if (\substr($content, -2) === '\\b')
                $content = substr($content, 0, -3);
                
        } while (
            Registry::get('TimeKeeper')->elapsedTime($beginning) <= $maxtime
        );
        \readline_callback_handler_remove();
        $this->closeStdin();

        return $content;

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
