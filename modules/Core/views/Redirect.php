<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Redirect_View extends Vtiger_Basic_View {

    /**
     * @inheritDoc
     */
    public function isLoginRequired(): bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $mode = (string)$request->get('mode');
        $url = $this->getUrl($mode);

        if (empty($url)) {
            throw new Exception('Invalid mode');
        }

        header('Location: ' . $url);
    }

    public function getUrl(string $mode): string
    {
        return Core_Redirect_Model::getInstance()->getUrl($mode);
    }
}
