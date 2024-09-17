<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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