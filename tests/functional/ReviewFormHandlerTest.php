<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\ReviewFormHandler;

// interfaces
use BeAmado\OjsMigrator\StubInterface;

// traits
use BeAmado\OjsMigrator\TestStub;

// mocks
use BeAmado\OjsMigrator\JournalMock;
use BeAmado\OjsMigrator\ReviewFormMock;

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
}
