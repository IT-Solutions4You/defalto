<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

//Maximum number of Mailboxes in mail converter
$max_mailboxes = 3;

/**
 * Configure runtime connectors to customization in core files.
 * Ex: Sessions are currently handled by PHP default session handler.
 *     This can be customized using runtime connector hook and avoid core file modifications.
 *     array('session' => 'Vtiger_CustomSession_Handler')
 */
$runtime_connectors = [];

//Password Regex for validation
$validation_regex = ['password_regex' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})'];