{*/*********************************************************************************
* The content of this file is subject to the ITS4YouEmails license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/*}
<html>
<head>
    <style>
        * {
            font-family: Helvetica, Arial, FreeSans, san-serif;
        }
        form {
            max-width: 600px;
            margin: 0.3em auto;
        }
        input {
            border-radius: 0.5rem;
            display: block;
            padding: 0.5em 1em;
            width: 100%;
            border: 1px solid gray;
        }
        button {
            font-weight: bold;
            border-radius: 0.5rem;
            padding: 1em 0.5em;
            border: 0;
            background: gray;
            color: white;
            width: 100%;
        }
    </style>
</head>
<body>
<form method="post">
    <h1>{vtranslate('Provider Informations', $QUALIFIED_MODULE)}</h1>
    <p>{vtranslate('Redirect Uri', $QUALIFIED_MODULE)}: <input type="text" value="{$REDIRECT_URI}" readonly="readonly"></p>
    <p>{vtranslate('Provider', $QUALIFIED_MODULE)}: <input type="text" value="{$PROVIDER}" readonly="readonly"></p>
    <p>{vtranslate('Client Id', $QUALIFIED_MODULE)}: <input type="text" value="{$CLIENT_ID}" readonly="readonly"><p>
    <p>{vtranslate('Client Secret', $QUALIFIED_MODULE)}: <input type="text" value="{$CLIENT_SECRET}" readonly="readonly"></p>
    <p>{vtranslate('Client Token', $QUALIFIED_MODULE)}: <input type="text" value="{$TOKEN}" readonly="readonly"></p>
    <p>{vtranslate('Client Access Token', $QUALIFIED_MODULE)}: <input type="text" value="{$ACCESS_TOKEN}" readonly="readonly"></p>
    <button type="button" onclick="javascript:window.close('','_parent','');">{vtranslate('Close', $QUALIFIED_MODULE)}</button>
</form>
</body>
</html>