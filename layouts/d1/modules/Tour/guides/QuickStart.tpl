{*
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{if 1 eq $GUIDE->getStep()}
    <h5 class="mb-3">Welcome to the Quick Start Guide! Let's begin with the first step.</h5>
    <h5 class="mb-3">You will learn how to navigate through the application and understand its layout.</h5>
    <p class="alert alert-primary"><b>Menu:</b> There is a menu on the top left side where you can access different modules sorted in categories.</p>
    <p class="alert alert-primary"><b>Left Side Menu:</b> There is a left side menu where you can access different modules from current category.</p>
    <p>Click on the "Next" button to proceed.</p>
{elseif 2 eq $GUIDE->getStep()}
    <h5 class="mb-3">You are now viewing the Contact record edit screen.</h5>
    <p>Here, you can update details and manage information with ease.</p>
    <p class="alert alert-primary"><b>Blocks:</b> You can view various sections, each grouping related fields together for easier editing.</p>
    <p class="alert alert-primary"><b>Buttons:</b> At the bottom of the window, you will find the Cancel and Save buttons to either discard or save your changes.</p>
{elseif 3 eq $GUIDE->getStep()}
    <h5 class="mb-3">You are now viewing the Contact record detail screen.</h5>
    <p>Here, you can view all the details of the Contact record.</p>
    <p class="alert alert-primary"><b>Actions:</b> You can perform actions like Edit, Delete, and more from the buttons available on the top right side.</p>
    <p class="alert alert-primary"><b>Tabs:</b> You can switch between different tabs to view related information.</p>
{/if}