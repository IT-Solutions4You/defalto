{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<div>
       <div id="table-content" class="table-container">
		<table id="listview-table"  class="table table-borderless {if $TASK_LIST eq '0'}listview-table-norecords {else} listview-table{/if} ">
			<thead>
				<tr class="listViewContentHeader bg-body-secondary">
					<th width="20%" class="text-secondary">{vtranslate('LBL_ACTIVE',$QUALIFIED_MODULE)}</th>
					<th width="30%" class="text-secondary">{vtranslate('LBL_TASK_TYPE',$QUALIFIED_MODULE)}</th>
					<th class="text-secondary">{vtranslate('LBL_TASK_TITLE',$QUALIFIED_MODULE)}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$TASK_LIST item=TASK}
					<tr class="listViewEntries border-bottom">
						<td>
                            <div class="actions d-flex align-items-center">
								<span class="actionImages btn-group me-3">
									<a class="btn text-secondary" data-url="{$TASK->getEditViewUrl()}">
										<i class="fa fa-pencil alignMiddle" title="{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}"></i>
                                    </a>
									<a class="deleteTask btn text-secondary" data-deleteurl="{$TASK->getDeleteActionUrl()}">
										<i class="fa fa-trash alignMiddle" title="{vtranslate('LBL_DELETE',$QUALIFIED_MODULE)}"></i>
									</a>
								</span>
								<label class="form-check form-switch">
									<input type="checkbox" data-on-color="success" class="taskStatus form-check-input" data-statusurl="{$TASK->getChangeStatusUrl()}" {if $TASK->isActive()}checked="checked" value="on"{else}value="off"{/if} />
								</label>
                            </div>
                        </td>
                        <td class="listViewEntryValue">
							<span>{vtranslate($TASK->getTaskType()->getLabel(),$QUALIFIED_MODULE)}</span>
						</td>
						<td>
							<span>{Vtiger_Util_Helper::toSafeHTML($TASK->getName())}</span>
						</td>
					<tr>
				{/foreach}
                <tr class="listViewEntries border-bottom hide taskTemplate">
                    <td>
                        <div class="actions d-flex align-items-center">
                            <span class="actionImages btn-group me-3">
                                <a class="editTask btn text-secondary">
                                    <i class="fa fa-pencil alignMiddle" ></i>
                                </a>
                                <a class="deleteTaskTemplate btn text-secondary">
                                    <i class="fa fa-trash alignMiddle"></i>
                                </a>
                            </span>
							<label class="form-check form-switch">
								<input type="checkbox" data-on-color="success" class="tmpTaskStatus form-check-input" checked="" value="on"/>
							</label>
                        </div>
                    </td>
                    <td class="listViewEntryValue taskType">

					</td>
                    <td>
						<span class="taskName"></span>
					</td>
                </tr>
				{if empty($TASK_LIST)}
					<tr class="emptyRecordsDiv border-bottom">
						<td class="py-5 text-center fs-3" colspan="3">
							{vtranslate('LBL_NO_TASKS_ADDED',$QUALIFIED_MODULE)}
						</td>
					</tr>
				{/if}
			</tbody>
		</table>
        </div>
	</div>
{/strip}