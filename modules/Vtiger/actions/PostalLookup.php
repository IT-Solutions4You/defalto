<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Address auto-completion endpoint: looks up the local postal-code dataset and returns
 * matching place/PSČ (+ region + country) for what the user is typing into an address
 * field. Bidirectional — search `by` 'zip' or 'city'.
 *
 * Lives on the Vtiger fallback module so any module's `action=PostalLookup` resolves
 * here. Intentionally available to ANY logged-in user (postal codes are public
 * reference data): it requires login + a valid CSRF token (validateReadAccess) but no
 * module/record privilege, so it is NOT admin-only and works from every edit form.
 */
class Vtiger_PostalLookup_Action extends Core_Controller_Action
{
    /**
     * @inheritDoc — no extra privilege beyond being a logged-in user.
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [];
    }

    public function process(Vtiger_Request $request)
    {
        $records = Core_PostalCode_Model::getInstance()->search(
            (string)$request->get('country'),
            (string)$request->get('term'),
            (string)$request->get('by'),
            (int)$request->get('limit')
        );

        $response = new Vtiger_Response();
        $response->setResult(['records' => $records]);
        $response->emit();
    }
}
