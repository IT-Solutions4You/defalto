{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="editViewPageDiv px-4 pb-4">
    <div class="rounded bg-body">
        <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
            <div class="editViewBody">
                <div class="editViewContents">
                    {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
                    {assign var=QUALIFIED_MODULE_NAME value={$MODULE}}
                    {assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
                    {if $IS_PARENT_EXISTS}
                        {assign var=SPLITTED_MODULE value=":"|explode:$MODULE}
                        <input type="hidden" name="module" value="{$SPLITTED_MODULE[1]}" />
                        <input type="hidden" name="parent" value="{$SPLITTED_MODULE[0]}" />
                    {else}
                        <input type="hidden" name="module" value="{$MODULE}" />
                    {/if}
                    <input type="hidden" name="action" value="Save" />
                    <input type="hidden" name="record" value="{$RECORD_ID}" />
                    <input type="hidden" name="mode" value="Calendar" />
                    <input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
                    <input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
                    {include file="partials/EditViewReturn.tpl"|vtemplate_path:$MODULE}
                    {include file="partials/CalendarSettingsEditView.tpl"|@vtemplate_path:$MODULE}
                </div>
            </div>
            <div class="modal-overlay-footer modal-footer border-top">
                <div class="container-fluid p-3">
                    <div class="row">
                        <div class="col text-end">
                            <a class="btn btn-primary cancelLink"  href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-primary active saveButton" type="submit" >{vtranslate('LBL_SAVE', $MODULE)}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>