<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_AssigneeSelect_Model extends Vtiger_Base_Model
{
    public function getOptions(string $moduleName): array
    {
        $user = Users_Record_Model::getCurrentUserModel();
        $options = ['users' => [], 'groups' => [], 'current_user' => (string)$user->getId()];

        if (!Users_Privileges_Model::isPermitted($moduleName, 'DetailView')) {
            return $options;
        }

        $owners = ['users' => $user->getAccessibleUsersForModule($moduleName),
            'groups' => $user->getAccessibleGroupForModule($moduleName)];

        foreach ($owners as $type => $values) {
            foreach ($values as $id => $label) {
                $owner = new Users_Record_Model(['id' => (int)$id]);
                $options[$type][] = ['id' => (string)$id, 'label' => decode_html((string)$label),
                    'image' => $type === 'users' ? $owner->getImageUrl() : ''];
            }
        }

        return $options;
    }
}
