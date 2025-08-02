<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class WebServiceErrorCode
{
    public static string $SESSLIFEOVER = 'SESSION_EXPIRED';
    public static string $REFERENCEINVALID = 'REFERENCE_INVALID';
    public static string $SESSIONIDLE = 'SESSION_LEFT_IDLE';
    public static string $SESSIONIDINVALID = 'INVALID_SESSIONID';
    public static string $INVALIDUSERPWD = 'INVALID_USER_CREDENTIALS';
    public static string $AUTHREQUIRED = 'AUTHENTICATION_REQUIRED';
    public static string $AUTHFAILURE = 'AUTHENTICATION_FAILURE';
    public static string $ACCESSDENIED = 'ACCESS_DENIED';
    public static string $DATABASEQUERYERROR = 'DATABASE_QUERY_ERROR';
    public static string $MANDFIELDSMISSING = 'MANDATORY_FIELDS_MISSING';
    public static string $INVALIDID = 'INVALID_ID_ATTRIBUTE';
    public static string $QUERYSYNTAX = 'QUERY_SYNTAX_ERROR';
    public static string $INVALIDTOKEN = 'INVALID_AUTH_TOKEN';
    public static string $ACCESSKEYUNDEFINED = 'ACCESSKEY_UNDEFINED';
    public static string $RECORDNOTFOUND = 'RECORD_NOT_FOUND';
    public static string $UNKNOWNOPERATION = 'UNKNOWN_OPERATION';
    public static string $INTERNALERROR = 'INTERNAL_SERVER_ERROR';
    public static string $OPERATIONNOTSUPPORTED = 'OPERATION_NOT_SUPPORTED';
    public static string $UNKOWNENTITY = 'UNKOWN_ENTITY';
    public static string $INVALID_POTENTIAL_FOR_CONVERT_LEAD = 'INVALID_POTENTIAL_FOR_CONVERTLEAD';
    public static string $LEAD_ALREADY_CONVERTED = 'LEAD_ALREADY_CONVERTED';
    public static string $LEAD_RELATED_UPDATE_FAILED = 'LEAD_RELATEDLIST_UPDATE_FAILED';
    public static string $FAILED_TO_CREATE_RELATION = 'FAILED_TO_CREATE_RELATION';
    public static string $FAILED_TO_MARK_CONVERTED = 'FAILED_TO_MARK_LEAD_CONVERTED';
    public static string $INVALIDOLDPASSWORD = 'INVALID_OLD_PASSWORD';
    public static string $NEWPASSWORDMISMATCH = 'NEW_PASSWORD_MISMATCH';
    public static string $CHANGEPASSWORDFAILURE = 'CHANGE_PASSWORD_FAILURE';
    public static string $POTENTIAL_ALREADY_CONVERTED = 'POTENTIAL_ALREADY_CONVERTED';
    public static string $FAILED_TO_MARK_POTENTIAL_CONVERTED = 'FAILED_TO_MARK_POTENTIAL_CONVERTED';
    public static string $POTENTIAL_RELATED_UPDATE_FAILED = 'POTENTIAL_RELATEDLIST_UPDATE_FAILED';
    public static string $FAILED_TO_CREATE = 'FAILED_TO_CREATE';
    public static string $INACTIVECURRENCY = 'CURRENCY_INACTIVE';
    public static string $PASSWORDNOTSTRONG = 'PASSWORD_NOT_STRONG';
    public static string $FAILED_TO_UPDATE = 'FAILED_TO_UPDATE';
}