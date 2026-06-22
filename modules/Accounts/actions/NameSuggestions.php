<?php
/**
 * This file is part of Defalto - a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Accounts_NameSuggestions_Action extends Core_Controller_Action
{
    private const MINIMUM_SEARCH_LENGTH = 3;
    private const SUGGESTION_LIMIT = 10;

    /**
     * Escape wildcard characters before using the search value in a SQL LIKE condition.
     *
     * @param string $value Raw LIKE value.
     *
     * @return string LIKE-safe value with escaped wildcards.
     */
    private function escapeLikeValue(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value
        );
    }

    /**
     * Return accounts whose names contain the searched value, bypassing record access filters.
     *
     * @param string $accountName Normalized account name fragment.
     * @param int $recordId Current account id to exclude from edit forms.
     *
     * @return array<int,array<string,mixed>> Matching account records.
     */
    private function getMatchingAccounts(string $accountName, int $recordId): array
    {
        $db = PearDatabase::getInstance();
        $query = "
            SELECT vtiger_account.accountid, vtiger_account.accountname
            FROM vtiger_account
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid
            WHERE vtiger_crmentity.deleted = 0
                AND vtiger_account.accountname LIKE ? ESCAPE '\\\\'
        ";
        $params = [
            '%' . $this->escapeLikeValue($accountName) . '%',
        ];

        if ($recordId > 0) {
            $query .= ' AND vtiger_account.accountid != ?';
            $params[] = $recordId;
        }

        $query .= '
            ORDER BY
                CASE WHEN LOWER(vtiger_account.accountname) = LOWER(?) THEN 0 ELSE 1 END,
                vtiger_account.accountname ASC
            LIMIT ?
        ';
        $params[] = $accountName;
        $params[] = self::SUGGESTION_LIMIT;

        $result = $db->pquery($query, $params);
        $accounts = [];

        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $accounts[] = [
                'id' => (int)$db->query_result($result, $i, 'accountid'),
                'name' => decode_html((string)$db->query_result($result, $i, 'accountname')),
            ];
        }

        return $accounts;
    }

    /**
     * Normalize a user-entered account name for duplicate hint searching.
     *
     * @param string $accountName Raw account name from request.
     *
     * @return string Trimmed account name with collapsed whitespace.
     */
    private function normalizeSearchValue(string $accountName): string
    {
        return trim(preg_replace('/\s+/', ' ', $accountName) ?? '');
    }

    private function renderSuggestions(array $records): string
    {
        if (empty($records)) {
            return '';
        }

        $viewer = Vtiger_Viewer::getInstance();
        $viewer->assign('MODULE', 'Accounts');
        $viewer->assign('RECORDS', $records);

        return (string)$viewer->view('NameSuggestions.tpl', 'Accounts', true);
    }

    /**
     * Return permission checks required before account name suggestions can be requested.
     *
     * @param Vtiger_Request $request Current request context.
     *
     * @return array Permission definitions evaluated by the controller.
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'EditView'];

        return $permissions;
    }

    /**
     * Validate account name suggestion requests as read-only Ajax requests.
     *
     * @param Vtiger_Request $request Current request context.
     *
     * @return bool True when the request is valid.
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateReadAccess();
    }

    /**
     * Search existing non-deleted accounts by name and return passive duplicate hints.
     *
     * @param Vtiger_Request $request Current request context.
     *
     * @return void
     */
    public function process(Vtiger_Request $request): void
    {
        $response = new Vtiger_Response();
        $accountName = $this->normalizeSearchValue((string)$request->get('accountname'));
        $recordId = (int)$request->get('record');

        if (strlen($accountName) < self::MINIMUM_SEARCH_LENGTH) {
            $response->setResult([
                'records' => [],
            ]);
            $response->emit();

            return;
        }

        $records = $this->getMatchingAccounts($accountName, $recordId);

        $response->setResult([
            'records' => $records,
            'html' => $this->renderSuggestions($records),
        ]);
        $response->emit();
    }
}
