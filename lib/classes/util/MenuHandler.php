<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\Registry;

class MenuHandler
{
    protected function showMenu(
        $title = '',
        $header = '',
        $options = array()
    ) {
        if (!empty($title))
            Registry::get('IoManager')->writeToStdout(
                PHP_EOL . $title . PHP_EOL
            );

        Registry::get('IoManager')->writeToStdout(PHP_EOL . $header . PHP_EOL);

        foreach ($options as $number => $text) {
            Registry::get('IoManager')->writeToStdout(
                $number . ' - ' . $text . PHP_EOL
            );
        }
    }

    public function confirm($choice, $default = false)
    {
        return \in_array(
            \strtolower(Registry::get('IoManager')->getUserInput(
                'You chose "' . $choice . '". Are you sure? '
                    . ($default ? '(Y/n)' : '(y/N)') . ' : '
            )),
            ($default ? array('no', 'n') : array('yes', 'y'))
        );
    }

    public function getOption(
        $options,
        $header = 'Choose on of the following options:',
        $choicePrompt = 'Enter the number of your choice: ',
        $title = ''
    ) {
        if (!empty($title))
            Registry::get('IoManager')->writeToStdout(
                PHP_EOL . '###### ' . $title . ' ######' . PHP_EOL
            );

        for ($i = 0; $i < 10; $i++) {
            $this->showMenu('', $header, $options);
            $option = Registry::get('IoManager')->getUserInput($choicePrompt);

            if (\array_key_exists($option, $options))
                return $options[$option];

            Registry::get('IoManager')->writeToStdout(
                PHP_EOL . 'Invalid option!' . PHP_EOL
            );
        }
    }
}
