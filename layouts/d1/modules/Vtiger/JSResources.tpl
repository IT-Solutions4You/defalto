{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/purl.js')}"></script>
    <script type="text/javascript" src="{vresource_url('vendor/select2/select2/dist/js/select2.full.min.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/jquery.class.min.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/jquery-ui-1.12.0.custom/jquery-ui.js')}"></script>
    <script type="text/javascript" src="{vresource_url('vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js')}"></script>
    <script type="text/javascript" src="{vresource_url('libraries/jquery/jstorage.min.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/jquery-validation/jquery.validate.min.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/jquery.slimscroll.min.js')}"></script>
    <script type="text/javascript" src="{vresource_url('libraries/jquery/jquery.ba-outside-events.min.js')}"></script>
	<script type="text/javascript" src="{vresource_url('libraries/jquery/defunkt-jquery-pjax/jquery.pjax.js')}"></script>
    <script type="text/javascript" src="{vresource_url('libraries/jquery/multiplefileupload/jquery_MultiFile.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/resources/jquery.additions.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/bootstrap-notify/bootstrap-notify.min.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/websockets/reconnecting-websocket.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/jquery-play-sound/jquery.playSound.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/malihu-custom-scrollbar/jquery.mousewheel.min.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/autoComplete/jquery.textcomplete.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/jquery.qtip.custom/jquery.qtip.js')}"></script>
    <script type="text/javascript" src="{vresource_url('libraries/jquery/jquery-visibility.min.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/momentjs/moment.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/jquery/jquery.timeago.js')}"></script>
    <script type="text/javascript" src="{vresource_url('vendor/ckeditor/ckeditor/ckeditor.js')}"></script>
    <script type="text/javascript" src="{vresource_url('vendor/ckeditor/ckeditor/adapters/jquery.js')}"></script>
	<script type='text/javascript' src="{vresource_url('layouts/$LAYOUT$/lib/anchorme_js/anchorme.min.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/modules/Vtiger/resources/Class.js')}"></script>
    <script type='text/javascript' src="{vresource_url('layouts/$LAYOUT$/resources/helper.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/resources/application.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/modules/Vtiger/resources/Utils.js')}"></script>
    <script type='text/javascript' src="{vresource_url('layouts/$LAYOUT$/modules/Vtiger/resources/validation.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/lib/bootbox/bootbox.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/modules/Vtiger/resources/Base.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/modules/Vtiger/resources/Vtiger.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/modules/Import/resources/Import.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/modules/Vtiger/resources/Base.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/modules/Google/resources/Settings.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/modules/Vtiger/resources/CkEditor.js')}"></script>
    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/modules/Documents/resources/Documents.js')}"></script>
    <script type="text/javascript" src="{vresource_url('libraries/DOMPurify/dist/purify.min.js')}"></script>

    {foreach item=SCRIPT_MODEL from=$SCRIPTS}
        <script type="{$SCRIPT_MODEL->getType()}" src="{vresource_url($SCRIPT_MODEL->getSrc())}"></script>
    {/foreach}

    <script type="text/javascript" src="{vresource_url('layouts/$LAYOUT$/resources/v7_client_compat.js')}"></script>
    <!-- Added in the end since it should be after less file loaded -->
    <script type="text/javascript" src="{vresource_url('libraries/bootstrap/js/less.min.js')}"></script>

    <!-- Enable tracking pageload time -->
	<script type="text/javascript">
		var _REQSTARTTIME = "{$smarty.server.REQUEST_TIME}";
		{literal}jQuery(document).ready(function() { window._PAGEREADYAT = new Date(); });
		jQuery(window).load(function() {
			window._PAGELOADAT = new Date();
			window._PAGELOADREQSENT = false;
			// Transmit the information to server about page render time now.
			if (typeof _REQSTARTTIME != 'undefined') {
				// Work with time converting it to GMT (assuming _REQSTARTTIME set by server is also in GMT)
				var _PAGEREADYTIME = _PAGEREADYAT.getTime() / 1000.0; // seconds
				var _PAGELOADTIME = _PAGELOADAT.getTime() / 1000.0;    // seconds
				var data = { page_request: _REQSTARTTIME, page_ready: _PAGEREADYTIME, page_load: _PAGELOADTIME };
				data['page_xfer'] = (_PAGELOADTIME - _REQSTARTTIME).toFixed(3);
				data['client_tzoffset']= -1*_PAGELOADAT.getTimezoneOffset()*60;
				data['client_now'] = JSON.parse(JSON.stringify(new Date()));
				if (!window._PAGELOADREQSENT) {
					// To overcome duplicate firing on Chrome
					window._PAGELOADREQSENT = true;
				}
			}
		});{/literal}
	</script>
{/strip}