{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

<div class="detailViewContainer viewContent clearfix" >
    <div class="col-sm-12 col-xs-12 content-area">

        <div class=" detailview-header-block">
            <div class="detailview-header">
                <div class="row">
                    <h3>{vtranslate('LBL_MODULE_NAME',$MODULE)} {vtranslate('LBL_INSTALL',$MODULE)}</h3>
                </div>
            </div>
        </div>

        <div class="editContainer">
            <hr>
            <div id="breadcrumb">
                <ul class="crumbs marginLeftZero">
                    <li class="first step {if $STEP eq "1"}active{/if}" style="z-index:10;" id="steplabel1"><a>
                            <span class="stepNum">1
                            </span>
                            <span class="stepText">{vtranslate('LBL_INSTALL_STEP_1',$MODULE)}
                            </span></a>
                    </li>        {if $TOTAL_STEPS eq "3"}
                        <li class="step {if $STEP eq "2"}active{/if}" style="z-index:9;"  id="steplabel2"><a>
                            <span class="stepNum">2
                            </span>
                                <span class="stepText">{vtranslate('LBL_DOWNLOAD',$MODULE)}
                            </span></a>
                        </li>        {/if}
                    <li class="step last {if $CURRENT_STEP eq $TOTAL_STEPS}active{/if}" style="z-index:7;"  id="steplabel{$TOTAL_STEPS}"><a>
                            <span class="stepNum">{$TOTAL_STEPS}
                            </span>
                            <span class="stepText">{vtranslate('LBL_FINISH',$MODULE)}
                            </span></a>
                    </li>
                </ul>
            </div>
            <div class="clearfix">
            </div>
            <form name="install" id="editLicense"  method="POST" action="index.php" class="form-horizontal">
                <input type="hidden" name="module" value="PDFMaker"/>
                <input type="hidden" name="view" value="List"/>
                <div id="step1" class="padding1per" style="border:1px solid #ccc; {if $STEP neq "1"}display:none;{/if}" >
                    <input type="hidden" name="installtype" value="validate"/>
                    <div class="controls">
                        <div>            <strong>{vtranslate('LBL_WELCOME',$MODULE)}</strong>
                        </div>
                        <br>
                        <div class="clearfix">
                        </div>
                    </div>
                    <div class="controls">
                        <div>           {vtranslate('LBL_WELCOME_DESC',$MODULE)}
                            </br>            {vtranslate('LBL_WELCOME_FINISH',$MODULE)}
                        </div>
                        <br>
                        <div class="clearfix">
                        </div>
                    </div>
                    <br>
                    <div class="controls">
                        <button type="button" id="start_button" class="btn btn-success"/><strong>{vtranslate('LBL_NEXT',$MODULE)}</strong>
                        </button>&nbsp;&nbsp;
                    </div>
                </div>     {if $TOTAL_STEPS eq "3"}
                <div id="step2" class="padding1per" style="border:1px solid #ccc;  {if $STEP neq "2"}display:none;{/if}">
                    <input type="hidden" name="installtype" value="download_src"/>
                    <div class="controls">
                        <div>            <strong>{vtranslate('LBL_DOWNLOAD_SRC',$MODULE)}</strong>
                        </div>
                        <br>
                        <div class="clearfix">
                        </div>
                    </div>
                    <div class="controls">
                        <div>            {vtranslate('LBL_DOWNLOAD_SRC_DESC',$MODULE)}             {if $MB_STRING_EXISTS eq 'false'}
                                <br>{vtranslate('LBL_MB_STRING_ERROR',$MODULE)}             {/if}
                        </div>
                        <br>
                        <div class="clearfix">
                        </div>
                    </div>
                    <div class="controls">
                        <button type="button" id="download_button" class="btn btn-success"/><strong>{vtranslate('LBL_DOWNLOAD',$MODULE)}</strong>
                        </button>&nbsp;&nbsp;
                    </div>
                    </div>{/if}
                <div id="step{$TOTAL_STEPS}" class="padding1per" style="border:1px solid #ccc; {if $STEP neq "3"}display:none;{/if}" >
                    <input type="hidden" name="installtype" value="redirect_recalculate" />
                    <div class="controls">
                        <div>{vtranslate('LBL_INSTALL_SUCCESS',$MODULE)}
                        </div>
                        <div class="clearfix">
                        </div>
                    </div>
                    <br>
                    <div class="controls">
                        <button type="submit" id="next_button" class="btn btn-success"/><strong>{vtranslate('LBL_FINISH',$MODULE)}</strong>
                        </button>&nbsp;&nbsp;
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>