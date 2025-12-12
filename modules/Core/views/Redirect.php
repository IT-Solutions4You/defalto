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

        if (!empty($url)) {
            header('Location: ' . $url);
        }

        throw new Exception('Invalid mode');
    }

    public function getUrl(string $mode): string
    {
        return match ($mode) {
            'Web' => 'https://defalto.com/',
            'Forum' => 'https://defalto.com/forum/',
            'Blog' => 'https://defalto.com/blog/',
            'Documentation' => 'https://defalto.com/docs/',
            'Videos' => 'https://www.youtube.com/@itsolutions4you',
            'Releases' => 'https://github.com/IT-Solutions4You/defalto/releases',
            'Facebook' => 'https://www.facebook.com/defalto.crm',
            'Youtube' => 'https://www.youtube.com/@itsolutions4you',
            'Migration' => 'https://defalto.com/administrator-guide/installation-guide/system-backup-database-and-files/',
            'SourceForge' => 'https://sourceforge.net/projects/defalto-crm/',
            'Requirements' => 'https://defalto.com/administrator-guide/installation-guide/system-requirements/',
            default => '',
        };
    }
}
