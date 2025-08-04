{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/LayoutEditor/views/Index.php *}

{strip}
	<div class="px-4 pb-4" id="layoutEditorContainer">
		<div class="bg-body rounded">
			<div class="main-scroll">
				<input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
				<input class="selectedTab" type="hidden" value="{$SELECTED_TAB}">
				<input class="selectedMode" type="hidden" value="{$MODE}">
				<input type="hidden" id="selectedModuleLabel" value="{vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)}" />
				<div class="container-fluid align-items-center px-3 pt-3 border-bottom">
					<div class="row align-items-center">
						<div class="col-lg pb-3">
							<h4 class="m-0">{sprintf(vtranslate('LBL_EDIT_FIELDS', $QUALIFIED_MODULE), vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME))}</h4>
						</div>
						<label class="col-lg-auto pb-3">
							{vtranslate('SELECT_MODULE', $QUALIFIED_MODULE)}
						</label>
						<div class="col-lg pb-3">
							<select class="select2 form-select" data-close-on-select="true" name="layoutEditorModules">
								<option value=''>{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
								{foreach item=MODULE_NAME key=TRANSLATED_MODULE_NAME from=$SUPPORTED_MODULES}
									<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>
										{$TRANSLATED_MODULE_NAME}
									</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>
				{if $SELECTED_MODULE_NAME}
					<div class="contents tabbable">
						<ul class="nav nav-tabs layoutTabs massEditTabs my-3 border-bottom">
							{assign var=URL value="index.php?module=LayoutEditor&parent=Settings&view=Index"}
							<li class="nav-item detailViewTab ms-3">
								<a class="nav-link {if $SELECTED_TAB eq 'detailViewTab'}active{/if}" data-bs-toggle="tab" href="#detailViewLayout" data-url="{$URL}" data-mode="showFieldLayout">
									<strong>{vtranslate('LBL_DETAILVIEW_LAYOUT', $QUALIFIED_MODULE)}</strong>
								</a>
							</li>
							<li class="nav-item headerFieldsTab ms-3">
								<a class="nav-link {if $SELECTED_TAB eq 'headerFieldsTab'}active{/if}" data-bs-toggle="tab" href="#headerFieldsLayout" data-url="{$URL}" data-mode="showHeaderFieldsLayout">
									<strong>{vtranslate('LBL_HEADER_FIELDS', $QUALIFIED_MODULE)}</strong>
								</a>
							</li>
							<li class="nav-item relatedListTab ms-3">
								<a class="nav-link {if $SELECTED_TAB eq 'relatedListTab'}active{/if}" data-bs-toggle="tab" href="#relatedTabOrder" data-url="{$URL}" data-mode="showRelatedListLayout">
									<strong>{vtranslate('LBL_RELATION_SHIPS', $QUALIFIED_MODULE)}</strong>
								</a>
							</li>
							<li class="nav-item duplicationTab ms-3">
								<a class="nav-link {if $SELECTED_TAB eq 'duplicationTab'}active{/if}" data-bs-toggle="tab" href="#duplicationContainer" data-url="{$URL}" data-mode="showDuplicationHandling">
									<strong>{vtranslate('LBL_DUPLICATE_HANDLING', $QUALIFIED_MODULE)}</strong>
								</a>
							</li>
						</ul>
						<div class="tab-content layoutContent themeTableColor overflowVisible px-3">
							<div class="tab-pane{if $SELECTED_TAB eq 'detailViewTab'} active{/if}" id="detailViewLayout">
								{if $SELECTED_TAB eq 'detailViewTab'}
									{include file=vtemplate_path('FieldsList.tpl', $QUALIFIED_MODULE)}
								{/if}
							</div>
							<div class="tab-pane{if $SELECTED_TAB eq 'headerFieldsTab'} active{/if}" id="headerFieldsLayout">
								{if $SELECTED_TAB eq 'headerFieldsTab'}
									{include file=vtemplate_path('HeaderFields.tpl', $QUALIFIED_MODULE)}
								{/if}
							</div>
							<div class="tab-pane {if $SELECTED_TAB eq 'relatedListTab'} active{/if}" id="relatedTabOrder">
								{if $SELECTED_TAB eq 'relatedListTab'}
									{include file=vtemplate_path('RelatedList.tpl', $QUALIFIED_MODULE)}
								{/if}
							</div>
							<div class="tab-pane{if $SELECTED_TAB eq 'duplicationTab'} active{/if}" id="duplicationContainer">
								{if $SELECTED_TAB eq 'duplicationTab'}
									{include file=vtemplate_path('DuplicateHandling.tpl', $QUALIFIED_MODULE)}
								{/if}
							</div>
						</div>
					</div>
				{/if}
			</div>
		</div>
	</div>
	</div>
{/strip}