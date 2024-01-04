<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_Field_Model extends Vtiger_Field_Model
{
    /**
     * Function to get all the supported advanced filter operations
     * @return <Array>
     */
    public static function getAdvancedFilterOptions()
    {
        return array(
            'is' => 'is',
            'contains' => 'contains',
            'does not contain' => 'does not contain',
            'starts with' => 'starts with',
            'ends with' => 'ends with',
            'is empty' => 'is empty',
            'is not empty' => 'is not empty',
            'less than' => 'less than',
            'greater than' => 'greater than',
            'does not equal' => 'does not equal',
            'less than or equal to' => 'less than or equal to',
            'greater than or equal to' => 'greater than or equal to',
            'before' => 'before',
            'after' => 'after',
            'between' => 'between',
        );
    }

    /**
     * Function to get the advanced filter option names by Field type
     * @return <Array>
     */
    public static function getAdvancedFilterOpsByFieldType()
    {
        return array(
            'string' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'),
            'salutation' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'),
            'text' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'),
            'url' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'),
            'email' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'),
            'phone' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'),
            'integer' => array('equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to'),
            'double' => array('equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to'),
            'currency' => array('equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'is not empty'),
            'picklist' => array('is', 'is not', 'is empty', 'is not empty'),
            'multipicklist' => array('is', 'is not', 'contains', 'does not contain'),
            'datetime' => array(
                'is',
                'is not',
                'before',
                'after',
                'is today',
                'is tomorrow',
                'is yesterday',
                'less than hours before',
                'less than hours later',
                'more than hours before',
                'more than hours later',
                'less than days ago',
                'less than days later',
                'more than days ago',
                'more than days later',
                'days ago',
                'days later',
                'is empty',
                'is not empty'
            ),
            'time' => array('is', 'is not', 'is not empty'),
            'date' => array(
                'is',
                'is not',
                'between',
                'before',
                'after',
                'is today',
                'less than days ago',
                'more than days ago',
                'in less than',
                'in more than',
                'days ago',
                'days later',
                'is not empty',
                'more than days later',
                'in less than',
                'in more than',
                'days ago',
                'days later',
                'is empty',
                'is not empty'
            ),
            'boolean' => array('is', 'is not'),
            'reference' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'),
            'owner' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'),
            'recurrence' => array('is', 'is not'),
            'comment' => array('is'),
            'image' => array('is', 'is not', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'),
            'percentage' => array('equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'is not empty'),
            'documentsFolder' => array('is', 'contains', 'does not contain', 'starts with', 'ends with'),
        );
    }

    /**
     * Function to get comment field which will useful in creating conditions
     * @param <Vtiger_Module_Model> $moduleModel
     * @return <Vtiger_Field_Model>
     */
    public static function getCommentFieldForFilterConditions($moduleModel)
    {
        $commentField = new Vtiger_Field_Model();
        $commentField->set('name', '_VT_add_comment');
        $commentField->set('label', 'Comment');
        $commentField->setModule($moduleModel);
        $commentField->fieldDataType = 'comment';

        return $commentField;
    }

    public static function getAllForModule($moduleModel)
    {
        if (empty(self::$allFields)) {
            $fieldsList = array();
            $firstBlockFields = array('templatename' => 'LBL_TEMPLATE_NAME', 'description' => 'LBL_DESCRIPTION');
            $secondBlockFields = array('subject' => 'LBL_SUBJECT');
            $blocks = $moduleModel->getBlocks();

            foreach ($firstBlockFields as $fieldName => $fieldLabel) {
                $fieldModel = new EmailTemplates_Field_Model();
                $blockModel = $blocks['SINGLE_EmailTemplates'];
                $fieldModel->set('name', $fieldName)->set('label', $fieldLabel)->set('block', $blockModel);
                $fieldsList[$blockModel->get('id')][] = $fieldModel;

            }

            foreach ($secondBlockFields as $fieldName => $fieldLabel) {
                $fieldModel = new EmailTemplates_Field_Model();
                $blockModel = $blocks['LBL_EMAIL_TEMPLATE'];
                $fieldModel->set('name', $fieldName)->set('label', $fieldLabel)->set('block', $blockModel);
                $fieldsList[$blockModel->get('id')][] = $fieldModel;
            }
            self::$allFields = $fieldsList;
        }
        return self::$allFields;
    }

    public function isViewable()
    {
        return true;
    }

    /**
     * Function to check if the field is named field of the module
     * @return <Boolean> - True/False
     */
    public function isNameField()
    {
        return false;
    }

    /**
     * @return array
     */
    public static function getSharingTypes()
    {
        return array(
            '' => '',
            'public' => vtranslate('PUBLIC_FILTER', 'EMAILMaker'),
            'private' => vtranslate('PRIVATE_FILTER', 'EMAILMaker'),
            'share' => vtranslate('SHARE_FILTER', 'EMAILMaker'),
        );
    }

    /**
     * @return array
     */
    public static function getStatusOptions()
    {
        return array(
            'status_1' => vtranslate('Active', 'EMAILMaker'),
            'status_0' => vtranslate('Inactive', 'EMAILMaker'),
        );
    }

    /**
     * @return array
     */
    public static function getWorkflowOptions()
    {
        return array(
            'wf_1' => vtranslate('LBL_YES', 'EMAILMaker'),
            'wf_0' => vtranslate('LBL_NO', 'EMAILMaker'),
        );
    }

    /**
     * @return array
     */
    public static function getSearchTypes()
    {
        return array(
            'templatename',
            'module',
            'category',
            'description',
            'sharingtype',
            'owner',
            'status',
            'workflow',
        );
    }
}