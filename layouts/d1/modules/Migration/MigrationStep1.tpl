{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="main-container container h-main px-4 py-3">
        <div class="inner-container">
            <form action='index.php' method="POST" class="bg-body rounded">
                <input type="hidden" name="module" id="module" value="{$MODULE}">
                <input type="hidden" name="view" id="view" value="Index">
                <input type="hidden" name="mode" value="step2">
                {include file='StepHeader.tpl'|@vtemplate_path:$MODULE}
                <div class="p-3">
                    <div id="running" class="text-center py-5">
                        <h4>{vtranslate('LBL_WAIT',$MODULE)}</h4>
                        <div class="spinner-border text-primary my-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5>{vtranslate('LBL_INPROGRESS',$MODULE)}</h5>
                    </div>
                    <div id="success" class="hide">
                        <h4> {vtranslate('LBL_DATABASE_CHANGE_LOG',$MODULE)} </h4>
                    </div>
                    <div id="showDetails" class="overflow-auto border rounded my-3 hide" style="height: 30vh;"></div>
                    <div id="nextButton" class="button-container text-end hide">
                        <button type="submit" class="btn btn-primary active">{vtranslate('Next', $MODULE)}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/strip}