<?php

namespace BeAmado\OjsMigrator\Util;

class EncodingHandler
{
    protected function getCharMapping()
    {
        return array(
            array(
                'broken' => '&Atilde;&iexcl;',
                'fixed'  => '&aacute;'
            ), // á
            array(
                'broken' => '&Atilde;&pound;',
                'fixed'  => '&atilde;'
            ), // ã
            array(
                'broken' => '&Atilde;&cent;',
                'fixed'  => '&acirc;'
            ),  // â
            array(
                'broken' => '&Atilde;&nbsp;',
                'fixed'  => '&agrave;'
            ), // à
            array(
                'broken' => '&Atilde;&curren;',
                'fixed'  => '&auml;'
            ), // ä
            array(
                'broken' => '&Atilde;&euro;',
                'fixed'  => '&Agrave;'
            ), // À
            array(
                'broken' => '&Atilde;&fnof;',
                'fixed'  => '&Atilde;'
            ), // Ã
            array(
                'broken' => '&Atilde;&sbquo;',
                'fixed'  => '&Acirc;'
            ),  // Â
            array(
                'broken' => '&Atilde;&copy;',
                'fixed'  => '&eacute;'
            ), // é
            array(
                'broken' => '&Atilde;&uml;',
                'fixed'  => '&egrave;'
            ), // è
            array(
                'broken' => '&Atilde;&ordf;',
                'fixed'  => '&ecirc;'
            ),  // ê
            array(
                'broken' => '&Atilde;&permil;',
                'fixed'  => '&Eacute;'
            ), // É
            array(
                'broken' => '&Atilde;&Scaron;',
                'fixed'  => '&Ecirc;'
            ),  // Ê
            array(
                'broken' => '&Atilde;&shy;',
                'fixed'  => '&iacute;'
            ), // í
            array(
                'broken' => '&Atilde;&sup3;',
                'fixed'  => '&oacute;'
            ), // ó
            array(
                'broken' => '&Atilde;&micro;',
                'fixed'  => '&otilde;'
            ), // õ
            array(
                'broken' => '&Atilde;&acute;',
                'fixed'  => '&ocirc;'
            ),  // ô
            array(
                'broken' => '&Atilde;&ldquo;',
                'fixed'  => '&Oacute;'
            ), // Ó
            array(
                'broken' => '&Atilde;&bull;',
                'fixed'  => '&Otilde;'
            ), // Õ
            array(
                'broken' => '&Atilde;&ordm;',
                'fixed'  => '&uacute;'
            ), // ú
            array(
                'broken' => '&Atilde;&scaron;',
                'fixed'  => '&Uacute;'
            ), // Ú
            array(
                'broken' => '&Atilde;&sect;',
                'fixed'  => '&ccedil;'
            ), // ç
            array(
                'broken' => '&Atilde;&Dagger;',
                'fixed'  => '&Ccedil;'
            ), // Ç
            array(
                'broken' => '&Acirc;&nbsp;',
                'fixed'  => '&nbsp;'
            ),
            array(
                'broken' => '&Acirc;&ordf;',
                'fixed'  => '&ordf;'
            ),
            array(
                'broken' => '&Acirc;&ordm;',
                'fixed'  => '&ordm;'
            ),
            array(
                'broken' => '&acirc;&euro;&oelig;',
                'fixed'  => '&lsquo;'
            ),
            array(
                'broken' => '&acirc;&euro;&trade;',
                'fixed'  => '&rsquo;'
            ),
            array(
                'broken' => '&acirc;&euro;&tilde;',
                'fixed'  => '&ldquo;'
            ),
            array(
                'broken' => '&acirc;&euro;&ldquo;',
                'fixed'  => '&ndash;'
            ),
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

    protected function getBrokenChars()
    {
        return \array_map(function($mapping) {
            return $mapping['broken'];
        }, $this->getCharMapping());
    }

    protected function getFixedChars()
    {
        return \array_map(function($mapping) {
            return $mapping['fixed'];
        }, $this->getCharMapping());
    }

    public function fixHtmlEntityEncoding($str)
    {
        return \str_replace(
            $this->getBrokenChars(),
            $this->getFixedChars(),
            $str
        );
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
