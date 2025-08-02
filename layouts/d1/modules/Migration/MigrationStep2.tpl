{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{strip}
	<div class="main-container container h-main px-4 py-3">
		<div class="inner-container">
			<form action="index.php" method="post" class="bg-body rounded">
				{include file='StepHeader.tpl'|@vtemplate_path:$MODULE TITLE='LBL_MIGRATION_COMPLETED_SUCCESSFULLY'}
				<div class="container-fluid py-3">
					<div class="row">
						<div class="col">
							<p>{vtranslate('LBL_RELEASE_NOTES', $MODULE)}</p>
							<p>{vtranslate('LBL_CRM_DOCUMENTATION', $MODULE)}</p>
							<p>
								<span class="me-2">Connect with us</span>
								<a href="https://www.facebook.com/defalto.crm" target="_blank">
									<i class="bi bi-facebook"></i>
								</a>
							</p>
						</div>
					</div>
				</div>
				<div class="button-container p-3 text-end">
					<button type="button" onclick="window.location.href='index.php'" class="btn btn-primary active">{vtranslate('Finish', $MODULE)}</button>
				</div>
			</form>
		</div>
	</div>
{/strip}
