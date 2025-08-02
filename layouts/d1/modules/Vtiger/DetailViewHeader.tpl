{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="detailview-header-block p-3 bg-body rounded">
        <div class="detailview-header">
            {include file='DetailViewHeaderTitle.tpl'|vtemplate_path:$QUALIFIED_MODULE}
            {include file='DetailViewActions.tpl'|vtemplate_path:$QUALIFIED_MODULE}
        </div>
    </div>
{/strip}