{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<label class="pull-left themeTextColor font-x-x-large">Instructions</label>
<br clear="all">
<hr>
<p>
    We developed for that purpose related custom function as well as other files which you can download using the button in the right upper corner. Please copy whole content of the unzipped file UnsubscribeEmail.zip into your webserver.
</p>
<p>
    You can use UnsbuscibeEmail.php within your webpage or vice versa you can edit UnsbuscibeEmail.php according to your needs. Inside UnsbuscibeEmail.php is necessary to define:
</p>
<ul>
    <li>$server_path – this is your vtiger URL. If you are using "vtiger On Demand" you'll find it in your browser's address bar</li>
    <li>$user_name – this is the username you use to login to the vtiger CRM, see also “My Preferences”</li>
    <li>$user_access_key – the access key can be retrieved by logging into your vtiger CRM account, going to "My Preferences"</li>
    <li>Sentence shown after successful unsubscribing (row 77)</li>
    <li>Sentence shown when email address has not been unsubscribed (row 79)</li>
</ul>
<center><img src="layouts/vlayout/modules/EMAILMaker/images/unsubscribe_email_1.jpg"></center>
<br><br>
<p>
    The final step is to put custom function its4you_unsubscribeemail into your template. There are just two parameters which you need to modify inside custom function [CUSTOMFUNCTION_AFTER| its4you_unsubscribeemail|$accounts-crmid$|$contacts-crmid$|URL_ADDRESS|Unsubscribe email|CUSTOMFUNCTION_AFTER]:
</p>
<ul>
    <li>parameter 3 - URL_ADDRESS – url address of your unsubscribe email web page</li>
    <li>parameter 4 – Text of the unsubscribe link</li>
</ul>
<p>
    Next picture shows process of unsubscribing contact which leads to change his “Email Opt Out” to “yes”.
</p>
<center><img src="layouts/vlayout/modules/EMAILMaker/images/unsubscribe_email_2.jpg"></center>