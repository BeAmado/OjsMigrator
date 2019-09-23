<?php

namespace BeAmado\OjsMigrator;

trait WorkWithFiles
{
    public function getDataDir()
    {
        return \BeAmado\OjsMigrator\BASE_DIR . '/tests/_data';
    }
}
