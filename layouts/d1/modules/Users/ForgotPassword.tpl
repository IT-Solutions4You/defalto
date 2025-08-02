{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<!DOCTYPE html>
<html>
<head>
    <link type='text/css' rel='stylesheet' href='{vresource_url('vendor/twbs/bootstrap/dist/css/bootstrap.min.css')}'>
    <link type='text/css' rel='stylesheet' href='{vresource_url('vendor/twbs/bootstrap-icons/font/bootstrap-icons.min.css')}'>
    <link type="text/css" rel="stylesheet" href="{vresource_url('layouts/$LAYOUT$/skins/base/style.css?v=0.0.150')}" media="screen">
    <script language='JavaScript'>
        function checkPassword() {
            let password = document.getElementById('password').value,
                confirmPassword = document.getElementById('confirmPassword').value;

            if (!password && !confirmPassword) {
                alert('Please enter new Password');
                return false;
            } else if (password != confirmPassword) {
                alert('Password and Confirm Password should be same');
                return false;
            } else {
                return true;
            }
        }
    </script>
</head>
<body class="bg-body-secondary">
<div id="container" class="container">
    <div class="logo p-3">
        <img src="{$LOGOURL}" alt="{$TITLE}">
    </div>
    <div class="border rounded bg-body">
        {if $LINK_EXPIRED neq 'true'}
            <div id="content">
                <div class="p-3 border-bottom">
                    <h3 class="m-0">{vtranslate('LBL_CHANGE_PASSWORD',$MODULE)}</h3>
                </div>
                <div id="changePasswordBlock" class="p-3">
                    <form name="changePassword" id="changePassword" action="{$TRACKURL}" method="post" accept-charset="utf-8">
                        <input type="hidden" name="username" value="{$USERNAME}">
                        <input type="hidden" name="shorturl_id" value="{$SHORTURL_ID}">
                        <input type="hidden" name="secret_hash" value="{$SECRET_HASH}">
                        <div>
                            <div class="row py-2 align-items-center">
                                <div class="col-lg-3 fieldLabel text-secondary">
                                    <label class="control-label " for="password">{vtranslate('LBL_NEW_PASSWORD',$MODULE)}</label>
                                </div>
                                <div class="col-lg-3 fieldValue">
                                    <input class="form-control" type="password" id="password" name="password">
                                </div>
                            </div>
                            <div class="row py-2 align-items-center">
                                <div class="col-lg-3 fieldLabel text-secondary">
                                    <label class="control-label" for="confirmPassword">{vtranslate('LBL_CONFIRM_PASSWORD',$MODULE)}</label>
                                </div>
                                <div class="col-lg-3 fieldValue">
                                    <input class="form-control" type="password" id="confirmPassword" name="confirmPassword">
                                </div>
                            </div>
                            <div class="row py-2 align-items-center">
                                <div class="col-lg-3"></div>
                                <div class="col-lg-3">
                                    <input class="btn btn-success" type="submit" id="btn" value="Submit" onclick="return checkPassword();"/>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="footer">
                    <p></p>
                </div>
                <div style="clear:both;"></div>
            </div>
        {else}
            <div id="content">
                {vtranslate('LBL_PASSWORD_LINK_EXPIRED_OR_INVALID_PASSWORD', $MODULE)}
            </div>
        {/if}
    </div>
</div>
</div>
</div>
</body>
</html>
