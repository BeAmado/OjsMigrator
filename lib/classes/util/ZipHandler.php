<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\Registry;

class ZipHandler
{
    /**
     * Compresses the given file using the gzip algorithm.
     *
     * @param string $filename
     * @param integer $rate
     * @return boolean
     */
    public function gzip($filename, $rate = null)
    {
        $gz = \gzopen(
            $filename . '.gz',
            'wb' . $rate
        );
        
        \gzwrite(
            $gz,
            Registry::get('FileHandler')->read($filename)
        );

        return \gzclose($gz);
    }

    /**
     * Uncompresses the specified gzip compressed file.
     *
     * @param string $filename - The name of the file with or without .gz at 
     * the end.
     * @return boolean
     */
    public function gunzip($filename)
    {
        if (\substr($filename, -3) !== '.gz') {
            $filename .= '.gz';
        }

        $gz = \gzopen($filename, 'rb');

        if ($gz === false) {
            unset($gz);
            return false;
        }

        $fp = \fopen(
            substr($filename, 0, -3),
            'wb'
        );

        while (!\gzeof($gz)) {
            $content = \gzread($gz, 10000);
            $written = \fwrite($fp, $content);

            if (!$written) {
                return false;
            }
        }

        \gzclose($gz);
        return \fclose($fp);
    }
}
