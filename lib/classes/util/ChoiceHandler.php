<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\Registry;

class ChoiceHandler
{
    public function binaryChoice($message, $default = false)
    {
        if (
            \is_string($default) && 
            \in_array(
                \strtolower($default), 
                array('n', 'no')
            )
        )
            $default = false;

        $options = $default ? '(Y/n)' : '(y/N)';

        $answer = Registry::get('IoManager')->getUserInput(
            $message . ' ' . $options . ' : '
        );

        if (!\in_array(
            \strtolower($answer), 
            array('y', 'yes', 'n', 'no')
        ))
            return $default;

        return \strtolower(\substr($answer, 0, 1)) === 'y';
    }
}
