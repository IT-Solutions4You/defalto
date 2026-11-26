{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
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
