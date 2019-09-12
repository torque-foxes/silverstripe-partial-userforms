<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Assets\File;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\UserForms\Model\UserDefinedForm;

/**
 * Class PartialUserFormControllerTest
 * @package Firesphere\PartialUserforms\Tests
 */
class PartialUserFormControllerTest extends FunctionalTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures/partialformtest.yml';

    public function testPartialPage()
    {
        $result = $this->get("partial");
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testPartialValidKeyToken()
    {
        $token = 'q1w2e3r4t5y6u7i8';
        // No Parent
        $key = singleton(PartialFormSubmission::class)->generateKey($token);
        $result = $this->get("partial/{$key}/{$token}");
        $this->assertEquals(404, $result->getStatusCode());

        // Partial with UserDefinedForm
        $key = $this->objFromFixture(PartialFormSubmission::class, 'submission1')->generateKey($token);
        $result = $this->get("partial/{$key}/{$token}");
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertContains('Field 1', $result->getBody());
    }

    public function testPartialInvalidToken()
    {
        $token = 'abcdef';
        $key = singleton(PartialFormSubmission::class)->generateKey($token);

        $result = $this->get("partial/{$key}/{$token}");
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testPartialInvalidKey()
    {
        $token = 'e6b27462211e1711';
        $key = 'abcdef';

        $result = $this->get("partial/{$key}/{$token}");
        $this->assertEquals(404, $result->getStatusCode());

        $token = 'qwerty';
        $key = 'abcdef';

        $result = $this->get("partial/{$key}/{$token}");
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testDataPopulated()
    {
        $partial = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $key = $partial->generateKey($partial->Token);

        $response = $this->get("/partial/{$key}/{$partial->Token}");
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertCount(1, $partial->PartialUploads());
        $this->assertCount(3, $partial->PartialFields());

        $content = $response->getBody();
        $this->assertContains('I have a question', $content);
        $this->assertContains('Hans-fullsize-sqr.png', $content);
    }

    public function testPasswordProtectedPartial()
    {
        // Partial with UserDefinedForm
        $submission = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        /** @var UserDefinedForm $parent */
        $parent = $submission->Parent();
        $parent->EnablePartialSubmissions = true;
        $parent->PasswordProtected = true;
        $parent->write();
        $parent->publishRecursive();

        $key = $submission->generateKey($submission->Token);
        $result = $this->get("partial/{$key}/{$submission->Token}");
        // Be redirected to the Password form
        $formOpeningTag = '<form id="PasswordForm_getForm" action="/form-1/VerifyForm" method="post" enctype="application/x-www-form-urlencoded" class="userform">';
        $this->assertContains($formOpeningTag, $result->getBody());
    }

    public function testIsLockedOut()
    {
        $partial = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $page = $partial->Parent();
        $page->EnablePartialSubmissions = true;
        $page->write();
        $page->publishRecursive();
        $this->session()->set(PartialSubmissionController::SESSION_KEY, $partial->ID);

        // No session recorded = not locked
        $response = $this->get($page->Link('overview'));
        $this->assertNull($partial->LockedOutUntil);
        $this->assertNull($partial->PHPSessionID);
        $this->assertNotContains('This form is currently being used by someone else', $response->getBody());

        // Session in the past + same user = Not Locked
        DBDatetime::set_mock_now('2019-02-15 11:00:00');
        session_id('white');
        $partial->LockedOutUntil = '2019-02-15 09:00:00';
        $partial->PHPSessionID = 'white';
        $partial->write();

        $response = $this->get($page->Link('overview'));
        $this->assertNotContains('This form is currently being used by someone else', $response->getBody());

        // Session in the past + different user = Not Locked
        DBDatetime::set_mock_now('2019-02-15 11:00:00');
        session_id('red');
        $partial = PartialFormSubmission::get()->byID($partial->ID); // update the latest partial
        $partial->LockedOutUntil = '2019-02-15 09:00:00';
        $partial->PHPSessionID = 'white';
        $partial->write();

        $response = $this->get($page->Link('overview'));
        $this->assertNotContains('This form is currently being used by someone else', $response->getBody());

        // Session within 30mins + same user = Not Locked
        DBDatetime::set_mock_now('2019-02-15 12:00:00');
        session_id('blue');
        $partial = PartialFormSubmission::get()->byID($partial->ID); // update the latest partial
        $partial->LockedOutUntil = '2019-02-15 12:15:00';
        $partial->PHPSessionID = 'blue';
        $partial->write();

        $response = $this->get($page->Link('overview'));
        $this->assertNotContains('This form is currently being used by someone else', $response->getBody());

        // Session within 30mins + different user = Locked
        DBDatetime::set_mock_now('2019-02-15 12:00:00');
        session_id('black');
        $partial = PartialFormSubmission::get()->byID($partial->ID); // update the latest partial
        $partial->LockedOutUntil = '2019-02-15 12:15:00';
        $partial->PHPSessionID = 'white';
        $partial->write();

        $response = $this->get($page->Link('overview'));
        $this->assertContains('This form is currently being used by someone else', $response->getBody());
    }

    public function testRequirements()
    {
        $response = $this->get("partial/2f27a563575293c8/q1w2e3r4t5y6u7i8");
        $this->assertContains('/client/dist/main.js', $response->getBody());
    }

    public function setUp()
    {
        parent::setUp();
        $this->objFromFixture(UserDefinedForm::class, 'form1')->publishRecursive();
        $this->objFromFixture(File::class, 'file1')->publishRecursive();
    }

    public function tearDown()
    {
        if (session_id()) {
            // Set the session back to empty string to prevent destroying uninitialized session
            session_id('');
        }
        parent::tearDown();
    }
}
