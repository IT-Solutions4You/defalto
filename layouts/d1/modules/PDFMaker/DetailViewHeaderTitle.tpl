{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="col-lg-6 col-md-6 col-sm-6">
		<div class="record-header clearfix">
			<div class="recordBasicInfo">
				<div class="info-row">
					<h4>
						{if !$MODULE}
							{assign var=MODULE value=$MODULE_NAME}
						{/if}
						<span class="modulename_label me-2">{vtranslate('LBL_MODULENAMES',$MODULE)}:</span>
						<span>{vtranslate($RECORD->get('module'),$RECORD->get('module'))}</span>
					</h4>
				</div>
			</div>
		</div>
	</div>
{/strip}