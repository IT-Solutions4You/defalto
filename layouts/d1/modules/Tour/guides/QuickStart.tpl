{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{if 1 eq $GUIDE->getStep()}
    <h5 class="mb-3">{vtranslate('Welcome to the Quick Start Guide! Lets begin with the first step.', $MODULE)}</h5>
    <h5 class="mb-3">{vtranslate('You will learn how to navigate through the application and understand its layout.', $MODULE)}</h5>
    <p class="alert alert-primary"><b>{vtranslate('Menu', $MODULE)}:</b> {vtranslate('There is a menu on the top left side where you can access different modules sorted in categories.', $MODULE)}</p>
    <p class="alert alert-primary"><b>{vtranslate('Left Side Menu', $MODULE)}:</b> {vtranslate('There is a left side menu where you can access different modules from current category.', $MODULE)}</p>
{elseif 2 eq $GUIDE->getStep()}
    <h5 class="mb-3">{vtranslate('You are now viewing the Contact record edit screen.', $MODULE)}</h5>
    <p>{vtranslate('Here, you can update details and manage information with ease.', $MODULE)}</p>
    <p class="alert alert-primary"><b>{vtranslate('Blocks', $MODULE)}:</b> {vtranslate('You can view various sections, each grouping related fields together for easier editing.', $MODULE)}</p>
    <p class="alert alert-primary"><b>{vtranslate('Buttons', $MODULE)}:</b> {vtranslate('At the bottom of the window, you will find the Cancel and Save buttons to either discard or save your changes.', $MODULE)}</p>
{elseif 3 eq $GUIDE->getStep()}
    <h5 class="mb-3">{vtranslate('You are now viewing the Contact record detail screen.', $MODULE)}</h5>
    <p>{vtranslate('Here, you can view all the details of the Contact record.', $MODULE)}</p>
    <p class="alert alert-primary"><b>{vtranslate('Actions', $MODULE)}:</b> {vtranslate('You can perform actions like Edit, Delete, and more from the buttons available on the top right side.', $MODULE)}</p>
    <p class="alert alert-primary"><b>{vtranslate('Tabs', $MODULE)}:</b> {vtranslate('You can switch between different tabs to view related information.', $MODULE)}</p>
{/if}