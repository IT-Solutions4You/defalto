<?php
////////////////////////////////////////////////////
// PHPMailer - PHP email class
//
// Class for sending email using either
// sendmail, PHP mail(), or SMTP.  Methods are
// based upon the standard AspEmail(tm) classes.
//
// Copyright (C) 2001 - 2003  Brent R. Matzelle
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

/**
 * PHPMailer - PHP email transport class
 * @package PHPMailer
 * @author Brent R. Matzelle
 * @copyright 2001 - 2003 Brent R. Matzelle
 */

include_once 'config.php';
require_once 'vendorCheck.php';
require_once 'vendor/autoload.php';
include_once 'include/Webservices/Relation.php';

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

require_once 'include/utils/utils.php';
require_once 'include/logging.php';

global $adb, $log, $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME, $current_language;
$log = Logger::getLogger('SendSupportNotification');
$log->debug(" invoked SendSupportNotification ");

// retrieve the translated strings.
$app_strings = return_application_language($current_language);

$status = '';
//To send email notification before a week
$query="select vtiger_contactdetails.contactid,vtiger_contactdetails.email,vtiger_contactdetails.firstname,vtiger_contactdetails.lastname,contactid  from vtiger_customerdetails inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_customerdetails.customerid inner join vtiger_contactdetails on vtiger_contactdetails.contactid=vtiger_customerdetails.customerid  where vtiger_crmentity.deleted=0 and support_end_date=DATE_ADD(now(), INTERVAL 1 WEEK)";
$result = $adb->pquery($query, array());

while ($result_set = $adb->fetch_array($result)) {
    $content = getcontent_week($result_set['contactid']);
    $body = $content['body'];
    $body = str_replace('$logo$', '<img src="cid:logo" />', $body);
    $subject = $content['subject'];

    $mailer = ITS4YouEmails_Mailer_Model::getCleanInstance();
    $mailer->retrieveSMTPVtiger();
    $mailer->setFrom($HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME);
    $mailer->addBCC($HELPDESK_SUPPORT_EMAIL_ID);
    $mailer->addAddress($result_set['email'], 'Support');
    $mailer->Subject = $subject;
    $mailer->Body = $body;
    $mailer->isHTML();
    $mailer->send();
}

//comment / uncomment this line if you want to hide / show the sent mail status
//showstatus($status);
$log->debug(" Send Support Notification Before a week - Status: ".$status);

//To send email notification before a month
$query="select vtiger_contactdetails.contactid,vtiger_contactdetails.email,vtiger_contactdetails.firstname,vtiger_contactdetails.lastname,contactid  from vtiger_customerdetails inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_customerdetails.customerid inner join vtiger_contactdetails on vtiger_contactdetails.contactid=vtiger_customerdetails.customerid  where vtiger_crmentity.deleted=0 and support_end_date=DATE_ADD(now(), INTERVAL 1 MONTH)";
$result = $adb->pquery($query, array());

while ($result_set = $adb->fetch_array($result)) {
    $content = getcontent_month($result_set['contactid']);
    $body = $content['body'];
    $body = str_replace('$logo$', '<img src="cid:logo" />', $body);
    $subject = $content['subject'];

    $mailer = ITS4YouEmails_Mailer_Model::getCleanInstance();
    $mailer->retrieveSMTPVtiger();
    $mailer->setFrom($HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME);
    $mailer->addBCC($HELPDESK_SUPPORT_EMAIL_ID);
    $mailer->addAddress($result_set['email'], 'Support');
    $mailer->Subject = $subject;
    $mailer->Body = $body;
    $mailer->isHTML();
    $mailer->send();
}

//comment / uncomment this line if you want to hide / show the sent mail status
//showstatus($status);
$log->debug(" Send Support Notification Befoe a Month - Status: ".$status);

//used to dispaly the sent mail status
function showstatus($status)
{
	
	if($status == 1)
		echo "Mails sent successfully";
	else if($status == "")
		echo "No contacts matched";
	else
		echo "Error while sending mails: ".$status;	
}



//function used to get the header and body content of the mail to be sent.
function getcontent_month($id)
{
	global $adb;

	$moduleName = 'Contacts';
	$params = array('templatename' => 'Support end notification before a month', 'category' => 'system');
	$templateId = EMAILMaker_Record_Model::getTemplateId($params);
	$language = Vtiger_Language_Handler::getLanguage();
	$contentModel = EMAILMaker_EMAILContent_Model::getInstanceById($templateId, $language, $moduleName, $id, $id, $moduleName);
	$contentModel->getContent();

	$body = $contentModel->getBody();
	$body = getMergedDescription($body, $id, 'Contacts');
	$body = getMergedDescription($body, $id, 'Users');

	return [
		'subject' => $body->getSubject(),
		'body' => $body,
	];
}

//function used to get the header and body content of the mail to be sent.
function getcontent_week($id)
{
	$moduleName = 'Contacts';
	$params = array('templatename' => 'Support end notification before a week', 'category' => 'system');
	$templateId = EMAILMaker_Record_Model::getTemplateId($params);
	$language = Vtiger_Language_Handler::getLanguage();
	$contentModel = EMAILMaker_EMAILContent_Model::getInstanceById($templateId, $language, $moduleName, $id, $id, $moduleName);
	$contentModel->getContent();

	$body = $contentModel->getBody();
	$body = getMergedDescription($body, $id, "Contacts");
	$body = getMergedDescription($body, $id, "Users");

	return [
		'subject' => $body->getSubject(),
		'body' => $body,
	];
}