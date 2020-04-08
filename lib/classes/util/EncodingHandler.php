<?php

namespace BeAmado\OjsMigrator\Util;

class EncodingHandler
{
    protected function getCharMapping()
    {
        return array(
            '&Atilde;&iexcl;'  => '&aacute;', // á
            '&Atilde;&pound;'  => '&atilde;', // ã
            '&Atilde;&euro;'   => '&Agrave;', // À
            '&Atilde;&fnof;'   => '&Atilde;', // Ã
            '&Atilde;&copy;'   => '&eacute;', // é
            '&Atilde;&ordf;'   => '&ecirc;',  // ê
            '&Atilde;&shy;'    => '&iacute;', // í
            '&Atilde;&sup3;'   => '&oacute;', // ó
            '&Atilde;&micro;'  => '&otilde;', // õ
            '&Atilde;&ordm;'   => '&uacute;', // ú
            '&Atilde;&scaron;' => '&Uacute;', // Ú
            '&Atilde;&sect;'   => '&ccedil;', // ç
            '&Atilde;&Dagger;' => '&Ccedil;', // Ç
            '&Acirc;&nbsp;'    => '&ndash;',  // -

        );
    }

    public function fixHtmlEntityEncoding($str)
    {
        foreach ($this->getCharMapping() as $bad => $good) {
            $str = str_replace($bad, $good, $str);
        }

        unset($bad);
        unset($good);
        return $str;
    }

    protected function hasToConvert($str)
    {
        return \is_string($str) &&
            !empty($str) &&
            empty(\htmlentities($str));
    }

    protected function convertEncoding(
        $str,
        $to = 'UTF-8',
        $from = 'Windows-1252'
    ) {
        return \mb_convert_encoding(
            $str,
            $to,
            $from
        );
//        return \iconv(
//            $from,
//            \implode('//', array(
//                $to,
//                'TRANSLIT',
//            )),
//            $str
//        );
    }

    public function fixEncoding($str)
    {
        return \html_entity_decode(
            $this->fixHtmlEntityEncoding(
                \htmlentities($str)
            )
        );
    }

    protected function encode($str)
    {
        if (!\is_string($str))
            return $str;

        return \htmlentities(
            $this->hasToConvert($str) ? $this->convertEncoding($str) : $str
        );
    }

    protected function decode($str)
    {
        return \html_entity_decode($str);
    }

    protected function processData(
        $data, 
        $operation, 
        $depth = 0, 
        $maxdepth = 10
    ) {
        if (!\in_array(\strtolower($operation), array(
            'import',
            'export',
        )))
            return $data;

        if ($maxdepth > 10)
            $maxdepth = 10;

        if ($depth > $maxdepth)
            return $data;

        if (\is_string($data))
            return $this->{\strtolower($operation) === 'import' 
                ? 'decode' 
                : 'encode'}($data);

        if (!\is_array($data))
            return $data;

        foreach ($data as $key => $value) {
            $data[$key] = $this->processData($value, $operation, $depth + 1);
        }

        return $data;
    }

    public function processForExport($data)
    {
        return $this->processData($data, 'export');
    }

    public function processForImport($data)
    {
        return $this->processData($data, 'import');
    }
}
