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

    public function fixEncoding($str)
    {
        return \html_entity_decode(
            $this->fixHtmlEntityEncoding(
                \htmlentities($str)
            )
        );
    }
}
