<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\ReviewFormHandler;

// interfaces
use BeAmado\OjsMigrator\StubInterface;

// traits
use BeAmado\OjsMigrator\TestStub;

// mocks
use BeAmado\OjsMigrator\ReviewFormMock;
use BeAmado\OjsMigrator\JournalMock;

class ReviewFormHandlerTest extends FunctionalTest implements StubInterface
{
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::useTables(array(
            'journals',
            'review_forms',
            'review_form_settings',
            'review_form_elements',
            'review_form_element_settings',
        ));

        self::setUpTestJournal();
    }

    public function getStub()
    {
        return new class extends ReviewFormHandler {
            use TestStub;
        };
    }

    protected function createFirstReviewForm()
    {
        return $this->getStub()->create(
            (new ReviewFormMock())->getFirstReviewForm()
        );
    }

    protected function createSecondReviewForm()
    {
        return $this->getStub()->create(
            (new ReviewFormMock())->getSecondReviewForm()
        );
    }

    public function testCanCreateTheMockedReviewForms()
    {
        $first = $this->createFirstReviewForm();
        $second = $this->createSecondReviewForm();

        $testJournal = Registry::get('JournalsDAO')->read(array(
            'path' => 'test_journal',
        ))->get(0);

        $this->assertSame(
            '1-1-1-1-1-1',
            implode('-', array(
                (int) $this->areEqual(
                    $testJournal->getId(),
                    Registry::get('DataMapper')->getMapping(
                        'journals',
                        $first->getData('assoc_id')
                    )
                ),
                (int) $this->areEqual(
                    $testJournal->getId(),
                    Registry::get('DataMapper')->getMapping(
                        'journals',
                        $second->getData('assoc_id')
                    )
                ),
                (int) ($first->get('settings')->length() == 2),
                (int) ($second->get('settings')->length() == 1),
                (int) ($first->get('elements')->length() == 2),
                (int) ($second->get('elements')->length() == 2),
            ))
        );
    }

    public function testCanRegisterTheFirstReviewForm()
    {
        $first = $this->createFirstReviewForm();

        $registered = $this->getStub()->callMethod(
            'registerReviewForm',
            $first
        );

        $fromDb = Registry::get('ReviewFormsDAO')->read(array(
            'review_form_id' => Registry::get('DataMapper')->getMapping(
                'review_forms',
                $first->getId()
            )
        ));

        $reviewForm = $fromDb->get(0);

        $testJournal = Registry::get('JournalsDAO')->read(array(
            'path' => 'test_journal')
        )->get(0);

        $this->assertSame(
            '1-1-1-1',
            implode('-', array(
                (int) $registered,
                (int) $fromDb->length() == 1,
                (int) $this->areEqual(
                    $testJournal->getId(),
                    $reviewForm->getData('assoc_id')
                ),
                (int) Registry::get('EntityHandler')->areEqual(
                    $reviewForm,
                    $first,
                    array('assoc_id') // disconsider the assoc_id when comparing
                )
            ))
        );
    }

    /**
     * @depends testCanRegisterTheFirstReviewForm
     */
    public function testCanImportASettingOfTheFirstReviewForm()
    {
        $setting = $this->createFirstReviewForm()->get('settings')->get(0);

        $imported = $this->getStub()->callMethod(
            'importReviewFormSetting',
            $setting
        );

        $fromDb = Registry::get('ReviewFormSettingsDAO')->read(array(
            'review_form_id' => Registry::get('DataMapper')->getMapping(
                'review_forms',
                $setting->get('review_form_id')->getValue()
            )
        ));

        $this->assertSame(
            '1-1-1-1',
            implode('-', array(
                (int) $imported,
                (int) $this->areEqual(
                    1, 
                    $fromDb->length()
                ),
                (int) $this->areEqual(
                    Registry::get('DataMapper')->getMapping(
                        'review_forms',
                        $setting->get('review_form_id')->getValue()
                    ),
                    $fromDb->get(0)->getData('review_form_id')
                ),
                (int) Registry::get('EntityHandler')->areEqual(
                    $setting,
                    $fromDb->get(0),
                    array('review_form_id')
                ),
            ))
        );
    }

    /*
     * @depends testCanRegisterTheFirstReviewForm
     */
    public function testCanImportAnElementOfTheFirstReviewForm()
    {
        $element = $this->createFirstReviewForm()->get('elements')->get(0);

        $imported = $this->getStub()->callMethod(
            'importReviewFormElement',
            $element
        );

        $fromDb = Registry::get('ReviewFormElementsDAO')->read(array(
            'review_form_element_id' => Registry::get('DataMapper')->getMapping(
                'review_form_elements',
                $element->get('review_form_element_id')->getValue()
            )
        ));

        $settings = Registry::get('ReviewFormElementSettingsDAO')->read(array(
            'review_form_element_id' => Registry::get('DataMapper')->getMapping(
                'review_form_elements',
                $element->get('review_form_element_id')->getValue()
            )
        ));

        $this->assertSame(
            '1-1-1-1-1-1',
            implode('-', array(
                (int) $imported,
                (int) $this->areEqual(1, $fromDb->length()),
                (int) $this->areEqual(
                    Registry::get('DataMapper')->getMapping(
                        'review_form_elements',
                        $element->get('review_form_element_id')->getValue()
                    ),
                    $fromDb->get(0)->getId()
                ),
                (int) $this->areEqual(
                    Registry::get('DataMapper')->getMapping(
                        'review_forms',
                        $element->get('review_form_id')->getValue()
                    ),
                    $fromDb->get(0)->getData('review_form_id')
                ),
                (int) Registry::get('EntityHandler')->areEqual(
                    $element,
                    $fromDb->get(0),
                    array('review_form_id') // not compare the review_form_id
                ),
                (int) Registry::get('EntityHandler')->areEqual(
                    $element->get('settings')->get(0),
                    $settings->get(0),
                    array('review_form_element_id')
                ),
            ))
        );
    }

    /**
     * @depends testCanImportASettingOfTheFirstReviewForm
     * @depends testCanImportAnElementOfTheFirstReviewForm
     */
    public function testCanImportTheFirstReviewForm()
    {
        $rev1 = $this->createFirstReviewForm();

        $reviewForm1Id = Registry::get('DataMapper')->getMapping(
            'review_forms',
            $rev1->getId()
        );

        $cond = array('review_form_id' => $reviewForm1Id);

        $imported = Registry::get('ReviewFormHandler')->importReviewForm($rev1);

        $revFromDb = Registry::get('ReviewFormsDAO')->read($cond);

        $settingsFromDb = Registry::get('ReviewFormSettingsDAO')->read($cond);

        $elementsFromDb = Registry::get('ReviewFormElementsDAO')->read($cond);

        $this->assertSame(
            '1-1-1-1',
            implode('-', array(
                (int) $imported,
                (int) $this->areEqual(1, $revFromDb->length()),
                (int) $this->areEqual(2, $settingsFromDb->length()),
                (int) $this->areEqual(2, $elementsFromDb->length()),
            ))
        );
    }

    /**
     * @depends testCanImportTheFirstReviewForm
     */
    public function testCanImportTheSecondReviewForm()
    {
        $rev2 = $this->createSecondReviewForm();
        $imported = Registry::get('ReviewFormHandler')->importReviewForm($rev2);

        $reviewForm2Id = Registry::get('DataMapper')->getMapping(
            'review_forms',
            $rev2->getId()
        );

        $cond = array('review_form_id' => $reviewForm2Id);

        $revFromDb = Registry::get('ReviewFormsDAO')->read($cond);

        $settingsFromDb = Registry::get('ReviewFormSettingsDAO')->read($cond);

        $elementsFromDb = Registry::get('ReviewFormElementsDAO')->read($cond);

        $this->assertSame(
            '1-1-1-1',
            implode('-', array(
                (int) $imported,
                (int) $this->areEqual(1, $revFromDb->length()),
                (int) $this->areEqual(1, $settingsFromDb->length()),
                (int) $this->areEqual(2, $elementsFromDb->length()),
            ))
        );
    }

    protected function updateWithTheMappedIds($data)
    {
        if (!\is_a($data, \BeAmado\OjsMigrator\MyObject::class))
            return;

        if ($data->hasAttribute('review_form_id'))
            $data->set(
                'review_form_id',
                Registry::get('DataMapper')->getMapping(
                    'review_forms',
                    $data->get('review_form_id')->getValue()
                )
            );

        if ($data->hasAttribute('review_form_element_id'))
            $data->set(
                'review_form_element_id',
                Registry::get('DataMapper')->getMapping(
                    'review_form_elements',
                    $data->get('review_form_element_id')->getValue()
                )
            );
    }

    protected function updateReviewFormWithMappedIds($reviewForm)
    {
        $this->updateWithTheMappedIds($reviewForm);

        if ($reviewForm->hasAttribute('settings'))
            $reviewForm->get('settings')->forEachValue(function($setting) {
                $this->updateWithTheMappedIds($setting);
            });

        if (!$reviewForm->hasAttribute('elements'))
            return;
        
        $reviewForm->get('elements')->forEachValue(function($element) {
            $this->updateWithTheMappedIds($element);

            $element->get('settings')->forEachValue(function($setting) {
                $this->updateWithTheMappedIds($setting);
            });
        });
    }

    /**
     * @depends testCanImportTheSecondReviewForm
     */
    public function testCanGetTheSettingsOfTheSecondReviewForm()
    {
        $rev2 = $this->createSecondReviewForm();
        $this->updateReviewFormWithMappedIds($rev2);
        $settings = $this->getStub()->callMethod(
            'getReviewFormSettings',
            $rev2
        );

        $this->assertSame(
            '1-1-1',
            implode('-', array(
                (int) $this->areEqual(1, $settings->length()),
                (int) $this->areEqual(
                    $settings->get(0)->getData('setting_value'),
                    $rev2->get('settings')->get(0)->get('setting_value')
                                                  ->getValue()
                ),
                (int) $this->areEqual(
                    $settings->get(0)->getData('setting_name'),
                    $rev2->get('settings')->get(0)->get('setting_name')
                                                  ->getValue()
                ),
            ))
        );
    }

    /**
     * @depends testCanImportTheSecondReviewForm
     */
    public function testCanGetTheElementsOfTheSecondReviewForm()
    {
        $rev2 = $this->createSecondReviewForm();
        $this->updateReviewFormWithMappedIds($rev2);

        $elements = $this->getStub()->callMethod(
            'getReviewFormElements',
            $rev2
        );

        $rev2ElementsArr = array();
        $rev2ElementSettingsArr = array();
        foreach ($rev2->get('elements')->toArray() as $elementArr) {
            $rev2ElementSettingsArr[] = $elementArr['settings'];
            unset($elementArr['settings']);
            $rev2ElementsArr[] = $elementArr;
        }

        $elementsArr = array();
        $elementSettingsArr = array();
        foreach ($elements->toArray() as $elArr) {
            $elementSettingsArr[] = $elArr['settings'];
            unset($elArr['settings']);
            $elementsArr[] = $elArr;
        }

        $this->assertSame(
            '1-1-1',
            implode('-', array(
                (int) $this->areEqual(2, $elements->length()),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $elementsArr,
                    $rev2ElementsArr
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $elementSettingsArr,
                    $rev2ElementSettingsArr
                ),
            ))
        );
    }

    public function testCanImportTheReviewFormsOfTheTestJournal()
    {
        $jnl = Registry::get('JournalsDAO')->read(array(
            'path' => (new JournalMock())->getTestJournal()->get('path')
                                                           ->getValue()
        ))->get(0);

        Registry::get('ReviewFormHandler')->exportReviewFormsFromJournal($jnl);
        $dir = Registry::get('EntityHandler')->getEntityDataDir('review_forms');

        $content = Registry::get('FileSystemManager')->listdir($dir);

        $filenameRev1 = $dir . \BeAmado\OjsMigrator\DIR_SEPARATOR . '1.json';
        $filenameRev2 = $dir . \BeAmado\OjsMigrator\DIR_SEPARATOR . '2.json';

        $rev1 = Registry::get('JsonHandler')->createFromFile($filenameRev1);
        $rev2 = Registry::get('JsonHandler')->createFromFile($filenameRev2);

        $this->assertSame(
            '1-1-1-1-1',
            implode('-', array(
                (int) Registry::get('ArrayHandler')->equals(
                    $content,
                    array($filenameRev1, $filenameRev2)
                ),
                (int) $this->areEqual($rev1->get('settings')->length(), 2),
                (int) $this->areEqual($rev1->get('elements')->length(), 2),
                (int) $this->areEqual($rev2->get('settings')->length(), 1),
                (int) $this->areEqual($rev2->get('elements')->length(), 2),
            ))
        );
    }
}
