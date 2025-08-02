{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="col-lg-2 p-0">
    <div class="module-filters bg-body rounded ms-0 ms-lg-4 mb-4" id="module-filters">
        <div class="container-fluid d-flex flex-column h-list-max">
            <div class="row align-items-center py-2">
                <div class="col">
                    <h5 class="m-0">{vtranslate('LBL_FOLDERS',$MODULE)}</h5>
                </div>
                <div class="col-auto">
                    <button id="createFolder" class="btn btn-outline-secondary sidebar-btn">
                        <span class="fa fa-plus" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
            <div class="row py-2">
                <div class="col">
                    <input class="search-folders form-control" type="text" placeholder="{vtranslate('Search for Folders', $QUALIFIED_MODULE)}">
                </div>
            </div>
            <div class="row overflow-auto">
                <div class="col pb-3">
                    <ul id="folders-list" class="nav nav-pills flex-column">
                        {foreach item=FOLDER from=$FOLDERS name=folderView}
                            {assign var=FOLDERNAME value=vtranslate($FOLDER->get('foldername'), $MODULE)}
                            <li class="tab-item nav-link documentFolder w-100 {if $FOLDER_VALUE eq $FOLDER->getName()}active{/if} {if 'Default' eq $FOLDER->get('foldername')}documentFolderClone{/if}">
                                <div class="d-flex justify-content-between flex-nowrap">
                                    <a class="filterName text-truncate text-nowrap" href="javascript:void(0);" data-filter-id="{$FOLDER->get('folderid')}" data-folder-name="{$FOLDER->get('foldername')}" title="{$FOLDERNAME}">
                                        <i class="me-2 fa {if $FOLDER_VALUE eq $FOLDER->getName()}fa-folder-open{else}fa-folder{/if}"></i>
                                        <span class="foldername">{$FOLDERNAME}</span>
                                    </a>
                                    <div class="dropdown folderDropdown {if !$FOLDER->isEditable()}invisible{/if}">
                                        <div class="ps-2 pe-1 cursorPointer" data-bs-toggle="dropdown" aria-expanded="true">
                                            <i class="fa fa-caret-down"></i>
                                        </div>
                                        <ul class="dropdown-menu dropdown-menu-right vtDropDown" role="menu">
                                            <li class="editFolder " data-folder-id="{$FOLDER->get('folderid')}">
                                                <a class="dropdown-item" role="menuitem">
                                                    <i class="fa-solid fa-pencil text-secondary"></i>
                                                    <span class="ms-2">{vtranslate('Edit',$MODULE)}</span>
                                                </a>
                                            </li>
                                            <li class="deleteFolder " data-deletable="{!$FOLDER->hasDocuments()}" data-folder-id="{$FOLDER->get('folderid')}">
                                                <a class="dropdown-item" role="menuitem">
                                                    <i class="fa fa-trash text-secondary"></i>
                                                    <span class="ms-2">{vtranslate('Delete',$MODULE)}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>