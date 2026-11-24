<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
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
