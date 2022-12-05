{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
    <div class="col-lg-6 col-md-6 col-sm-6">
        <div class="record-header clearfix">
            {if !$MODULE}
                {assign var=MODULE value=$MODULE_NAME}
            {/if}
            <div class="hidden-sm hidden-xs recordImage bg_{$MODULE} app-{$SELECTED_MENU_CATEGORY}">
                <div class="name">
                    <span><strong><i class="vicon-{strtolower($MODULE)}"></i></strong></span>
                </div>
            </div>
            <div class="recordBasicInfo">
                <div class="info-row">
                    <h4>
						<span class="recordLabel pushDown" title="{$RECORD->getName()}">
							<span>{$RECORD->getName()}</span>&nbsp;
						</span>
                    </h4>
                </div>
                <div class="info-row">
                    <span class="modulename_label">{vtranslate('LBL_MODULENAMES',$MODULE)}:</span>
                    &nbsp;{vtranslate($RECORD->get('module'),$RECORD->get('module'))}
                </div>
            </div>
        </div>
    </div>
{/strip}