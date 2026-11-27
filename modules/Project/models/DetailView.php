<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_DetailView_Model extends Vtiger_DetailView_Model
{
    public array $skipDetailLinkByLabel = [];

    /**
     * Function to get the detail view related links
     * @return <array> - list of links parameters
     */
    public function getDetailViewRelatedLinks()
    {
        $relatedLinks = parent::getDetailViewRelatedLinks();
        $registeredLinks = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['DETAILVIEWTAB'], [
            'MODULE' => $this->getModuleName(),
            'RECORD' => $this->getRecord()->getId(),
        ]);

        foreach ($registeredLinks['DETAILVIEWTAB'] ?? [] as $link) {
            $relatedLinks[] = get_object_vars($link);
        }

        return $relatedLinks;
    }
}
