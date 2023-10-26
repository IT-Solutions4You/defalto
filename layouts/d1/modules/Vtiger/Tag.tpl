{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{strip}
    <div class="tag btn me-1 lh-base {if 'Detail' eq $VIEW}text-secondary bg-secondary bg-opacity-10{elseif $ACTIVE eq true}btn-primary active{else}text-primary bg-primary bg-opacity-10{/if}" title="{$TAG_MODEL->getName()}" data-cv-id="{$ALL_CUSTOM_VIEW_ID}" data-type="{$TAG_MODEL->getType()}" data-id="{$TAG_MODEL->getId()}">
        <i class="activeToggleIcon fa fa-tag"></i>
        <span class="tagLabel mx-2 display-inline-block text-truncate" title="{$TAG_MODEL->getName()}">{$TAG_MODEL->getName()}</span>
        {if !$NO_EDIT}
            <i class="editTag fa fa-pencil me-2"></i>
        {/if}
        {if !$NO_DELETE}
            <i class="deleteTag fa fa-times ms-2"></i>
        {/if}
    </div>
{/strip}