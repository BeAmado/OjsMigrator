<?php

namespace BeAmado\OjsMigrator\Util;

class EncodingHandler
{
    protected function getCharMapping()
    {
        return array(
            '&Atilde;&iexcl;'  => '&aacute;', // á
            '&Atilde;&pound;'  => '&atilde;', // ã
            '&Atilde;&cent;'   => '&acirc;',  // â
            '&Atilde;&nbsp;'   => '&agrave;', // à
            '&Atilde;&euro;'   => '&Agrave;', // À
            '&Atilde;&fnof;'   => '&Atilde;', // Ã
            '&Atilde;&sbquo;'  => '&Acirc;',  // Â
            '&Atilde;&copy;'   => '&eacute;', // é
            '&Atilde;&uml;'    => '&egrave;', // è
            '&Atilde;&ordf;'   => '&ecirc;',  // ê
            '&Atilde;&permil;' => '&Eacute;', // É
            '&Atilde;&Scaron;' => '&Ecirc;',  // Ê
            '&Atilde;&shy;'    => '&iacute;', // í
            '&Atilde;&sup3;'   => '&oacute;', // ó
            '&Atilde;&micro;'  => '&otilde;', // õ
            '&Atilde;&acute;'  => '&ocirc;',  // ô
            '&Atilde;&ldquo;'  => '&Oacute;', // Ó
            '&Atilde;&ordm;'   => '&uacute;', // ú
            '&Atilde;&scaron;' => '&Uacute;', // Ú
            '&Atilde;&sect;'   => '&ccedil;', // ç
            '&Atilde;&Dagger;' => '&Ccedil;', // Ç
            '&Acirc;&nbsp;'    => '&nbsp;',
            '&Acirc;&ordf;'    => '&ordf;',
            '&Acirc;&ordm;'    => '&ordm;',
            '&acirc;&euro;&oelig;' => '&lsquo;',
            '&acirc;&euro;&trade;' => '&rsquo;',
            '&acirc;&euro;&tilde;' => '&ldquo;',
            '&acirc;&euro;&ldquo;' => '&ndash;',
        );
    }

    protected function hasToFixJson($json)
    {
        return \strpos($json, '\\u009d') ||
               \strpos($json, '\\u0081') ||
               \strpos($json, '\\u008d');
    }

    protected function fixJsonHtmlEntities($json)
    {
        return \str_replace(
            array(
                '&acirc;&euro;\\u009d',
                '&Atilde;\\u0081',
                '&Atilde;\\u008d',
            ),
            array(
                '&rdquo;',
                '&Aacute;', // Á
                '&Iacute;', // Í
            ),
            $json
        );
    }

    public function fixJson($json)
    {
        return $this->hasToFixJson($json) ? $this->fixJsonHtmlEntities($json) : $json;
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

        return $this->fixHtmlEntityEncoding(\htmlentities(
            $this->hasToConvert($str) ? $this->convertEncoding($str) : $str
        ));
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
