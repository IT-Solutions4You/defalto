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
							<p>{vtranslate('LBL_DISCUSS_WITH_US_AT_BLOGS', $MODULE)}</p>
							<p>{vtranslate('LBL_TALK_TO_US_AT_FORUMS', $MODULE)}</p>
                            <p>{vtranslate('LBL_CRM_DOCUMENTATION', $MODULE)}</p>
							<p>
								<span>Connect with us</span>
								<a class="ms-2" href="index.php?module=Core&view=Redirect&mode=Facebook" target="_blank">
									<i class="bi bi-facebook"></i>
								</a>
                                <a class="ms-2" href="index.php?module=Core&view=Redirect&mode=Youtube">
                                    <i class="bi bi-youtube"></i>
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
