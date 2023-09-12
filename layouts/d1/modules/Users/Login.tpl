{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    <div class="container-fluid loginPageContainer h-100">
        <div class="row loginPageBackground">
            <div class="col-lg-7 bg-white rounded-end">
                <div class="container-fluid">
                    <div class="row d-flex align-items-center justify-content-center">
                        <div class="col-lg-4">
                            <div class="loginDiv">
                                <div>
                                    <span class="{if !$ERROR}hide{/if} failureMessage" id="validationMessage">{$MESSAGE}</span>
                                    <span class="{if !$MAIL_STATUS}hide{/if} successMessage">{$MESSAGE}</span>
                                </div>
                                <div id="loginFormDiv">
                                    <form method="POST" action="index.php" class="d-block">
                                        <input type="hidden" name="module" value="Users"/>
                                        <input type="hidden" name="action" value="Login"/>
                                        <div class="mb-5">
                                            <div class="fs-2">Welcome to our CRM.</div>
                                            <div class="text-secondary">Enter your details to proceed further</div>
                                        </div>
                                        <div class="border-1 border-bottom py-3">
                                            <label class="form-label py-2 text-secondary" for="username">Username</label>
                                            <input id="username" class="form-control px-0 border-0" type="text" name="username" placeholder="Start typing...">
                                        </div>
                                        <div class="border-1 border-bottom py-3">
                                            <label class="form-label py-2 text-secondary" for="password">Password</label>
                                            <input id="password" class="form-control px-0 border-0" type="password" name="password" placeholder="Start typing...">
                                        </div>
                                        <div class="text-end py-3">
                                            <a class="forgotPasswordLink text-primary">Recover password</a>
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary w-100 active button">Sign in</button>
                                        </div>
                                    </form>
                                </div>
                                <div id="forgotPasswordDiv" class="hide">
                                    <form action="forgotPassword.php" method="POST" class="d-block">
                                        <div class="mb-5">
                                            <div class="fs-2">Lost your password?</div>
                                            <div class="text-secondary">Enter your details to proceed further</div>
                                        </div>
                                        <div class="border-1 border-bottom py-3">
                                            <label class="form-label py-2 text-secondary" for="fusername">Username</label>
                                            <input id="fusername" class="form-control px-0 border-0" type="text" name="username" placeholder="Start typing...">
                                        </div>
                                        <div class="border-1 border-bottom py-3">
                                            <label for="email" class="form-label py-2 text-secondary">Email</label>
                                            <input id="email" class="form-control border-0 px-0" type="email" name="emailId" placeholder="Start typing..." >
                                        </div>
                                        <div class="py-3">
                                            <a class="forgotPasswordLink text-primary">Return back and cancel</a>
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary active w-100 button forgot-submit-btn">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">

            </div>
        </div>
        <style>
            .loginPageContainer .row {
                min-height: 100vh !important;
            }
            .loginPageBackground {
                background: url(layouts/d1/resources/Images/login-background.jpg) center center / cover;
            }

            #page {
                background-color: #fff;
                padding-top: 0;
            }
            h3, h4 {
                margin-top: 0px;
            }

            hgroup {
                text-align: center;
                margin-top: 4em;
            }

            .group {
                position: relative;
                margin: 20px 20px 40px;
            }

            .failureMessage {
                color: red;
                display: block;
                text-align: center;
                padding: 0px 0px 10px;
            }

            .successMessage {
                color: green;
                display: block;
                text-align: center;
                padding: 0px 0px 10px;
            }

            .inActiveImgDiv {
                padding: 5px;
                text-align: center;
                margin: 30px 0px;
            }

            .app-footer p {
                margin-top: 0px;
            }

            .bar {
                position: relative;
                display: block;
                width: 100%;
            }

            .bar:before, .bar:after {
                content: '';
                width: 0;
                bottom: 1px;
                position: absolute;
                height: 1px;
                background: #35aa47;
                transition: all 0.2s ease;
            }

            .bar:before {
                left: 50%;
            }

            .bar:after {
                right: 50%;
            }

            @keyframes inputHighlighter {
                from {
                    background: #4a89dc;
                }
                to {
                    width: 0;
                    background: transparent;
                }
            }

            @keyframes ripples {
                0% {
                    opacity: 0;
                }
                25% {
                    opacity: 1;
                }
                100% {
                    width: 200%;
                    padding-bottom: 200%;
                    opacity: 0;
                }
            }
        </style>
        <script>
            jQuery(document).ready(function () {
                var validationMessage = jQuery('#validationMessage');
                var forgotPasswordDiv = jQuery('#forgotPasswordDiv');

                var loginFormDiv = jQuery('#loginFormDiv');
                loginFormDiv.find('#password').focus();

                loginFormDiv.find('a').click(function () {
                    loginFormDiv.toggleClass('hide');
                    forgotPasswordDiv.toggleClass('hide');
                    validationMessage.addClass('hide');
                });

                forgotPasswordDiv.find('a').click(function () {
                    loginFormDiv.toggleClass('hide');
                    forgotPasswordDiv.toggleClass('hide');
                    validationMessage.addClass('hide');
                });

                loginFormDiv.find('button').on('click', function () {
                    var username = loginFormDiv.find('#username').val();
                    var password = jQuery('#password').val();
                    var result = true;
                    var errorMessage = '';
                    if (username === '') {
                        errorMessage = 'Please enter valid username';
                        result = false;
                    } else if (password === '') {
                        errorMessage = 'Please enter valid password';
                        result = false;
                    }
                    if (errorMessage) {
                        validationMessage.removeClass('hide').text(errorMessage);
                    }
                    return result;
                });

                forgotPasswordDiv.find('button').on('click', function () {
                    var username = jQuery('#forgotPasswordDiv #fusername').val();
                    var email = jQuery('#email').val();

                    var email1 = email.replace(/^\s+/, '').replace(/\s+$/, '');
                    var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/;
                    var illegalChars = /[\(\)\<\>\,\;\:\\\"\[\]]/;

                    var result = true;
                    var errorMessage = '';
                    if (username === '') {
                        errorMessage = 'Please enter valid username';
                        result = false;
                    } else if (!emailFilter.test(email1) || email == '') {
                        errorMessage = 'Please enter valid email address';
                        result = false;
                    } else if (email.match(illegalChars)) {
                        errorMessage = 'The email address contains illegal characters.';
                        result = false;
                    }
                    if (errorMessage) {
                        validationMessage.removeClass('hide').text(errorMessage);
                    }
                    return result;
                });
                jQuery('input').blur(function (e) {
                    var currentElement = jQuery(e.currentTarget);
                    if (currentElement.val()) {
                        currentElement.addClass('used');
                    } else {
                        currentElement.removeClass('used');
                    }
                });

                var ripples = jQuery('.ripples');
                ripples.on('click.Ripples', function (e) {
                    jQuery(e.currentTarget).addClass('is-active');
                });

                ripples.on('animationend webkitAnimationEnd mozAnimationEnd oanimationend MSAnimationEnd', function (e) {
                    jQuery(e.currentTarget).removeClass('is-active');
                });
                loginFormDiv.find('#username').focus();

                var slider = jQuery('.bxslider').bxSlider({
                    auto: true,
                    pause: 4000,
                    nextText: "",
                    prevText: "",
                    autoHover: true
                });
                jQuery('.bx-prev, .bx-next, .bx-pager-item').live('click', function () {
                    slider.startAuto();
                });
                jQuery('.bx-wrapper .bx-viewport').css('background-color', 'transparent');
                jQuery('.bx-wrapper .bxslider li').css('text-align', 'left');
                jQuery('.bx-wrapper .bx-pager').css('bottom', '-40px');

                var params = {
                    theme: 'dark-thick',
                    setHeight: '100%',
                    advanced: {
                        autoExpandHorizontalScroll: true,
                        setTop: 0
                    }
                };
                jQuery('.scrollContainer').mCustomScrollbar(params);
            });
        </script>
    </div>
{/strip}