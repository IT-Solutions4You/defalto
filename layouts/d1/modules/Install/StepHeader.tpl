{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{if empty($TITLE)}
    {assign var=TITLE value='LBL_WELCOME'}
{/if}
<div class="container-fluid border-bottom p-3">
    <div class="row">
        <div class="col-sm">
            <span class="fs-4">{vtranslate($TITLE, 'Install')}</span>
        </div>
        <div class="col-sm-auto">
            <a href="index.php?module=Core&view=Redirect&mode=Documentation" target="_blank" class="fs-3">
                <i class="fa-solid fa-circle-question"></i>
            </a>
        </div>
    </div>
</div>