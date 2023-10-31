{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div id="formContainer">
    <div class="main-container container px-4 py-3">
        <div class="inner-container">
            <form class="rounded bg-body" name="step6" method="post" action="index.php">
                <input type=hidden name="module" value="Install"/>
                <input type=hidden name="view" value="Index"/>
                <input type=hidden name="mode" value="Step7"/>
                <input type=hidden name="auth_key" value="{$AUTH_KEY}"/>
                {include file='StepHeader.tpl'|@vtemplate_path:'Install' TITLE='LBL_ONE_LAST_THING'}
                <div class="container-fluid p-3">
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                            <table class="table table-borderless config-table input-table">
                                <tbody>
                                <tr>
                                    <td><strong>Your Name</strong> <span class="no text-danger ms-2">*</span></td>
                                    <td><input name="myname" class="text form-control" required="true"></td>
                                </tr>
                                <tr>
                                    <td><strong>Your Email</strong>
                                    <td><input name="myemail" class="email form-control" required="true"></td>
                                <tr>
                                    <td><strong>Your Industry</strong> <span class="no text-danger ms-2">*</span></td>
                                    <td>
                                        <select name="industry" class="select2" required="true" placeholder="Choose one...">
                                            <option>Accounting</option>
                                            <option>Advertising</option>
                                            <option>Agriculture</option>
                                            <option>Apparel &amp; Accessories</option>
                                            <option>Automotive</option>
                                            <option>Banking &amp; Financial Services</option>
                                            <option>Biotechnology</option>
                                            <option>Call Centers</option>
                                            <option>Careers/Employment</option>
                                            <option>Chemical</option>
                                            <option>Computer Hardware</option>
                                            <option>Computer Software</option>
                                            <option>Consulting</option>
                                            <option>Construction</option>
                                            <option>Education</option>
                                            <option>Energy Services</option>
                                            <option>Engineering</option>
                                            <option>Entertainment</option>
                                            <option>Financial</option>
                                            <option>Food &amp; Food Service</option>
                                            <option>Government</option>
                                            <option>Health care</option>
                                            <option>Insurance</option>
                                            <option>Legal</option>
                                            <option>Logistics</option>
                                            <option>Manufacturing</option>
                                            <option>Media &amp; Production</option>
                                            <option>Non-profit</option>
                                            <option>Pharmaceutical</option>
                                            <option>Real Estate</option>
                                            <option>Rental</option>
                                            <option>Retail &amp; Wholesale</option>
                                            <option>Security</option>
                                            <option>Service</option>
                                            <option>Sports</option>
                                            <option>Telecommunications</option>
                                            <option>Transportation</option>
                                            <option>Travel &amp; Tourism</option>
                                            <option>Utilities</option>
                                            <option>Other</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        This information will not be shared. Vtiger will use the email address for sending a monthly newsletter and any product updates.
                                        Industry will be used to understand use cases and further improve the product.
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="button-container text-end p-3">
                    <input type="button" class="btn btn-primary active" value="{vtranslate('LBL_NEXT','Install')}" name="step7"/>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="progressIndicator" class="hide">
    <div class="main-container container px-4 py-3">
        <div class="inner-container">
            <div class="rounded bg-body">
                <div class="welcome-div text-center">
                    <div class="p-3">
                        <h3>{vtranslate('LBL_INSTALLATION_IN_PROGRESS','Install')}...</h3>
                    </div>
                    <div class="p-3">
                        <img src="{'install_loading.gif'|vimage_path}"/>
                        <h6>{vtranslate('LBL_PLEASE_WAIT','Install')}.... </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>