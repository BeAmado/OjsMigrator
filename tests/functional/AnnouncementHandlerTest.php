<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Entity\AnnouncementHandler;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Test\AnnouncementMock;
use BeAmado\OjsMigrator\Test\FixtureHandler;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

class AnnouncementHandlerTest extends FunctionalTest
{
    public static function setUpBeforeClass($args = array(
        'createTables' => array(
            'announcements',
            'announcement_settings',
            'announcement_types',
            'announcement_type_settings',
        ),
    )) : void {
        parent::setUpBeforeClass($args);
        (new FixtureHandler())->createSingle('journals', 'test_journal');
    }

    protected function createWelcomeAnnouncement()
    {
        return (new AnnouncementMock())->getWelcomeAnnouncement();
    }

    protected function createInscriptionAnnouncement()
    {
        return (new AnnouncementMock())->getInscriptionAnnouncement();
    }

    public function getStub()
    {
        return new class extends AnnouncementHandler {
            use TestStub;
        };
    }

    public function testCanRegisterAnnouncement()
    {
        $ann = $this->createWelcomeAnnouncement();
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
        $annSetting = $this->createWelcomeAnnouncement()
                           ->get('settings')->get(0)->toArray();
        
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
        $announcement = $this->createWelcomeAnnouncement();

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
        $announcement = $this->createInscriptionAnnouncement();

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
            $this->createWelcomeAnnouncement()
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

        $mocked = array(
            $this->createWelcomeAnnouncement()->toArray(),
            $this->createInscriptionAnnouncement()->toArray(),
        );
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
