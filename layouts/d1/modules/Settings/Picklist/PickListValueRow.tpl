{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="pickListValue border-bottom py-2" data-key-id="{$PICKLIST_KEY}" data-key="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE)}" data-deletable="{if !in_array($PICKLIST_VALUE, $NON_DELETABLE_VALUES)}true{else}false{/if}">
        <div class="fieldPropertyContainer">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="btn text-secondary dragHandle cursorDrag">
                        <i class="fa-solid fa-grip-vertical"></i>
                    </span>
                    <a title="{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}" class="colorItem btn text-secondary">
                        <i class="fa-solid fa-palette"></i>
                    </a>
                </div>
                <div class="col overflow-hidden">
                    <span class="d-inline-block text-truncate text-middle w-100-max py-1 px-2 rounded picklist-color picklist-{$SELECTED_PICKLIST_FIELDMODEL->getId()}-{$PICKLIST_KEY}">{vtranslate($PICKLIST_VALUE,$SELECTED_MODULE_NAME)}</span>
                </div>
                <div class="col-auto">
                    <div class="picklistActions">
                        <a title="{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}" class="renameItem btn text-secondary">
                            <i class="fa fa-pencil"></i>
                        </a>
                        {if !in_array($PICKLIST_VALUE, $NON_DELETABLE_VALUES)}
                            <a title="{vtranslate('LBL_DELETE_VALUE',$QUALIFIED_MODULE)}" class="deleteItem btn text-secondary">
                                <i class="fa fa-trash-o"></i>
                            </a>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}