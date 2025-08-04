{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <form class="modal-dialog modelContainer modal-content form-horizontal contentsBackground" id="moveDocuments" method="post" action="index.php">
        {assign var=HEADER_TITLE value={vtranslate('LBL_SELECT_A_FOLDER_TO_MOVE', $MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <input type="hidden" name="module" value="{$MODULE}" />
        <input type="hidden" name="action" value="MoveDocuments" />
        <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)} />
        <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)} />
        <input type="hidden" name="viewname" value="{$VIEWNAME}" />
        <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
        <input type="hidden" name="operator" value="{$OPERATOR}" />
        <input type="hidden" name="folder_id" value="{$FOLDER_ID}" />
        <input type="hidden" name="folder_value" value="{$FOLDER_VALUE}" />
        <input type="hidden" name="folderid" value="">
        <div class="modal-body">
            <div class="input-group">
                <input id="searchFolders" class="form-control" type="text" placeholder="{vtranslate('LBL_SEARCH', $MODULE)}">
                <span class="input-group-text">
                    <i class="fa fa-search"></i>
                </span>
            </div>
            <div id="foldersList">
                {foreach item=FOLDER from=$FOLDERS}
                    <div class="folder cursorPointer p-2 text-secondary rounded my-1" data-folder-id="{$FOLDER->getId()}">
                        <i class="fa fa-folder me-2"></i>
                        <span class="foldername">{$FOLDER->getName()}</span>
                    </div>
                {/foreach}
            </div>
        </div>
        {assign var=BUTTON_NAME value={vtranslate('LBL_MOVE', $MODULE)}}
        {assign var=BUTTON_ID value="js-move-documents"}
        {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
    </form>
{/strip}
