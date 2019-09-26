<?php

namespace BeAmado\OjsMigrator;

trait WorkWithFiles
{
    public function getDataDir()
    {
        return \BeAmado\OjsMigrator\BASE_DIR . '/tests/_data';
    }

    public function bandsAsArray()
    {
        return [
            'bands' => [
                [
                    'name' => 'Iron Maiden',
                    'albums' => [
                        [
                            'title' => 'Iron Maiden',
                            'year' => '1980',
                            'songs' => [
                                'Prowler',
                                'Sanctuary',
                                'Remember Tomorrow',
                                'Running Free',
                                'The Phantom of the Opera',
                                'Transylvania',
                                'Strange World',
                                'Charlotte the Harlot',
                                'Iron Maiden',
                            ]
                        ],
                        [
                            'title' => 'The number of the beast',
                            'year' => '1983',
                            'songs' => [
                                'Invaders',
                                'Children of the Damned',
                                'The prisoner',
                                '22 Acacia Avenue',
                                'The number of the beast',
                                'Run to the hills',
                                'Gangland',
                                'Total Eclipse',
                                'Hallowed Be Thy Name',
                            ],
                        ],
                        [
                            'title' => 'Somewhere in time',
                            'year' => '1986',
                            'songs' => [
                                'Caught somewhere in time',
                                'Wasted years',
                                'Sea of madness',
                                'Heaven can wait',
                                'The loneliness of the long distance runner',
                                'Stranger in a strange land',
                                'Déjà vu',
                                'Alexander the great',
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Helloween',
                    'albums' => [
                        [
                            'title' => 'Keeper of the seven keys - Part I',
                            'year' => '1987',
                            'songs' => [
                                'Initiation',
                                'I\'m alive',
                                'A little time',
                                'Twilight of the gods',
                                'A tale that wasn\'t right',
                                'Future world',
                                'Halloween',
                                'Follow the sign',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getFileFullpath($filename)
    {
        return $this->getDataDir() . '/' . $filename;
    }
}
