{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}

{if $FOLDERS}
    {assign var=INBOX_ADDED value=0}
    {assign var=TRASH_ADDED value=0}
    <ul class="nav nav-pills flex-column">
        {foreach item=FOLDER from=$FOLDERS}
            {if stripos($FOLDER->name(), 'inbox') !== false && $INBOX_ADDED == 0}
                {assign var=INBOX_ADDED value=1}
                {assign var=INBOX_FOLDER value=$FOLDER->name()}
                <li class="tab-item nav-link fs-6 mm_folder mmMainFolder active d-flex align-items-center justify-content-between" data-foldername="{$FOLDER->name()}">
                    <div>
                        <i class="fa fa-inbox fs-3"></i>
                        <b class="mx-2">{vtranslate('LBL_INBOX', $MODULE)}</b>
                    </div>
                    <div class="badge rounded-pill text-bg-primary mmUnreadCountBadge {if !$FOLDER->unreadCount()}hide{/if}">
                       {$FOLDER->unreadCount()} 
                    </div>
                </li>
                <li class="tab-item nav-link fs-6 mm_folder mmMainFolder" data-foldername="vt_drafts">
                    <div>
                        <i class="fa fa-floppy-o fs-3"></i>
                        <b class="mx-2">{vtranslate('LBL_Drafts', $MODULE)}</b>
                    </div>
                </li>
            {/if}
        {/foreach}
        
        {foreach item=FOLDER from=$FOLDERS}
            {if $FOLDER->isSentFolder()}
                {assign var=SENT_FOLDER value=$FOLDER->name()}
                <li class="tab-item nav-link fs-6 mm_folder mm_folder mmMainFolder d-flex align-items-center justify-content-between" data-foldername="{$FOLDER->name()}">
                    <div>
                        <i class="fa fa-paper-plane fs-3"></i>
                        <b class="mx-2">{vtranslate('LBL_SENT', $MODULE)}</b>
                    </div>
                    <div class="badge rounded-pill text-bg-primary mmUnreadCountBadge {if !$FOLDER->unreadCount()}hide{/if}">
                       {$FOLDER->unreadCount()} 
                    </div>
                </li>
            {/if}
        {/foreach}
        
        {foreach item=FOLDER from=$FOLDERS}
            {if stripos($FOLDER->name(), 'trash') !== false && $TRASH_ADDED == 0}
                {assign var=TRASH_ADDED value=1}
                {assign var=TRASH_FOLDER value=$FOLDER->name()}
                <li class="tab-item nav-link fs-6 mm_folder mm_folder mmMainFolder d-flex align-items-center justify-content-between" data-foldername="{$FOLDER->name()}">
                    <div>
                        <i class="fa fa-trash-o fs-3"></i>
                        <b class="mx-2">{vtranslate('LBL_TRASH', $MODULE)}</b>
                    </div>
                    <div class="badge rounded-pill text-bg-primary mmUnreadCountBadge {if !$FOLDER->unreadCount()}hide{/if}">
                       {$FOLDER->unreadCount()} 
                    </div>
                </li>
            {/if}
        {/foreach}
        <li class="p-3 text-secondary">
            <b>{vtranslate('LBL_Folders', $MODULE)}</b>
        </li>
        {assign var=IGNORE_FOLDERS value=array($INBOX_FOLDER, $SENT_FOLDER, $TRASH_FOLDER)}
        {foreach item=FOLDER from=$FOLDERS}
            {if !in_array($FOLDER->name(), $IGNORE_FOLDERS)}
            <li class="tab-item nav-link fs-6 mm_folder mm_folder mmOtherFolder d-flex align-items-center justify-content-between" data-foldername="{$FOLDER->name()}">
                <div>
                    <i class="fa-solid fa-folder fs-3"></i>
                    <b class="mx-2">{$FOLDER->name()}</b>
                </div>
                <div class="badge rounded-pill text-bg-primary mmUnreadCountBadge {if !$FOLDER->unreadCount()}hide{/if}">
                   {$FOLDER->unreadCount()} 
                </div>
            </li>
            {/if}
        {/foreach}
    </ul>
{/if}