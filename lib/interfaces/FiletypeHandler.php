<?php

namespace BeAmado\OjsMigrator;

interface FiletypeHandler
{
    public function createFromFile($filename);

    public function dumpToFile($filename, $content);
}
