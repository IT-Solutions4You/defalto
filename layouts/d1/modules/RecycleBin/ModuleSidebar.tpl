<div class="containerModuleSidebar col-lg-2 mb-lg-4 ms-lg-4 bg-body rounded p-3">
    <div class="sidebar-menu sidebar-menu-full">
        <div class="module-filters" id="module-filters">
            <div class="sidebar-container lists-menu-container">
                <h5 class="sidebar-header">{vtranslate('LBL_MODULES', 'Settings:$MODULE')}</h5>
                <div class="my-3">
                    <input class="search-list form-control" type="text" placeholder="Search for Modules">
                </div>
                <div class="list-menu-content">
                    <div class="list-group">
                        <ul class="lists-menu nav nav-pills flex-column">
                            {if $MODULE_LIST|@count gt 0}
                                {foreach item=MODULEMODEL from=$MODULE_LIST}
                                    <li class='listViewFilter tab-item nav-link fs-6 {if $MODULEMODEL->getName() eq $SOURCE_MODULE}active{/if} '>
                                        <a class="filterName" href="index.php?module=RecycleBin&view=List&sourceModule={$MODULEMODEL->getName()}" >{vtranslate($MODULEMODEL->getName(), $MODULEMODEL->getName())}</a>
                                    </li>
                                {/foreach}
                            {/if}
                        </ul>
                    </div>
                    <div class="list-group hide noLists">
                        <h6 class="lists-header"><center> {vtranslate('LBL_NO')} {vtranslate('LBL_MODULES', 'Settings:$MODULE')} {vtranslate('LBL_FOUND')} ... </center></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>