{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Compatibility entry point; workflow presentation is supplied by the editor model. *}
{if empty($FILTER_EDITOR)}
    {assign var=FILTER_EDITOR value=Settings_Workflows_FilterEditor_Model::getInstance($SELECTED_MODULE_NAME|default:$SOURCE_MODULE|default:$MODULE)}
{/if}
{include file='AdvanceFilterCondition.tpl'|vtemplate_path:'Vtiger'}
