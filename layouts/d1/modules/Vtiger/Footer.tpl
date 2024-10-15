{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
</main>
<footer class="app-footer bg-body">
    <div class="h-footer d-flex justify-content-center align-items-center">
        <div>
            <a href="https://defalto.com">Powered by Defalto {$VTIGER_VERSION} © 2022 - {date('Y')}</a>
        </div>
    </div>
</footer>
<libraries class="d-block">
    <div id="overlayPage" class="modal fade">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <!-- arrow is added to point arrow to the clicked element (Ex:- TaskManagement),
                any one can use this by adding "show" class to it -->
                <div class='arrow'></div>
                <div class='data'></div>
            </div>
        </div>
    </div>
    <div id="helpPageOverlay" class="modal fade">
        <div class="modal-dialog">
            <div class="data"></div>
        </div>
    </div>
    <div id="js_strings" class="hide noprint">{Zend_Json::encode($LANGUAGE_STRINGS)}</div>
    <div id="maxListFieldsSelectionSize" class="hide noprint">{$MAX_LISTFIELDS_SELECTION_SIZE}</div>
    <div id="myModal" class="modal myModal fade">
        <div class="modal-dialog"></div>
    </div>
    {include file='JSResources.tpl'|@vtemplate_path}
</libraries>
</body>
</html>
