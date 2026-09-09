<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting extends CRMEntity
{
    public string $moduleVersion = '1.2';
    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = [
        'df_reportingcf',
        'reportingid',
    ];
    /**
     * Used in class functions of CRMEntity
     */
    public string $moduleName = 'Reporting';
    public string $parentName = 'ANALYTICS';
    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    public $tab_name = [
        'vtiger_crmentity',
        'df_reporting',
        'df_reportingcf',
    ];
    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    public $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'df_reporting'     => 'reportingid',
        'df_reportingcf'   => 'reportingid',
    ];
    public $table_index = 'reportingid';
    public $table_name = 'df_reporting';
    public $def_basicsearch_col = 'report_name';

    /**
     * @inheritDoc
     */
    public function save_module(string $module)
    {
        $this->saveSharing();
    }

    public function saveSharing(): void
    {
        if (empty($this->id)) {
            return;
        }

        $sharingType = $this->getSharingType();
        $viewMembers = match ($sharingType) {
            'all' => [$this->getAllUsersSharingMember()],
            'selected' => $this->getSelectedSharingMembers(),
            default => [],
        };
        $sharingModel = new Core_SharingRecord_Model();

        $sharingModel->setRecordId((int)$this->id);
        $sharingModel->setMemberViewList($viewMembers);
        $sharingModel->setMemberEditList([]);
        $sharingModel->save();
    }

    protected function getSharingType(): string
    {
        $sharingType = strtolower(trim((string)($this->column_fields['sharing_type'] ?? '')));

        if (in_array($sharingType, ['all', 'private', 'selected'], true)) {
            return $sharingType;
        }

        return empty($this->column_fields['sharing']) ? 'private' : 'selected';
    }

    protected function getAllUsersSharingMember(): string
    {
        $baseRole = Settings_Roles_Record_Model::getBaseRole();

        if (!$baseRole) {
            throw new RuntimeException('The base role required for sharing the report with all users is missing.');
        }

        return Settings_Groups_Member_Model::getQualifiedId(
            Settings_Groups_Member_Model::MEMBER_TYPE_ROLE_AND_SUBORDINATES,
            $baseRole->getId(),
        );
    }

    protected function getSelectedSharingMembers(): array
    {
        $selectedMembers = array_filter(explode(' |##| ', (string)($this->column_fields['sharing'] ?? '')));
        $availableMembers = [];
        $sharingModel = new Core_SharingRecord_Model();

        foreach ($sharingModel->getMembersOptions() as $members) {
            $availableMembers = array_merge($availableMembers, array_keys($members));
        }

        return array_values(array_unique(array_intersect($selectedMembers, $availableMembers)));
    }
}
