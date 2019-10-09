<?php

namespace BeAmado\OjsMigrator\Util;

class StringHandler
{

    protected function hasAmpersandChar($str)
    {
        return \strpos($str, '&') !== false;
    }

    protected function hasSemicolonChar($str)
    {
        return \strpos($str, ';') !== false;
    }

    public function hasHtmlEntity($str)
    {
        return $this->hasAmpersandChar($str) && $this->hasSemicolonChar($str);
    }

    public function encodeSpecialChars($str)
    {
        return \htmlentities($str);
    }

    public function decodeSpecialChars($str)
    {
        return \html_entity_decode($str);
    }
}
