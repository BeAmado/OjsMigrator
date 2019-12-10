<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Entity\AnnouncementHandler;
use BeAmado\OjsMigrator\Registry;

// interfaces
use BeAmado\OjsMigrator\StubInterface;

// traits
use BeAmado\OjsMigrator\TestStub;

class AnnouncementHandlerTest extends FunctionalTest
{
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        foreach (array(
            'announcements',
            'announcement_settings',
            'announcement_types',
            'announcement_type_settings',
            'journals',
        ) as $table) {
            Registry::get('DbHandler')->createTableIfNotExists($table);
        }

        $eh = Registry::get('EntityHandler');
        $eh->createOrUpdateInDatabase($eh->create('journals', array(
            'journal_id' => 289,
            'path' => 'test_journal',
        )));
    }

    protected function createAnnouncements()
    {
        return array(
            array(
                '__tableName_' => 'announcements',
                'announcement_id' => '1827',
                'assoc_type' => 0,
                'assoc_id' => 289,
                'type_id' => null,
                'date_expire' => '2018-09-18 08:09:10',
                'date_posted' => '2018-07-14 04:01:57',
                'settings' => array(
                    array(
                        '__tableName_' => 'announcement_settings',
                        'announcement_id' => '1827',
                        'locale' => 'fr_CA',
                        'setting_name' => 'title',
                        'setting_value' => 'Soyez les bienvenues',
                        'setting_type' => 'string',
                    ),
                    array(
                        '__tableName_' => 'announcement_settings',
                        'announcement_id' => '1827',
                        'locale' => 'fr_CA',
                        'setting_name' => 'description',
                        'setting_value' => '<p>Nous vous souhaitons une très bonne année</p>',
                        'setting_type' => 'string',
                    ),
                ),
            ),
            array(
                '__tableName_' => 'announcements',
                'announcement_id' => '179',
                'assoc_type' => 0,
                'assoc_id' => 289,
                'type_id' => null,
                'date_expire' => '2018-05-18 07:09:10',
                'date_posted' => '2018-01-24 02:03:59',
                'settings' => array(
                    array(
                        '__tableName_' => 'announcement_settings',
                        'announcement_id' => '179',
                        'locale' => 'fr_CA',
                        'setting_name' => 'title',
                        'setting_value' => 'Ouverture des inscriptions',
                        'setting_type' => 'string',
                    ),
                    array(
                        '__tableName_' => 'announcement_settings',
                        'announcement_id' => '179',
                        'locale' => 'fr_CA',
                        'setting_name' => 'description',
                        'setting_value' => '<p>Les inscriptions pour le course de vétérinaire sont ouvertes jusqu\'au 18 mai.</p>', 
                        'setting_type' => 'string',
                    ),
                ),
            ),
        );
    }

    public function getStub()
    {
        return new class extends AnnouncementHandler {
            use TestStub;
        };
    }

    public function testCanRegisterAnnouncement()
    {
        $ann = Registry::get('AnnouncementHandler')->create(
            $this->createAnnouncements()[0]
        );

        $registered = $this->getStub()->callMethod(
            'registerAnnouncement',
            $ann
        );

        $fromDb = Registry::get('AnnouncementsDAO')->read(array(
            'announcement_id' => Registry::get('DataMapper')->getMapping(
                'announcements',
                $ann->getId()
            )
        ));

        $testJournal = Registry::get('JournalsDAO')->read(array(
            'path' => 'test_journal',
        ))->get(0);

        $this->assertSame(
            '1|1|' . $ann->getData('date_posted') . '|' . $testJournal->getId(),
            implode('|', array(
                (int) $registered,
                $fromDb->length(),
                $fromDb->get(0)->getData('date_posted'),
                $fromDb->get(0)->getData('assoc_id')
            ))
        );
    }

    /**
     * @depends testCanRegisterAnnouncement
     */
    public function testCanImportAnnouncementSetting()
    {
        $annSetting = $this->createAnnouncements()[0]['settings'][0];
        
        $imported = $this->getStub()->callMethod(
            'importAnnouncementSetting',
            array('data' => $annSetting)
        );

        $fromDb = Registry::get('AnnouncementSettingsDAO')->read(array(
            'announcement_id' => Registry::get('DataMapper')->getMapping(
                'announcements',
                $annSetting['announcement_id']
            ),
        ));

        $this->assertSame(
            '1|1'
                . '|' . $annSetting['locale']
                . '|' . $annSetting['setting_name']
                . '|' . $annSetting['setting_value']
                . '|' . $annSetting['setting_type'], 
            implode('|', array(
                (int) $imported,
                $fromDb->length(),
                $fromDb->get(0)->getData('locale'),
                $fromDb->get(0)->getData('setting_name'),
                $fromDb->get(0)->getData('setting_value'),
                $fromDb->get(0)->getData('setting_type'),
            ))
        );
    }

    /**
     * @depends testCanRegisterAnnouncement
     * @depends testCanImportAnnouncementSetting
     */
    public function testCanImportAnnouncement()
    {
        $announcement = Registry::get('MemoryManager')->create(
            $this->createAnnouncements()[0]
        );

        $imported = Registry::get('AnnouncementHandler')->importAnnouncement(
            $announcement
        );

        $announcement->set(
            'announcement_id',
            Registry::get('DataMapper')->getMapping(
                'announcements',
                $announcement->get('announcement_id')->getValue()
            )
        );

        $announcement->get('settings')->forEachValue(function($setting) {
            $setting->set(
                'announcement_id',
                Registry::get('DataMapper')->getMapping(
                    'announcements',
                    $setting->get('announcement_id')->getValue()
                )
            );
        });

        $ann = Registry::get('AnnouncementsDAO')->read(array(
            'announcement_id' => $announcement->get('announcement_id')
                                              ->getValue(),
        ))->get(0);

        $settings = Registry::get('AnnouncementSettingsDAO')->read(array(
            'announcement_id' => $announcement->get('announcement_id')
                                              ->getValue(),
        ));

        $this->assertSame(
            '1|1|' . $announcement->get('date_expire')->getValue(),
            implode('|', array(
                $imported,
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $announcement->get('settings')->toArray(),
                    $settings->toArray()
                ),
                $ann->getData('date_expire')
            ))
        );
    }

    /**
     * @depends testCanImportAnnouncement
     */
    public function testCanImportAnotherAnnouncement()
    {
        $announcement = Registry::get('MemoryManager')->create(
            $this->createAnnouncements()[1]
        );

        $imported = Registry::get('AnnouncementHandler')->importAnnouncement(
            $announcement
        );

        $announcement->set(
            'announcement_id',
            Registry::get('DataMapper')->getMapping(
                'announcements',
                $announcement->get('announcement_id')->getValue()
            )
        );

        $announcement->get('settings')->forEachValue(function($setting) {
            $setting->set(
                'announcement_id',
                Registry::get('DataMapper')->getMapping(
                    'announcements',
                    $setting->get('announcement_id')->getValue()
                )
            );
        });

        $ann = Registry::get('AnnouncementsDAO')->read(array(
            'announcement_id' => $announcement->get('announcement_id')
                                              ->getValue(),
        ))->get(0);

        $settings = Registry::get('AnnouncementSettingsDAO')->read(array(
            'announcement_id' => $announcement->get('announcement_id')
                                              ->getValue(),
        ));

        $announcements = Registry::get('AnnouncementsDAO')->read();
        $announcementSettings = Registry::get('AnnouncementSettingsDAO')
                                        ->read();

        $this->assertSame(
            '1|1|' 
                . $announcement->get('date_expire')->getValue()
                . '|2|4',
            implode('|', array(
                $imported,
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $announcement->get('settings')->toArray(),
                    $settings->toArray()
                ),
                $ann->getData('date_expire'),
                $announcements->length(),
                $announcementSettings->length(),
            ))
        );
    }

    /**
     * @depends testCanImportAnnouncement
     */
    public function testCanGetTheAnnouncementSettings()
    {
        $announcement = Registry::get('MemoryManager')->create(
            $this->createAnnouncements()[0]
        );

        $announcement->set(
            'announcement_id',
            Registry::get('DataMapper')->getMapping(
                'announcements',
                $announcement->get('announcement_id')->getValue()
            )
        );

        $announcement->get('settings')->forEachValue(function($setting) {
            $setting->set(
                'announcement_id',
                Registry::get('DataMapper')->getMapping(
                    'announcements',
                    $setting->get('announcement_id')->getValue()
                )
            );
        });

        $settings = $this->getStub()->getAnnouncementSettings($announcement);

        $this->assertTrue(Registry::get('ArrayHandler')->areEquivalent(
            $announcement->get('settings')->toArray(),
            $settings->toArray()
        ));
    }

    /**
     * @depends testCanGetTheAnnouncementSettings
     */
    public function testCanExportTheAnnouncementsFromTheTestJournal()
    {
        $journal = Registry::get('JournalsDAO')->read(array(
            'path' => 'test_journal',
        ))->get(0);

        $annHandler = Registry::get('AnnouncementHandler');

        $annHandler->exportAnnouncementsFromJournal($journal);

        $list = Registry::get('FileSystemManager')->listdir(
            $annHandler->getEntityDataDir('announcements')
        );

        $anns = array();
        foreach ($list as $filename) {
            $anns[] = Registry::get('JsonHandler')->createFromFile($filename);
        }

        $mocked = $this->createAnnouncements();
        $mockedSettings = array();
        $mockedAnnouncements = array();
        foreach ($mocked as $m) {
            $mockedSettings = array_merge($mockedSettings, $m['settings']);
            unset($m['settings']);
            $mockedAnnouncements[] = $m;
        }

        foreach ($mockedAnnouncements as &$ma) {
            $ma['announcement_id'] = Registry::get('DataMapper')->getMapping(
                'announcements',
                $ma['announcement_id']
            );
            $ma['assoc_id'] = Registry::get('DataMapper')->getMapping(
                'journals',
                $ma['assoc_id']
            );
        }
        unset($ma);
        
        foreach ($mockedSettings as &$ms) {
            $ms['announcement_id'] = Registry::get('DataMapper')->getMapping(
                'announcements',
                $ms['announcement_id']
            );
        }
        unset($ms);

        $announcements = array();
        $settings = array();
        foreach($anns as $ann) {
            $a = $ann->toArray();
            $settings = array_merge($settings, $a['settings']);
            unset($a['settings']);
            $announcements[] = $a;
        }

        $this->assertSame(
            '2-1-1',
            implode('-', array(
                count($list),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $mockedAnnouncements,
                    $announcements
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $mockedSettings,
                    $settings
                ),
            ))
        );
    }
}
