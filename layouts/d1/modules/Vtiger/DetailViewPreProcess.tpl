{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {include file='partials/Topbar.tpl'|vtemplate_path:$MODULE}
    <div class="container-fluid app-nav">
        <div class="row">
            {include file='partials/SidebarHeader.tpl'|vtemplate_path:$MODULE}
            {include file='ModuleHeader.tpl'|vtemplate_path:$MODULE}
        </div>
    </div>
    </nav>
    <div class="container-fluid main-container">
        <div class="row">
            {include file='ModuleNavigator.tpl'|vtemplate_path:$MODULE}
            <div class="detailViewContainer viewContent col p-0 overflow-auto">
                <div class="content-area container-fluid px-0 pb-lg-4 px-lg-4">
                    {include file='DetailViewHeader.tpl'|vtemplate_path:$MODULE}
                    {assign var=HAS_RELATED_MODULES value=false}
                    {foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWRELATED']|default:[]}
                        {if $RELATED_LINK->getRelatedModuleName() neq 'ModComments'}
                            {assign var=HAS_RELATED_MODULES value=true}
                        {/if}
                    {/foreach}
                    {include file='RecordTabs.tpl'|vtemplate_path:'Core'}
                    <div class="detailview-content row">
                        <input id="recordId" type="hidden" value="{$RECORD->getId()}" />
                        {if $HAS_RELATED_MODULES}
                            {include file="ModuleRelatedTabs.tpl"|vtemplate_path:$MODULE}
                        {/if}
                        <div class="details {if $HAS_RELATED_MODULES}col-lg-9 col-xl-10{else}col-12{/if} order-2 order-lg-1 px-0">
{/strip}
