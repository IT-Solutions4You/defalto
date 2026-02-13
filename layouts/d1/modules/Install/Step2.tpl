{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="main-container container py-3 px-4">
	<div class="inner-container">
		<form class="bg-body rounded" name="step2" method="get" action="index.php">
			<input type=hidden name="module" value="Install" />
			<input type=hidden name="view" value="Index" />
			<input type=hidden name="mode" value="Step3" />
			{include file='StepHeader.tpl'|@vtemplate_path:'Install'}
			<div class="license border m-3 h-50vh overflow-auto p-3">
                {Core_Utils_Helper::getLicenseFileContents()}
			</div>
			<div class="button-container text-end p-3">
				<input name="back" type="button" class="btn btn-primary me-2" value="{vtranslate('LBL_DISAGREE', 'Install')}"/>
				<input id="agree" type="submit" class="btn btn-primary active" value="{vtranslate('LBL_I_AGREE', 'Install')}"/>
			</div>
		</form>
	</div>
</div>