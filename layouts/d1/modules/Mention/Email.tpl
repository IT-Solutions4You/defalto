{*/* * *******************************************************************************
 * The content of this file is subject to the ITS4YouSignature license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */*}
{strip}
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="x-apple-disable-message-reformatting">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <style>
            .commentArea b {
                padding: 0 2px;
                margin-right: 2px;
                background: #dae6f1;
                border-radius: 2px;
            }
        </style>
    </head>
    <body>
    <p>{$CURRENT_USER->getName()|escape:'html'} {$MESSAGE_MENTIONED_YOU|escape:'html'}</p>
    <h3>{$RECORD_TITLE|escape:'html'}</h3>
    <p class="commentArea">{$RECORD_MESSAGE}</p>
    <a href="{$RECORD_URL|escape:'html'}">{$MESSAGE_CLICK_HERE|escape:'html'}</a>
    </body>
    </html>
{/strip}
