{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="fc-overlay-modal overlayEdit">
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/modules/Inventory/resources/Edit.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/modules/'|cat:{$MODULE}|cat:'/resources/Edit.js')}"></script>
    {foreach key=index item=jsModel from=$SCRIPTS}
        <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
    {/foreach}
    <form class="form-horizontal recordEditView" id="EditView" name="edit" method="post" action="index.php" enctype="multipart/form-data">
        <div class="modal-content h-overlay d-flex flex-column">
            {assign var=TITLE value="{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} {$RECORD_STRUCTURE_MODEL->getRecordName()}"}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE}
            <div class="modal-body editViewBody overflow-auto">
                <div class="editViewContents">
                    {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
                    {assign var=QUALIFIED_MODULE_NAME value={$MODULE}}
                    {assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
                    {if $IS_PARENT_EXISTS}
                        {assign var=SPLITTED_MODULE value=":"|explode:$MODULE}
                        <input type="hidden" name="module" value="{$SPLITTED_MODULE[1]}"/>
                        <input type="hidden" name="parent" value="{$SPLITTED_MODULE[0]}"/>
                    {else}
                        <input type="hidden" name="module" value="{$MODULE}"/>
                    {/if}
                    <input type="hidden" name="action" value="Save"/>
                    <input type="hidden" name="record" value="{$RECORD_ID}"/>
                    <input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}"/>
                    <input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}"/>
                    {if $IS_RELATION_OPERATION }
                        <input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}"/>
                        <input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}"/>
                        <input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}"/>
                    {/if}
                    {include file="partials/EditViewReturn.tpl"|vtemplate_path:$MODULE}
                    {include file="partials/EditViewContents.tpl"|@vtemplate_path:'Inventory'}
                </div>
            </div>
            {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
        </div>
    </form>
</div>