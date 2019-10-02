<?php

require_once(dirname(__FILE__) . '/../includes/bootstrap.php');

$io = new class extends \BeAmado\OjsMigrator\Util\IoManager {
    use \BeAmado\OjsMigrator\TestStub;
};

$names = array(
    'Gilberto',
    'Edil',
    'Bernardo',
    'Bárbara',
    'Jefferson',
    'Luciano',
);

$chars1 = array('|', '/', '-', '\\', '|', '/', '-', '\\',);
$chars2 = array('-', '--', '---', '----', '-----', '------', '-------', '--------', '---------', '----------');

function wait($time)
{
    $sum = function($a, $b) {
        return $a + $b;
    };

    $begin = array_reduce(
        explode(' ', microtime()),
        function($a, $b) {
            return $a + $b;
        }
    );

    $maxTime = 10 * 1000;

    $elapsed = 0;

    while (
        $elapsed < $maxTime
     && $elapsed < $time
    ) {
        $now = array_reduce(
            explode(' ', microtime()),
            function($a, $b) {
                return $a + $b;
            }
        );

        $elapsed = 1000 * ($now - $begin);
    }
}

/*$io->callMethod(
    'writeToStdout',
    '!Hola a todos!'
);*/

for ($i = 0 ; $i < 5; $i++) {
    foreach ($chars1 as $char) {
        wait(50);
        $io->clearStdout();
        $io->writeToStdout('      ' . $char);
    }
}

for ($i = 0 ; $i < 5; $i++) {
    foreach ($chars2 as $char) {
        wait(50);
        $io->clearStdout();
        $io->writeToStdout('      ' . $char);
    }
}
$io->clearStdout();


//$data = $io->getUserInput('Enter some data: ');
$io->clearStdout();
//var_dump($data);
(new \BeAmado\OjsMigrator\Util\MemoryManager())->destroy($io);

