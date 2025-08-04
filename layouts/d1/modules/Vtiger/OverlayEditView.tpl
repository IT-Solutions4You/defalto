{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{foreach key=index item=jsModel from=$SCRIPTS}
    <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}
<form class="form-horizontal recordEditView" id="EditView" name="edit" method="post" action="index.php" enctype="multipart/form-data">
    <div class="fc-overlay-modal modal-content overlayEdit border-0">
        {if isset($SINGLE_MODULE_NAME)}
            {assign var="singleModuleName" value=$SINGLE_MODULE_NAME}
        {else}
            {assign var="singleModuleName" value=""}
        {/if}
        {assign var=TITLE value="{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($singleModuleName, $MODULE)} - {$RECORD_STRUCTURE_MODEL->getRecordName()}"}
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
                {elseif $SOURCE_MODULE neq ''}
                    <input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}"/>
                    <input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}"/>
                {/if}
                {include file="partials/EditViewReturn.tpl"|vtemplate_path:$MODULE}
                {include file="partials/EditViewContents.tpl"|@vtemplate_path:$MODULE}
            </div>
        </div>
        {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
    </div>
</form>