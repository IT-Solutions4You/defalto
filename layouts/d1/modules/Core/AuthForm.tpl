{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
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
    <p>{vtranslate('Client Access Expire', $QUALIFIED_MODULE)}: <input type="text" value="{$EXPIRE_DATE}" readonly="readonly"></p>
    <p>{vtranslate('Authorization Message', $QUALIFIED_MODULE)}: <input type="text" value="{$AUTHORIZATION_MESSAGE}" readonly="readonly"></p>
    <button type="button" onclick="javascript:window.close('','_parent','');">{vtranslate('Close', $QUALIFIED_MODULE)}</button>
</form>
</body>
</html>