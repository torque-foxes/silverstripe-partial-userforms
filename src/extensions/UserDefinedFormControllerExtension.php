<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Core\Extension;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\View\Requirements;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

/**
 * Class UserDefinedFormControllerExtension
 *
 * @package Firesphere\PartialUserforms\Extensions
 * @property UserDefinedFormController|UserDefinedFormControllerExtension $owner
 */
class UserDefinedFormControllerExtension extends Extension
{
    /**
     * Add required javascripts
     */
    public function onBeforeInit()
    {
        Requirements::javascript('firesphere/partialuserforms:client/dist/main.js');
    }

    /**
     * This action handles rendering the "finished" message, which is
     * customizable by editing the ReceivedFormSubmission template.
     * 
     * This is exact copy of Control/UserDefinedFormController.php 
     * only change with function from call to function renderSubmittedData 
     *
     * @return ViewableData
     */
    public function finished()
    {
        $submission = $this->getRequest()->getSession()->get('userformssubmission'. $this->ID);

        if ($submission) {
            $submission = SubmittedForm::get()->byId($submission);
        }

        $referrer = isset($_GET['referrer']) ? urldecode($_GET['referrer']) : null;

        if (!$this->DisableAuthenicatedFinishAction) {
            $formProcessed = $this->getRequest()->getSession()->get('FormProcessed');

            if (!isset($formProcessed)) {
                return $this->redirect($this->Link() . $referrer);
            } else {
                $securityID = $this->getRequest()->getSession()->get('SecurityID');
                // make sure the session matches the SecurityID and is not left over from another form
                if ($formProcessed != $securityID) {
                    // they may have disabled tokens on the form
                    $securityID = md5($this->getRequest()->getSession()->get('FormProcessedNum'));
                    if ($formProcessed != $securityID) {
                        return $this->redirect($this->Link() . $referrer);
                    }
                }
            }

            $this->getRequest()->getSession()->clear('FormProcessed');
        }

        $data = [
            'Submission' => $submission,
            'Link' => $referrer
        ];

        $this->extend('updateReceivedFormSubmissionData', $data);

        return $this->customise([
            'Content' => $this->customise($data)->renderWith(__CLASS__ . '_ReceivedFormSubmission'),
            'Form' => '',
        ]);
    }

    public function renderSubmittedData() {
        // Ignore following form fields as they are not part of submission
        $ignoreFields = [
            "SilverStripe\SpamProtection\EditableSpamProtectionField",
            "SilverStripe\UserForms\Model\EditableFormField\EditableFormHeading",
            "SilverStripe\UserForms\Model\EditableFormField\EditableLiteralField",
            "SilverStripe\UserForms\Model\EditableFormField\EditableMemberListField",
        ];
        $formFields = EditableFormField::get()
            ->where(['ParentID' => 1527])
            ->exclude(['ClassName' => $ignoreFields])
            ->sort('Sort');

        $stepsList = new ArrayList();
        $fieldsList = new ArrayList();
        $groupsList = new ArrayList();

        $_fields = 0;
        $groupTitle = '';

        foreach ($formFields as $formField) {
            if ($formField->ClassName == "SilverStripe\UserForms\Model\EditableFormField\EditableFormStep") {
                // Store title so when new step is encountered this title will be saved
                // For first step do not add data just continue store title in currentTitle
                if ($_fields == 0) {
                    $stepTitle = $formField->Title ?? "Step Generic Title";
                    $_fields++;
                    continue;
                }

                if ($fieldsList->count() != 0) {
                    $groupsList->add([
                        'FieldGroupTitle' => 'Fields Generic',
                        'FieldsList' => $fieldsList
                    ]);
                    $fieldsList = new ArrayList();
                }

                $stepsList->add([
                    'StepTitle' => $stepTitle,
                    'FieldGroup' => $groupsList,
                ]);
                $stepTitle = $formField->Title ?? "Section Generic";
                $groupsList = new ArrayList();
                continue;
            }

            if ($formField->ClassName == "SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup") {
                // New group start detected - Store values of previous fields as ungrouped data
                if ($fieldsList->Count()) {
                    $groupsList->add([
                        'FieldGroupTitle' => 'Group Generic',
                        'FieldsList' => $fieldsList
                    ]);
                }
                $groupTitle = $formField->Title ?? "Group Generic";
                $fieldsList = new ArrayList();
                continue;
            }
            if ($formField->ClassName == "SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroupEnd") {
                $groupsList->add([
                    'FieldGroupTitle' => $groupTitle,
                    'FieldsList' => $fieldsList
                ]);
                $fieldsList = new ArrayList();
                continue;
            }

            // Process the current field
            $value = SubmittedFormField::get()->where(['ParentID' => 2531, 'Name' => $formField->Name])->first();

            if ($formField->ClassName == "SilverStripe\UserForms\Model\EditableFormField\EditableFileField") {
                $fieldsList->add([
                    'FieldTitle' => $formField->Title ?? "Field Generic",
                    'FieldValue' => $value->Value ?? 'File uploaded',
                    'FieldClassName' => $formField->ClassName,
                ]);
                $_fields++;
                continue;
            }
            if ($value) {
                $fieldsList->add([
                    'FieldTitle' => $formField->Title ?? "Field Generic",
                    'FieldValue' => $value->Value,
                    'FieldClassName' => $formField->ClassName,
                ]);
                $_fields++;
                continue;
            }
        }

        // Finalise data with remaning group values
        if ($fieldsList->count() != 0) {
            $groupsList->add([
                'FieldGroupTitle' => '-NON GROUPED DATA-',
                'FieldsList' => $fieldsList
            ]);
            $fieldsList = new ArrayList();
        }

        $stepsList->add([
            'StepTitle' => $stepTitle,
            'FieldGroup' => $groupsList,
        ]);

        $data = [];

        return $this->customise(new ArrayData([
            'Title' => 'HTML Action',
            'Content' => $data,
            'Submission' => $stepsList,
        ]))->renderWith(__CLASS__ . '_ReceivedFormSubmission');
    }
}
