{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<div class="row" style="border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255);position: relative; z-index: 10000000; padding: 10px; width: 80%; margin: 0 auto; margin-top: 5%;">
		<div class ="col-lg-1 col-sm-2 col-md-1" style="float: left;"><img src="{vimage_path('denied.gif')}" ></div>
		<div class ="col-lg-11 col-sm-10 col-md-11" nowrap="nowrap">
			<span class="genHeaderSmall">
				{if $IS_DUPICATES_FAILURE}
					<span>{$EXCEPTION}</span>
				{else}
					{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
					<span class="genHeaderSmall">{vtranslate($SINGLE_MODULE, $MODULE)} {vtranslate('CANNOT_CONVERT', $MODULE)}
						<br>
						<ul> {vtranslate('LBL_FOLLOWING_ARE_POSSIBLE_REASONS', $MODULE)}:
							<li>{vtranslate('LBL_POTENTIALS_FIELD_MAPPING_INCOMPLETE', $MODULE)}</li>
							<li>{vtranslate('LBL_MANDATORY_FIELDS_ARE_EMPTY', $MODULE)}</li>
							{if $EXCEPTION}
								<li>{$EXCEPTION}</li>
							{/if}
						</ul>
					</span>
				{/if}
			</span>
			<hr>
			<div class="small" align="right" nowrap="nowrap">
				{if !$IS_DUPICATES_FAILURE && $CURRENT_USER->isAdminUser()}
					<a href="index.php?parent=Settings&module=Potentials&view=MappingDetail">{vtranslate('LBL_POTENTIALS_FIELD_MAPPING', $MODULE)}</a><br>
				{/if}
				<a href="javascript:window.history.back();">{vtranslate('LBL_GO_BACK', $MODULE)}</a><br>
			</div>
		</div>
	</div>
{/strip}

