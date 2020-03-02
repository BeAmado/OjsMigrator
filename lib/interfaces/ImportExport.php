<?php

namespace BeAmado\OjsMigrator;

interface ImportExport
{
    public function import($entity);

    public function export($entity);
}
