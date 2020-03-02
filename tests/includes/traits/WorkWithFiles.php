<?php

namespace BeAmado\OjsMigrator\Test;

trait WorkWithFiles
{
    public function sep()
    {
        return \BeAmado\OjsMigrator\DIR_SEPARATOR;
    }
    
    public function getDataDir()
    {
        return \BeAmado\OjsMigrator\BASE_DIR 
            . $this->sep() . 'tests' 
            . $this->sep() . '_data';
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

    private function getIronMaidenAlbumSongs()
    {
        return array(
            'name' => 'songs',
            'text' => null,
            'attributes' => array(),
            'children' => array(
                array(
                    'name' => 'song',
                    'text' => 'Prowler',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Sanctuary',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Remember Tomorrow',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Running Free',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'The Phantom of the Opera',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Transylvania',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Strange World',
                    'attributes' => array(),
                        'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Charlotte the Harlot',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Iron Maiden',
                    'attributes' => array(),
                    'children' => array(),
                ),
            ),
        );
    }

    private function getTheNumberOfTheBeastAlbumSongs()
    {
        return array(
            'name' => 'songs',
            'text' => null,
            'attributes' => array(),
            'children' => array(
                array(
                    'name' => 'song',
                    'text' => 'Invaders',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Children of the Damned',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'The prisoner',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => '22 Acacia Avenue',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'The number of the beast',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Run to the hills',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Gangland',
                    'attributes' => array(),
                        'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Total Eclipse',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Hallowed Be Thy Name',
                    'attributes' => array(),
                    'children' => array(),
                ),
            ),
        );
    }

    private function getSomewhereInTimeAlbumSongs()
    {
        return array(
            'name' => 'songs',
            'text' => null,
            'attributes' => array(),
            'children' => array(
                array(
                    'name' => 'song',
                    'text' => 'Caught somewhere in time',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Wasted years',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Sea of madness',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Heaven can wait',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'The loneliness of the long distance runner',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Stranger in a strange land',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Déjà vu',
                    'attributes' => array(),
                        'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Alexander the great',
                    'attributes' => array(),
                    'children' => array(),
                ),
            ),
        );
    }

    private function getIronMaidenAlbums()
    {
        return [
            [
                'name' => 'album',
                'text' => null,
                'attributes' => [],
                'children' => [
                    [
                        'name' => 'title',
                        'text' => 'Iron Maiden',
                        'attributes' => [],
                        'children' => [],
                    ],
                    [
                        'name' => 'year',
                        'text' => '1980',
                        'attributes' => [],
                        'children' => []
                    ],
                    $this->getIronMaidenAlbumSongs(),
                ],
            ],
            [
                'name' => 'album',
                'text' => null,
                'attributes' => [],
                'children' => [
                    [
                        'name' => 'title',
                        'text' => 'The number of the beast',
                        'attributes' => [],
                        'children' => [],
                    ],
                    [
                        'name' => 'year',
                        'text' => '1983',
                        'attributes' => [],
                        'children' => []
                    ],
                    $this->getTheNumberOfTheBeastAlbumSongs(),
                ],
            ],
            [
                'name' => 'album',
                'text' => null,
                'attributes' => [],
                'children' => [
                    [
                        'name' => 'title',
                        'text' => 'Somewhere in time',
                        'attributes' => [],
                        'children' => [],
                    ],
                    [
                        'name' => 'year',
                        'text' => '1986',
                        'attributes' => [],
                        'children' => []
                    ],
                    $this->getSomewhereInTimeAlbumSongs(),
                ],
            ],
        ];
    }

    private function getKeeperOfTheSevenKeysPart1AlbumSongs()
    {
        return array(
            'name' => 'songs',
            'text' => null,
            'attributes' => array(),
            'children' => array(
                array(
                    'name' => 'song',
                    'text' => 'Initiation',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'I\'m alive',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'A little time',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Twilight of the gods',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'A tale that wasn\'t right',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Future world',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Halloween',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Follow the sign',
                    'attributes' => array(),
                    'children' => array(),
                ),
            ),
        );
    }

    private function getHelloweenAlbums()
    {
        return [
            [
                'name' => 'album',
                'text' => null,
                'attributes' => [],
                'children' => [
                    [
                        'name' => 'title',
                        'text' => 'Keeper of the seven keys - Part I',
                        'attributes' => [],
                        'children' => [],
                    ],
                    [
                        'name' => 'year',
                        'text' => '1987',
                        'attributes' => [],
                        'children' => []
                    ],
                    $this->getKeeperOfTheSevenKeysPart1AlbumSongs(),
                ],
            ]
        ];
    }

    public function bandsAsVerboseArray()
    {
        $Helloween = [
            'name' => 'band',
            'text' => null,
            'attributes' => [],
            'children' => [
                [
                    'name' => 'name',
                    'text' => 'Helloween',
                    'attributes' => [],
                    'children' => [],
                ],
                [
                    'name' => 'albums',
                    'text' => null,
                    'attributes' => [],
                    'children' => $this->getHelloweenAlbums(),
                ],
            ],
        ];

        $IronMaidenAlbums = [];
        $IronMaiden = [
            'name' => 'band',
            'text' => null,
            'attributes' => [],
            'children' => [
                [
                    'name' => 'name',
                    'text' => 'Iron Maiden',
                    'attributes' => [],
                    'children' => [],
                ],
                [
                    'name' => 'albums',
                    'text' => null,
                    'attributes' => [],
                    'children' => $this->getIronMaidenAlbums(),
                ],
            ],
        ];

        return [
            'name' => 'bands',
            'text' => null,
            'attributes' => [],
            'children' => [
                $IronMaiden,
                $Helloween
            ],
        ];
    }

    public function getFileFullpath($filename)
    {
        return $this->getDataDir() . $this->sep() . $filename;
    }
}
