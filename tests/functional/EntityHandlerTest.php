<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Entity\EntityHandler;
use BeAmado\OjsMigrator\Registry;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

class EntityHandlerTest extends FunctionalTest implements StubInterface
{
    public static function setUpBeforeClass($args = array(
        'createTables' => array(
            'announcements',
            'groups',
        ),
    )) : void {
        parent::setUpBeforeClass($args);
    }

    public function getStub()
    {
        return new class extends EntityHandler {
            use TestStub;
        };
    }

    public function testCreateJournalPassingNoData()
    {
        $journal = (new EntityHandler())->create('journals');

        $this->assertTrue(
            $journal->getData('journal_id') === null &&
            $journal->getData('path') === '' &&
            $journal->getData('seq') === '0' &&
            $journal->getData('enabled') === '1' &&
            $journal->getData('primary_locale') === ''
        );
    }

    public function testCreateSectionEditor()
    {
        $sectionEditor = Registry::get('EntityHandler')->create(
            'section_editors',
            array(
                'user_id' => 21,
                'journal_id' => 4,
                'section_id' => 327,
            )
        );

        $this->assertTrue(
            $sectionEditor->getTableName() === 'section_editors' &&
            $sectionEditor->getData('user_id') === 21 &&
            $sectionEditor->getData('section_id') === 327 &&
            $sectionEditor->getData('journal_id') === 4 &&
            $sectionEditor->getData('can_edit') == 1 &&
            $sectionEditor->getData('can_review') == 1
        );
    }

    public function testComparing2EntitiesFromDifferentTablesReturnsFalse()
    {
        $user = Registry::get('EntityHandler')->create('users', array(
            'username' => 'johndoe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@devnull.com',
            'password' => 'nobody',
        ));

        $userInterest = Registry::get('EntityHandler')->create(
            'user_interests',
            array(
                'user_id' => 13,
                'controlled_vocab_entry_id' => 22,
            )
        );

        $this->assertFalse(
            Registry::get('entityHandler')->areEqual($user, $userInterest)
        );
    }

    public function testComparing2UsersWithDifferentIdsReturnsTrue()
    {
        $user = array(
            'username' => 'johndoe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@devnull.com',
            'password' => 'nobody',
        );

        $user1 = Registry::get('entityhandler')->create('users', $user);
        $user2 = Registry::get('entityhandler')->create('users', $user);

        $user1->set('user_id', 12);
        $user1->set('alpha', 123);

        $user2->set('user_id', 1890);
        $user2->set('alpha', 'clouds');
        $user2->set('beta', 8910);

        $this->assertTrue(
            $user1->getData('user_id') !== $user2->getData('user_id') &&
            Registry::get('EntityHandler')->areEqual($user1, $user2)
        );
    }

    public function testGetReviewFormIdFieldPassingAString()
    {
        $field = Registry::get('EntityHandler')->getIdField('review_forms');
        $this->assertSame(
            'review_form_id',
            $field
        );
    }

    public function testGetReviewFormIdFieldPassingAnEntity()
    {
        $reviewForm = Registry::get('EntityHandler')->create(
            'review_forms', 
            array(
                'assoc_id' => 23,
            )
        );

        $field = Registry::get('EntityHandler')->getIdField($reviewForm);

        $this->assertSame(
            'review_form_id',
            $field
        );
    }

    public function testGetUsersDataDirPassingAString()
    {
        $dir = Registry::get('EntityHandler')->getEntityDataDir('users');
        $expected = Registry::get('EntitiesDir')
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'users';
        
        $this->assertSame($expected, $dir);
    }

    public function testGetUsersDataDirPassingAnEntity()
    {
        $user = Registry::get('EntityHandler')->create('users', array(
            'user_id' => 12,
            'email' => 'chuck@masterkick.com',
            'first_name' => 'Charles',
            'password' => 'Dontmesswithme',
        ));

        $dir = Registry::get('EntityHandler')->getEntityDataDir($user);
        $expected = Registry::get('EntitiesDir')
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'users';
        
        $this->assertSame($expected, $dir);
    }

    public function testCreateAnnouncementInDatabase()
    {
        Registry::get('DbHandler')->createTableIfNotExists('announcements');
        $announcement = Registry::get('EntityHandler')->create(
            'announcements',
            array(
                'announcement_id' => 12,
                'assoc_id' => 1,
                'type_id' => null,
                'date_expire' => null,
                'date_posted' => '2018-10-12 17:58:21',
                'assoc_type' => 256,
            )
        );

        $created = $this->getStub()->callMethod(
            'createInDatabase',
            $announcement
        );

        $this->assertTrue(
            $created === true &&
            Registry::get('DataMapper')->getMapping('announcements', 12) === '1'
        );
    }

    /**
     * @depends testCreateAnnouncementInDatabase
     */
    public function testCreateAnotherAnnouncementAndGetTheMappings()
    {
        $created = $this->getStub()->callMethod(
            'createInDatabase',
            Registry::get('EntityHandler')->create(
                'announcements',
                array(
                    'announcement_id' => 2458,
                    'assoc_id' => 4,
                    'date_posted' => '2009-08-13 10:21:44',
                    'assoc_type' => 256,
                )
            )
        );

        /*$this->assertTrue(
            $created === true &&
            Registry::get('DataMapper')->getMapping('announcements', 2458) === '2' &&
            Registry::get('DataMapper')->getMapping('announcements', 12) === '1'
        );*/
        $this->assertSame(
            '1-2-1',
            implode('-', array(
                (int) $created,
                Registry::get('DataMapper')->getMapping(
                    'announcements',
                    2458
                ),
                Registry::get('DataMapper')->getMapping(
                    'announcements',
                    12
                ),
            ))
        );

    }

    /**
     * @depends testCreateAnnouncementInDatabase
     */
    public function testUpdateFirstAnnouncementExpiryDate()
    {
        $announcement = Registry::get('EntityHandler')->create(
            'announcements',
            array(
                'announcement_id' => 12,
                'assoc_id' => 1,
                'type_id' => null,
                'date_expire' => '2019-12-14 13:12:49',
                'date_posted' => '2018-10-12 17:58:21',
                'assoc_type' => 256,
            )
        );

        $announcement->setId(
            Registry::get('DataMapper')->getMapping(
                'announcements', 
                $announcement->getId()
            )
        );

        $annBefore = Registry::get('AnnouncementsDAO')->read(array(
            'announcement_id' => Registry::get('DataMapper')->getMapping('announcements', 12),
        ))->get(0);

        $updated = $this->getStub()->callMethod(
            'updateInDatabase',
            $announcement
        );

        $annAfter = Registry::get('AnnouncementsDAO')->read(array(
            'announcement_id' => Registry::get('DataMapper')->getMapping('announcements', 12),
        ))->get(0);

        $this->assertTrue(
            $annBefore->getData('date_expire') === null &&
            $updated === true &&
            $annAfter->getData('date_expire') === '2019-12-14 13:12:49'
        );
    }

    public function testCreateGroupInTheDatabase()
    {
        Registry::get('DbHandler')->createTableIfNotExists('groups');

        $group = Registry::get('EntityHandler')->create('groups', array(
            'group_id' => 14,
            'assoc_id' => 18,
            'assoc_type' => 256,
            'publish_email' => 1,
        ));

        $created = Registry::get('EntityHandler')->createOrUpdateInDatabase(
            $group
        );

        $this->assertTrue($created);
    } 

    /**
     * @depends testCreateGroupInTheDatabase
     */
    public function testUpdateGroupInTheDatabase()
    {
        $group = Registry::get('EntityHandler')->create('groups', array(
            'group_id' => 14,
            'assoc_id' => 18,
            'assoc_type' => 256,
            'publish_email' => 0,
        ));

        $groupId = $group->getId();

        $groupBefore = Registry::get('GroupsDAO')->read(array(
            'group_id' => Registry::get('DataMapper')->getMapping(
                'groups',
                $groupId
            )
        ))->get(0);
         
        $updated = Registry::get('EntityHandler')->createOrUpdateInDatabase(
            $group
        );

        $groupAfter = Registry::get('GroupsDAO')->read(array(
            'group_id' => Registry::get('DataMapper')->getMapping(
                'groups',
                $groupId
            )
        ))->get(0);

        $this->assertTrue(
            $updated === true &&
            Registry::get('EntityHandler')->areEqual(
                $groupBefore, 
                $groupAfter
            ) === false &&
            $groupBefore->getData('publish_email') === '1' &&
            $groupAfter->getData('publish_email') === '0'
        );
    } 

    public function testCanSetTheMappedIdsOfAnEntity()
    {
        $ids = array(
            'users' => array(
                'old' => 13,
                'new' => 22,
            ),
            'journals' => array(
                'old' => 8192,
                'new' => 5,
            ),
            'sections' => array(
                'old' => 9293,
                'new' => 19,
            ),
        );

        foreach ($ids as $table => $mapping) {
            Registry::get('DataMapper')->mapData($table, $mapping);
        }

        $se = Registry::get('EntityHandler')->create('section_editors', array(
            'journal_id' => $ids['journals']['old'],
            'section_id' => $ids['sections']['old'],
            'user_id' => $ids['users']['old'],
        ));

        $seBefore = $se->cloneInstance();

        Registry::get('EntityHandler')->setMappedData($se, array(
            'users' => 'user_id',
            'journals' => 'journal_id',
            'sections' => 'section_id',
        ));

        $this->assertSame(
            '1-1-1-1-1-1-1-1',
            implode('-', array(
                (int) $this->areEqual(
                    $ids['journals']['old'],
                    $seBefore->getData('journal_id')
                ),
                (int) $this->areEqual(
                    $ids['sections']['old'],
                    $seBefore->getData('section_id')
                ),
                (int) $this->areEqual(
                    $ids['users']['old'],
                    $seBefore->getData('user_id')
                ),
                (int) $this->areEqual(
                    $se->getData('can_edit'),
                    $seBefore->getData('can_edit')
                ),
                (int) $this->areEqual(
                    $se->getData('can_review'),
                    $seBefore->getData('can_review')
                ),
                (int) $this->areEqual(
                    $ids['journals']['new'],
                    $se->getData('journal_id')
                ),
                (int) $this->areEqual(
                    $ids['sections']['new'],
                    $se->getData('section_id')
                ),
                (int) $this->areEqual(
                    $ids['users']['new'],
                    $se->getData('user_id')
                ),
            ))
        );

    }
}
