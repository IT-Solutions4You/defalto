<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'modules/WSAPP/synclib/models/BaseModel.php';

class WSAPP_PullResultModel extends WSAPP_BaseModel
{
    public function setPulledRecords($records)
    {
        return $this->set('pulledrecords', $records);
    }

    public function getPulledRecords()
    {
        return $this->get('pulledrecords');
    }

    public function setNextSyncState(WSAPP_SyncStateModel $syncStateModel)
    {
        return $this->set('nextsyncstate', $syncStateModel);
    }

    public function getNextSyncState()
    {
        return $this->get('nextsyncstate');
    }

    public function setPrevSyncState(WSAPP_SyncStateModel $syncStateModel)
    {
        return $this->set('prevsyncstate', $syncStateModel);
    }

    public function getPrevSyncState()
    {
        return $this->get('prevsyncstate');
    }
}