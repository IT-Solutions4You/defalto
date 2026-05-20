{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <li class="ms-2">
        <div class="dropdown">
            <a href="#" class="userName" data-bs-toggle="dropdown">
                <div class="profile-img-container d-flex align-items-center justify-content-center">
                    {assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
                    {if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].path eq ''}
                        <i class='vicon-vtigeruser'></i>
                    {else}
                        {foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
                            {if !empty($IMAGE_INFO.url)}
                                <img src="{$IMAGE_INFO.url}" width="2.2rem" height="2.2rem">
                            {/if}
                        {/foreach}
                    {/if}
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end logout-content p-0 mt-2 border-0 shadow">
                <div class="container-fluid border-bottom p-4">
                    <div class="row text-nowrap">
                        <div class="col-auto">
                            <div class="profile-img-container p-0 d-flex align-items-center justify-content-center" style="width: 3.8rem; height: 3.8rem;">
                                {assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
                                {if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].path eq ''}
                                    <i class='vicon-vtigeruser'></i>
                                {else}
                                    {foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
                                        {if !empty($IMAGE_INFO.url)}
                                            <img src="{$IMAGE_INFO.url}" height="100%" width="100%">
                                        {/if}
                                    {/foreach}
                                {/if}
                            </div>
                        </div>
                        <div class="col overflow-hidden">
                            <div class="profile-container">
                                <div class="profile-name lh-base fw-bold fs-5 text-truncate">{$USER_MODEL->get('first_name')} {$USER_MODEL->get('last_name')}</div>
                                <div class="profile-username lh-base fs-5 text-truncate text-secondary" title='{$USER_MODEL->get('user_name')}'>{$USER_MODEL->get('email1')}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="logout-footer" class="logout-footer container-fluid px-4 py-2">
                    <a class="row" href="{$USER_MODEL->getPreferenceDetailViewUrl()}">
                        <div class="col-2 p-3 logout-footer-icon text-secondary text-center">
                            <i class="fa fa-cogs"></i>
                        </div>
                        <div class="col py-3 fw-semibold" id="menubar_item_right_LBL_MY_PREFERENCES">{vtranslate('LBL_MY_PREFERENCES')}</div>
                    </a>
                    {if Core_Utils_Helper::isModuleActive('Tour')}
                        <a class="row" href="index.php?module=Tour&view=Index">
                            <div class="col-2 p-3 logout-footer-icon text-secondary text-center">
                                <i class="fa-solid fa-lightbulb"></i>
                            </div>
                            <div class="col py-3 fw-semibold" id="menubar_item_right_LBL_WELCOME_GUIDES">{vtranslate('LBL_WELCOME_GUIDES')}</div>
                        </a>
                    {/if}
                    <a class="row" href="index.php?module=Users&action=Logout">
                        <div class="col-2 p-3 logout-footer-icon text-secondary text-center">
                            <i class="fa fa-power-off"></i>
                        </div>
                        <div class="col py-3 fw-semibold" id="menubar_item_right_LBL_SIGN_OUT">{vtranslate('LBL_SIGN_OUT')}</div>
                    </a>
                </div>
            </div>
        </div>
    </li>
{/strip}