<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class HelpDesk_Comments_Handler extends VTEventHandler
{
    public function handleEvent($name, $data)
    {
        if (!empty($data->focus) && 'ModComments' === get_class($data->focus)) {
            $commentInfo = $data->focus->column_fields;
            $relatedTo = $commentInfo['related_to'];

            if (empty($commentInfo['is_private']) && !empty($relatedTo)) {
                /** @var HelpDesk_Record_Model $recordModel */
                $recordModel = Vtiger_Record_Model::getInstanceById($relatedTo);

                if (!empty($recordModel) && 'HelpDesk' === $recordModel->getModuleName()) {
                    $recordModel->updateCommentFields();
                }
            }
        }
    }
}