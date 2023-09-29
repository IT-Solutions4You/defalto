<div class="col-lg-2 p-0">
    <div class="bg-white rounded p-3 ms-4 mb-4">
        <div class="module-filters" id="module-filters">
            <div class="sidebar-container lists-menu-container">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="m-0">{vtranslate('LBL_FOLDERS',$MODULE)}</h5>
                    <button id="createFolder" class="btn btn-outline-secondary sidebar-btn">
                        <span class="fa fa-plus" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="my-3">
                    <input class="search-folders form-control" type="text" placeholder="Search for Folders">
                </div>
                <div class="list-menu-content">
                    <div class="list-group">
                        <ul id="folders-list" class="nav nav-pills flex-column">
                            {foreach item=FOLDER from=$FOLDERS name=folderView}
                                {assign var=FOLDERNAME value={vtranslate($FOLDER->get('foldername'), $MODULE)}}
                                <li class="tab-item nav-link fs-6 documentFolder {if $FOLDER_VALUE eq $FOLDER->getName()}active{/if} {if $smarty.foreach.folderView.iteration gt 20}filterHidden hide{/if}">
                                    <div class="d-flex justify-content-between">
                                        <a class="filterName" href="javascript:void(0);" data-filter-id="{$FOLDER->get('folderid')}" data-folder-name="{$FOLDER->get('foldername')}" title="{$FOLDERNAME}">
                                            <i class="fa {if $FOLDER_VALUE eq $FOLDER->getName()}fa-folder-open{else}fa-folder{/if}"></i>
                                            <span class="foldername ms-2">{if {$FOLDERNAME|strlen} > 40 } {$FOLDERNAME|substr:0:40|@escape:'html'}..{else}{$FOLDERNAME|@escape:'html'}{/if}</span>
                                        </a>
                                        {if $FOLDER->getName() neq 'Default' && $FOLDER->getName() neq 'Google Drive' && $FOLDER->getName() neq 'Dropbox'}
                                            <div class="dropdown">
                                                <div data-bs-toggle="dropdown" aria-expanded="true">
                                                    <i class="fa fa-caret-down"></i>
                                                </div>
                                                <ul class="dropdown-menu dropdown-menu-right vtDropDown" role="menu">
                                                    <li class="editFolder " data-folder-id="{$FOLDER->get('folderid')}">
                                                        <a class="dropdown-item" role="menuitem">
                                                            <i class="fa fa-pencil-square-o"></i>
                                                            <span class="ms-2">{vtranslate('Edit',$MODULE)}</span>
                                                        </a>
                                                    </li>
                                                    <li class="deleteFolder " data-deletable="{!$FOLDER->hasDocuments()}" data-folder-id="{$FOLDER->get('folderid')}">
                                                        <a class="dropdown-item" role="menuitem">
                                                            <i class="fa fa-trash"></i>
                                                            <span class="ms-2">{vtranslate('Delete',$MODULE)}</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        {/if}
                                    </div>
                                </li>
                            {/foreach}
                            <li class="noFolderText tab-item nav-link fs-6" style="display: none;">
                                <h6 class="lists-header text-center">
                                    {vtranslate('LBL_NO')} {vtranslate('LBL_FOLDERS', $MODULE)} {vtranslate('LBL_FOUND')} ...
                                </h6>
                            </li>
                        </ul>
                        <div class="text-center mt-3">
                            <a class="btn btn-primary toggleFilterSize" data-more-text="{vtranslate('Show more',Vtiger)}" data-less-text="{vtranslate('Show less',Vtiger)}">
                                {if $smarty.foreach.folderView.iteration gt 5}
                                    {vtranslate('Show more',Vtiger)}
                                {/if}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>