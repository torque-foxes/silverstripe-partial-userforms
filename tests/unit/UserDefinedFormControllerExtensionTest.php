<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\UserForms\Model\UserDefinedForm;

/**
 * Class UserDefinedFormControllerExtensionTest
 * @package Firesphere\PartialUserforms\Tests
 */
class UserDefinedFormControllerExtensionTest extends FunctionalTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures/partialformtest.yml';

    /**
     * Test that page renders normal UserForm if partial submission is disabled
     */
    public function testPartialDisabled()
    {
        $form = $this->objFromFixture(UserDefinedForm::class, 'form1');
        $form->EnablePartialSubmissions = 0;
        $form->write();
        $form->publishRecursive();

        $response = $this->get('form-1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('form-1/Form', $response->getBody());
    }

    /**
     * Test that page renders start form if partial submission is enabled
     */
    public function testPartialEnabled()
    {
        $form = $this->objFromFixture(UserDefinedForm::class, 'form1');
        $form->EnablePartialSubmissions = 1;
        $form->write();
        $form->publishRecursive();

        $response = $this->get('form-1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Start', $response->getBody());
    }

    /**
     * Test start page
     */
    public function testStart()
    {
        // Partial disabled
        $response = $this->get('form-1/start');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('form-1/Form', $response->getBody());

        // Partial enabled
        $form = $this->objFromFixture(UserDefinedForm::class, 'form1');
        $form->EnablePartialSubmissions = 1;
        $form->write();
        $form->publishRecursive();

        $response = $this->get('form-1/start');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('form-1/StartForm', $response->getBody());
    }

    /**
     * Test overview page
     */
    public function testOverview()
    {
        $this->markTestSkipped('Fix forTemplate error');

        // Partial disabled
        $response = $this->get('form-1/overview');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('form-1/Form', $response->getBody());

        // Partial enabled
        $form = $this->objFromFixture(UserDefinedForm::class, 'form1');
        $form->EnablePartialSubmissions = 1;
        $form->write();
        $form->publishRecursive();

        $response = $this->get('form-1/overview');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Go to form', $response->getBody());
    }

    /**
     * Test overview page with ongoing form session
     */
    public function testOverviewLocked()
    {
        $this->markTestSkipped('Fix forTemplate error');

        // Session within 30mins + different user = Locked
        DBDatetime::set_mock_now('2019-02-15 12:00:00');
        session_id('black');
        $partial = PartialFormSubmission::get()->byID(1);
        $partial->LockedOutUntil = '2019-02-15 12:15:00';
        $partial->PHPSessionID = 'white';
        $partial->write();

        // Partial disabled
        $response = $this->get('form-1/overview');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('form-1/Form', $response->getBody());

        // Partial enabled
        $form = $this->objFromFixture(UserDefinedForm::class, 'form1');
        $form->EnablePartialSubmissions = 1;
        $form->write();
        $form->publishRecursive();

        $response = $this->get('form-1/overview');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Form locked', $response->getBody());
    }

    /**
     * Test verify page with password enabled
     */
    public function testVerify()
    {
        // Partial and password disabled
        $partial = $form = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $form = $partial->Parent();
        $form->PasswordProtected = 1;
        $form->EnablePartialSubmissions = 0;
        $form->write();
        $form->publishRecursive();

        $response = $this->get('partial/2f27a563575293c8/q1w2e3r4t5y6u7i8');
        $this->assertEquals(302, $response->getStatusCode());

        // Partial and password enabled
        $partial = $form = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $form = $partial->Parent();
        $form->PasswordProtected = 1;
        $form->EnablePartialSubmissions = 1;
        $form->write();
        $form->publishRecursive();

        $response = $this->get("partial/2f27a563575293c8/q1w2e3r4t5y6u7i8");
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('form-1/VerifyForm', $response->getBody());
    }

    /**
     * Test verify page with password disabled
     */
    public function testVerifyPasswordDisabled()
    {
        // Partial and password disabled
        $partial = $form = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $form = $partial->Parent();
        $form->PasswordProtected = 0;
        $form->EnablePartialSubmissions = 0;
        $form->write();
        $form->publishRecursive();

        $response = $this->get('partial/2f27a563575293c8/q1w2e3r4t5y6u7i8');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('form-1/Form', $response->getBody());

        // Partial enabled and password disabled
        $partial = $form = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $form = $partial->Parent();
        $form->EnablePartialSubmissions = 1;
        $form->PasswordProtected = 0;
        $form->write();
        $form->publishRecursive();

        $response = $this->get("partial/2f27a563575293c8/q1w2e3r4t5y6u7i8");
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('form-1/Form', $response->getBody());
    }

    public function setUp()
    {
        parent::setUp();
        $this->objFromFixture(UserDefinedForm::class, 'form1')->publishRecursive();

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
