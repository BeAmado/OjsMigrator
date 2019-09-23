<?php

namespace BeAmado\OjsMigrator\Util;

class JsonHandler
{
    public function createFromFile($filename)
    {
        return (new MemoryManager())->create(
            json_decode(
                \file_get_contents($filename),
                true
            )
        );
    }
}
