<?php

namespace Firesphere\PartialUserforms\Controllers;

use Exception;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use Page;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use Firesphere\PartialUserforms\Forms\PasswordForm;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Dev\Debug;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\UserForms\Control\UserDefinedFormController;

/**
 * Class PartialUserFormController
 *
 * @package Firesphere\PartialUserforms\Controllers
 */
class PartialUserFormController extends UserDefinedFormController
{
    /**
     * @var array
     */
    private static $url_handlers = [
        '$Key/$Token' => 'partial',
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'partial',
    ];

    /**
     * Partial form
     *
     * @param HTTPRequest $request
     * @return HTTPResponse|DBHTMLText|void
     * @throws HTTPResponse_Exception
     * @throws Exception
     */
    public function partial(HTTPRequest $request)
    {
        /** @var PartialFormSubmission $partial */
        $partial = $this->validateToken($request);
        $record = DataObject::get_by_id($partial->UserDefinedFormClass, $partial->UserDefinedFormID);

        /** @var self $controller */
        $controller = parent::create($record);
        $controller->doInit();

        Director::set_current_page($controller->data());

        // Set the session after init and check if the last session has expired
        // or another submission has started
        $sessionID = $request->getSession()->get(PartialSubmissionController::SESSION_KEY);
        if (!$sessionID || $sessionID !== $partial->ID) {
            PartialSubmissionController::reloadSession($request->getSession(), $partial->ID);
        }

        // Verify user
        if ($controller->PasswordProtected &&
            $request->getSession()->get(PasswordForm::PASSWORD_SESSION_KEY) !== $partial->ID
        ) {
            return $this->redirect('verify');
        }

        // Lock form
        if ($this->isLockedOut()) {
            // TODO: MMS-115
            Debug::dump('This form is currently being used by someone else. Please try again in 30 minutes.');
            // Redirect to overview page
        }

        $form = $controller->Form();
        $form->loadDataFrom($partial->getFields());
        $this->populateData($form, $partial);

        // Copied from {@link UserDefinedFormController}
        if ($controller->Content && $form && !$controller->config()->disable_form_content_shortcode) {
            $hasLocation = stristr($controller->Content, '$UserDefinedForm');
            if ($hasLocation) {
                /** @see Requirements_Backend::escapeReplacement */
                $formEscapedForRegex = addcslashes($form->forTemplate(), '\\$');
                $content = preg_replace(
                    '/(<p[^>]*>)?\\$UserDefinedForm(<\\/p>)?/i',
                    $formEscapedForRegex,
                    $controller->Content
                );

                return $controller->customise([
                    'Content'     => DBField::create_field('HTMLText', $content),
                    'Form'        => '',
                    'PartialLink' => $partial->getPartialLink()
                ])->renderWith([static::class, Page::class]);
            }
        }

        return $controller->customise([
            'Content'     => DBField::create_field('HTMLText', $controller->Content),
            'Form'        => $form,
            'PartialLink' => $partial->getPartialLink()
        ])->renderWith([static::class, Page::class]);
    }

    /**
     * A little abstraction to be more readable
     *
     * @param HTTPRequest $request
     * @return PartialFormSubmission|void
     * @throws HTTPResponse_Exception
     */
    public function validateToken($request)
    {
        // Ensure this URL doesn't get picked up by HTTP caches
        HTTPCacheControlMiddleware::singleton()->disableCache();

        $key = $request->param('Key');
        $token = $request->param('Token');
        if (!$key || !$token) {
            return $this->httpError(404);
        }

        /** @var PartialFormSubmission $partial */
        $partial = PartialFormSubmission::validateKeyToken($key, $token);
        if ($partial === false) {
            return $this->httpError(404);
        }

        return $partial;
    }

    /**
     * Add partial submission and set the uploaded filenames as right title of the file fields
     *
     * @param Form $form
     * @param PartialFormSubmission $partial
     */
    protected function populateData($form, $partial)
    {
        $fields = $form->Fields();
        // Add partial submission ID
        $fields->push(
            HiddenField::create(
                'PartialID',
                null,
                $partial->ID
            )
        );

        // Populate files
        $uploads = $partial->PartialUploads()->filter([
            'UploadedFileID:not'=> 0
        ]);

        if (!$uploads->exists()) {
            return;
        }

        foreach ($uploads as $upload) {
            $fields->dataFieldByName($upload->Name)
                ->setRightTitle(
                    sprintf(
                        'Uploaded: %s (Attach a new file to replace the uploaded file)',
                        $upload->UploadedFile()->Name
                    )
                );
        }
    }

    /**
     * Checks whether this form is currently being used by someone else
     * @return bool
     * @throws \SilverStripe\ORM\ValidationException
     */
    protected function isLockedOut()
    {
        $session = $this->getRequest()->getSession();
        $submissionID = $session->get(PartialSubmissionController::SESSION_KEY);
        $partial = PartialFormSubmission::get()->byID($submissionID);
        $phpSessionID = session_id();

        // If invalid sessions or if the last session was from the same user or that the recent session has expired
        if (
            !$submissionID ||
            !$partial ||
            !$partial->PHPSessionID ||
            $phpSessionID === $partial->PHPSessionID ||
            $partial->dbObject('LockedOutUntil')->InPast()
        ) {
            PartialSubmissionController::reloadSession($session, $partial->ID);
            return false;
        }

        // Lockout when there's an ongoing session
        return $phpSessionID !== $partial->PHPSessionID && $partial->dbObject('LockedOutUntil')->InFuture();
    }
}
