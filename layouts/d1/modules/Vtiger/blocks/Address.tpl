{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {if ($BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION') and ($MODULE neq 'PurchaseOrder')}
        <div class="container-fluid py-3 addressBlock">
            <div class="row">
                <div class="py-2 col-lg-6">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-4 fieldLabel" name="copyHeader1">
                                <label name="togglingHeader">{vtranslate('LBL_BILLING_ADDRESS_FROM', $MODULE)}</label>
                            </div>
                            <div class="col-lg-6 fieldValue" name="copyAddress1">
                                <div class="radio py-2">
                                    <label>
                                        <input type="radio" name="copyAddressFromRight" class="accountAddress" data-copy-address="billing" checked="checked">
                                        <span class="ms-3">{vtranslate('SINGLE_Accounts', $MODULE)}</span>
                                    </label>
                                </div>
                                <div class="radio py-2">
                                    <label>
                                        {if $MODULE eq 'Quotes'}
                                            <input type="radio" name="copyAddressFromRight" class="contactAddress" data-copy-address="billing" checked="checked">
                                            <span class="ms-3">{vtranslate('Related To', $MODULE)}</span>
                                        {else}
                                            <input type="radio" name="copyAddressFromRight" class="contactAddress" data-copy-address="billing" checked="checked">
                                            <span class="ms-3">{vtranslate('SINGLE_Contacts', $MODULE)}</span>
                                        {/if}
                                    </label>
                                </div>
                                <div class="radio py-2" name="togglingAddressContainerRight">
                                    <label>
                                        <input type="radio" name="copyAddressFromRight" class="shippingAddress" data-target="shipping" checked="checked">
                                        <span class="ms-3">{vtranslate('Shipping Address', $MODULE)}</span>
                                    </label>
                                </div>
                                <div class="radio py-2 hide" name="togglingAddressContainerLeft">
                                    <label>
                                        <input type="radio" name="copyAddressFromRight" class="billingAddress" data-target="billing" checked="checked">
                                        <span class="ms-3">{vtranslate('Billing Address', $MODULE)}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="py-2 col-lg-6">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-4 fieldLabel" name="copyHeader2">
                                <label name="togglingHeader">{vtranslate('LBL_SHIPPING_ADDRESS_FROM', $MODULE)}</label>
                            </div>
                            <div class="col-lg-6 fieldValue" name="copyAddress2">
                                <div class="radio py-2">
                                    <label>
                                        <input type="radio" name="copyAddressFromLeft" class="accountAddress" data-copy-address="shipping" checked="checked">
                                        <span class="ms-3">{vtranslate('SINGLE_Accounts', $MODULE)}</span>
                                    </label>
                                </div>
                                <div class="radio py-2">
                                    <label>
                                        {if $MODULE eq 'Quotes'}
                                            <input type="radio" name="copyAddressFromLeft" class="contactAddress" data-copy-address="shipping" checked="checked">
                                            <span class="ms-3">{vtranslate('Related To', $MODULE)}</span>
                                        {else}
                                            <input type="radio" name="copyAddressFromLeft" class="contactAddress" data-copy-address="shipping" checked="checked">
                                            <span class="ms-3">{vtranslate('SINGLE_Contacts', $MODULE)}</span>
                                        {/if}
                                    </label>
                                </div>
                                <div class="radio py-2" name="togglingAddressContainerLeft">
                                    <label>
                                        <input type="radio" name="copyAddressFromLeft" class="billingAddress" data-target="billing" checked="checked">
                                        <span class="ms-3">{vtranslate('Billing Address', $MODULE)}</span>
                                    </label>
                                </div>
                                <div class="radio py-2 hide" name="togglingAddressContainerRight">
                                    <label>
                                        <input type="radio" name="copyAddressFromLeft" class="shippingAddress" data-target="shipping" checked="checked">
                                        <span class="ms-3">{vtranslate('Shipping Address', $MODULE)}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
{/strip}