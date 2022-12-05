<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class EMAILMaker_SendEmails_View extends Vtiger_Index_View
{

    public $EMAILMaker = false;

    public function process(Vtiger_Request $request)
    {
        require_once('include/utils/utils.php');
        require_once('include/logging.php');
        require_once('include/database/PearDatabase.php');
        require_once('modules/Emails/Emails.php');
        require_once('modules/EMAILMaker/EMAILMaker.php');
        include_once('vtlib/Vtiger/Mailer.php');

        $this->EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $adb = PearDatabase::getInstance();
        $sql0 = "select from_email_field from vtiger_systems where server_type=?";
        $result0 = $adb->pquery($sql0, array('email'));
        $from_email_field = $adb->query_result($result0, 0, 'from_email_field');

        $sql1 = "SELECT * FROM vtiger_emakertemplates_delay";
        $result1 = $adb->pquery($sql1, array());
        $num_rows1 = $adb->num_rows($result1);

        if ($num_rows1 > 0) {
            $is = true;
        }

        $v = "vtiger_current_version";
        $vcv = vglobal($v);

        $i = "site_URL";
        $salt = vglobal($i);

        if ($num_rows1 > 0) {
            $adb->pquery("UPDATE vtiger_emakertemplates_delay SET delay_active = ?", array("1"));
        } else {
            $adb->pquery("INSERT INTO vtiger_emakertemplates_delay (delay_active) VALUES  (?)", array());
        }

        $default_language = vglobal("default_language");
        $default_theme = vglobal("default_theme");

        $from_name = $from_email = $cc = $bcc = "";

        $result_s = $adb->pquery("SELECT * FROM vtiger_emakertemplates_settings", array());
        $phpmailer_version = $adb->query_result($result_s, 0, "phpmailer_version");

        $sql4 = "SELECT me.*, me.me_subject as subject, tpl.body, cv.entitytype AS pmodule, field.tablename AS email_tablename, field.columnname AS email_columname 
                     FROM vtiger_emakertemplates_me AS me 
                     INNER JOIN vtiger_emakertemplates AS tpl USING(templateid)
                     INNER JOIN vtiger_customview AS cv ON cv.cvid = me.listid
                     INNER JOIN vtiger_tab AS tab ON tab.name = cv.entitytype
                     LEFT JOIN vtiger_field as field ON me.email_fieldname = field.fieldname AND field.tabid = tab.tabid
                     WHERE me.start_of <= now() AND me.status = 'not started' AND me.deleted = '0'";
        $result4 = $adb->pquery($sql4, array());
        $num_rows4 = $adb->num_rows($result4);

        if ($num_rows4 > 0) {
            while ($row = $adb->fetchByAssoc($result4)) {
                $templateid = $row["templateid"];

                $row["type"] = "2";
                $row["pdf_template_ids"] = $row["pdf_language"] = "";

                $Attachments = array();
                $sql10 = "SELECT vtiger_seattachmentsrel.attachmentsid as documentid FROM vtiger_notes 
                              INNER JOIN vtiger_crmentity 
                                 ON vtiger_crmentity.crmid = vtiger_notes.notesid
                              INNER JOIN vtiger_seattachmentsrel 
                                 ON vtiger_seattachmentsrel.crmid = vtiger_notes.notesid   
                              INNER JOIN vtiger_emakertemplates_documents 
                                 ON vtiger_emakertemplates_documents.documentid = vtiger_notes.notesid
                              WHERE vtiger_crmentity.deleted = '0' AND vtiger_emakertemplates_documents.templateid = ?";
                $result10 = $adb->pquery($sql10, array($templateid));
                $num_rows10 = $adb->num_rows($result10);

                if ($num_rows10 > 0) {
                    $Attachments = array();

                    while ($row10 = $adb->fetchByAssoc($result10)) {
                        $Attachments[] = $row10["documentid"];
                    }

                    $row["attachments"] = "1";
                    $row["att_documents"] = implode(",", $Attachments);
                } else {
                    $row["attachments"] = "0";
                    $row["att_documents"] = "";
                }

                if ($row["esentid"] == "") {
                    $sql6 = "INSERT INTO vtiger_emakertemplates_sent (from_name,from_email,subject,body,type,ids_for_pdf,pdf_template_ids,pdf_language,userid,attachments,att_documents,drip_group,saved_drip_delay,drip_delay,total_sent_emails,related_to,pmodule) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                    $adb->pquery($sql6, array($row["from_name"], $row["from_email"], $row["subject"], $row["body"], $row["type"], "", $row["pdf_template_ids"], $row["pdf_language"], $row["userid"], $row["attachments"], $row["att_documents"], "0", "0", "0", "0", "", $row["pmodule"]));

                    $row["esentid"] = $adb->database->Insert_ID("vtiger_emakertemplates_sent");

                    $sql10 = "UPDATE vtiger_emakertemplates_me SET esentid = ? WHERE meid = ?";
                    $adb->pquery($sql10, array($row["esentid"], $row["meid"]));
                }

                unset($current_user);

                $current_user = CRMEntity::getInstance('Users');
                $current_user->retrieveCurrentUserInfoFromFile($row['userid']);

                if (!$current_user) {
                    $current_user = Users::getActiveAdminUser();
                }

                $_SESSION["authenticated_user_id"] = $current_user->id;

                if (!empty($current_user->theme)) {
                    $theme = $current_user->theme;
                } else {
                    $theme = $default_theme;
                }

                $_SESSION['vtiger_authenticated_user_theme'] = $theme;

                if (!empty($current_user->language)) {
                    $current_language = $current_user->language;
                } else {
                    $current_language = $default_language;
                }
                $_SESSION['authenticated_user_language'] = $current_language;

                $queryGenerator = new QueryGenerator($row["pmodule"], $current_user);
                $queryGenerator->initForCustomViewById($row["listid"]);

                if ($row["email_columname"] != "") {
                    $queryGenerator->addCondition($row["email_columname"], '', 'n', 'AND');
                }

                $query7 = $queryGenerator->getQuery();
                list($sql7a, $sql7b) = explode(" FROM ", $query7);

                $sql7 = "SELECT vtiger_crmentity.crmid";

                if ($row["pmodule"] == "Contacts") {
                    $sql7 .= ", vtiger_contactdetails.emailoptout";
                } elseif ($row["pmodule"] == "Accounts") {
                    $sql7 .= ", vtiger_account.emailoptout";
                } else {
                    $sql7 .= ", '0' AS emailoptout";
                }

                if ($row["email_columname"] != "") {
                    $columname = $row["email_tablename"] . "." . $row["email_columname"];
                    $sql7 .= ", " . $columname;
                } elseif ($row["pmodule"] == "Contacts") {
                    $sql7 .= ", vtiger_contactdetails.email, vtiger_contactdetails.otheremail, vtiger_contactdetails.secondaryemail";
                } elseif ($row["pmodule"] == "Accounts") {
                    $sql7 .= ", vtiger_account.email1, vtiger_account.email2";
                } elseif ($row["pmodule"] == "Leads") {
                    $sql7 .= ", vtiger_leaddetails.email, vtiger_leaddetails.secondaryemail ";
                } elseif ($row["pmodule"] == "Vendors") {
                    $sql7 .= ", vtiger_vendor.email ";
                }

                $sql7 .= " FROM " . $sql7b;
                $result7 = $adb->pquery($sql7, array());
                $total_entries = $adb->num_rows($result7);

                $unsubscribes = 0;

                while ($row7 = $adb->fetchByAssoc($result7)) {
                    $result12 = $adb->pquery("SELECT emailid FROM vtiger_emakertemplates_emails WHERE esentid = ? AND pid = ?", array($row["esentid"], $row7["crmid"]));
                    $num_rows12 = $adb->num_rows($result12);

                    if (!$num_rows12) {
                        if ($row["email_columname"] != "") {
                            $to_email = $row7[$row["email_columname"]];
                        } else {
                            $to_email = $this->getMERecipientEmail($row["pmodule"], $row7);
                        }
                        $def_charset = vglobal("default_charset");
                        $to_email = html_entity_decode($to_email, ENT_QUOTES, $def_charset);
                        if ($row7["emailoptout"] == "1") {
                            $sql13 = "INSERT INTO vtiger_emakertemplates_emails (esentid,pid,email,cc,bcc,cc_ids,bcc_ids,status,error) VALUES (?,?,?,?,?,?,?,?,?)";
                            $adb->pquery($sql13, array($row["esentid"], $row7["crmid"], "massemail|" . $to_email, "", "", "", "", "1", "unsubscribes"));
                            $adb->pquery("UPDATE vtiger_emakertemplates_me SET unsubscribes = unsubscribes + 1 WHERE meid = ?", array($row["meid"]));
                        } elseif ($to_email != "") {
                            $sql8 = "INSERT INTO vtiger_emakertemplates_emails (esentid,pid,email,email_address,cc,bcc,cc_ids,bcc_ids,status,parent_id,email_send_date) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
                            $adb->pquery($sql8, array($row["esentid"], $row7["crmid"], "massemail|" . $to_email, $to_email, "", "", "", "", "0", "", ""));
                            $adb->pquery("UPDATE vtiger_emakertemplates_sent SET total_emails = total_emails + 1 WHERE esentid = ?", array($row["esentid"]));
                        }
                    }
                }
                $adb->pquery("UPDATE vtiger_emakertemplates_me SET status = 'in progress', total_entries = ? WHERE meid = ?", array($total_entries, $row["meid"]));
            }
        }

        $sql10 = "SELECT me.meid, me.language, me.max_limit, sent.* FROM vtiger_emakertemplates_me AS me 
                    INNER JOIN vtiger_emakertemplates_sent AS sent 
                        ON me.esentid = sent.esentid
                    INNER JOIN vtiger_emakertemplates_emails AS emails
                        ON sent.esentid = emails.esentid 
                    WHERE me.status = 'in progress' 
                    AND emails.status = '0' AND emails.deleted = '0' GROUP BY me.meid HAVING (max(emails.email_send_date) < date_add(now(),interval -15 minute) OR max(emails.email_send_date) IS NULL )";
        $result10 = $adb->pquery($sql10, array());
        $num_rows10 = $adb->num_rows($result10);

        if ($num_rows10 > 0) {
            $result17 = $adb->pquery("select from_email_field from vtiger_systems where server_type=?", array('email'));
            $from_email_field = $adb->query_result($result17, 0, 'from_email_field');

            while ($row = $adb->fetchByAssoc($result10)) {
                $set_language = $current_language;
                if ($row["language"] != "") {
                    $set_language = $row["language"];
                }
                $this->sendEMAILMakerEmails($from_email_field, $current_user, $row, $set_language);
            }
        }

        $result12 = $adb->pquery("SELECT meid, esentid FROM vtiger_emakertemplates_me WHERE status = ?", array('in progress'));
        $num_rows12 = $adb->num_rows($result12);

        if ($num_rows12 > 0) {
            while ($row = $adb->fetchByAssoc($result12)) {
                $sql13 = "SELECT * FROM vtiger_emakertemplates_emails WHERE status = '0' AND deleted = '0' AND esentid = ?";
                $result13 = $adb->pquery($sql13, array($row["esentid"]));
                $num_rows13 = $adb->num_rows($result13);

                if ($num_rows13 == 0) {
                    $sql14 = "UPDATE vtiger_emakertemplates_me SET status = 'finished' WHERE meid = ?";
                    $adb->pquery($sql14, array($row["meid"]));
                }
            }
        }
    }

    public function getMERecipientEmail($pmodule, $row7)
    {
        $to_email = "";

        if ($pmodule == "Contacts") {
            if ($row7["email"] != "") {
                $to_email = $row7["email"];
            } elseif ($row7["otheremail"] != "") {
                $to_email = $row7["otheremail"];
            } elseif ($row7["secondaryemail"] != "") {
                $to_email = $row7["secondaryemail"];
            }
        } elseif ($pmodule == "Accounts") {
            if ($row7["email1"] != "") {
                $to_email = $row7["email1"];
            } elseif ($row7["email2"] != "") {
                $to_email = $row7["email2"];
            }
        } elseif ($pmodule == "Leads") {
            if ($row7["email"] != "") {
                $to_email = $row7["email"];
            } elseif ($row7["secondaryemail"] != "") {
                $to_email = $row7["secondaryemail"];
            }
        } elseif ($pmodule == "Vendors") {
            if ($row7["email"] != "") {
                $to_email = $row7["email"];
            }
        }
        return $to_email;
    }

    public function sendEMAILMakerEmails($from_email_field, $current_user, $ED, $language)
    {
        $email_sending_pause = 0;
        if (class_exists(EMAILMaker_EmailSendingPause_Helper)) {
            $email_sending_pause = EMAILMaker_EmailSendingPause_Helper::getEmailSendingPause();
        }

        $default_charset = vglobal("default_charset");
        $adb = PearDatabase::getInstance();

        $esentid = $ED["esentid"];
        $from_name = html_entity_decode($ED["from_name"], ENT_QUOTES, $default_charset);
        $from_name = html_entity_decode($from_name, ENT_QUOTES, $default_charset);
        $from_email = $ED["from_email"];
        $type = $ED["type"];
        $load_subject = html_entity_decode($ED["subject"], ENT_QUOTES, $default_charset);
        $load_body = html_entity_decode($ED["body"], ENT_QUOTES, $default_charset);
        $total_emails = $ED["total_emails"];
        $pdf_template_ids = $ED["pdf_template_ids"];
        $pdf_language = $ED["pdf_language"];
        $attachments = $ED["attachments"];
        $att_documents = $ED["att_documents"];
        $pmodule = $ED["pmodule"];

        $sql2 = "SELECT * FROM vtiger_emakertemplates_emails WHERE esentid = ? AND status = 0 AND deleted = '0'";

        $limit = $this->getMELimitForSQL($ED);
        if ($limit != "") {
            if ($limit > 0) {
                $sql2 .= " LIMIT 0," . $limit;
            } else {
                return false;
            }
        }

        $result2 = $adb->pquery($sql2, array($esentid));
        $num_rows2 = $adb->num_rows($result2);

        if ($num_rows2 > 0) {
            while ($row2 = $adb->fetchByAssoc($result2)) {
                $mail_status = false;
                $mailer = false;
                $semailid = $row2["emailid"];

                $sql_u1 = "UPDATE vtiger_emakertemplates_emails SET email_send_date = now() WHERE (email_send_date IS NULL OR email_send_date = '0000-00-00 00:00:00' OR email_send_date < date_add(now(),interval -15 minute)) AND status = '0' AND deleted = '0' AND emailid = ?";
                $adb->pquery($sql_u1, array($semailid));
                $is_updated = $adb->database->affected_rows();

                if ($is_updated == "1") {
                    $Inserted_Emails = array();
                    $pid = $row2["pid"];
                    $myid = $row2["email"];
                    $email_address = $row2["email_address"];
                    $cc = $row2["cc"];
                    $bcc = $row2["bcc"];
                    $cc_ids = $row2["cc_ids"];
                    $bcc_ids = $row2["bcc_ids"];
                    $parent_id = $row2["parent_id"];

                    list($mycrmid, $temp) = explode("|", $myid, 2);

                    if ($mycrmid == "email") {
                        if ($email_address == "") {
                            $email_address = $temp;
                        }
                        $mycrmid = $rmodule = $track_URL = "";
                        $saved_toid = $email_address;
                    } elseif ($mycrmid == "massemail") {
                        if ($email_address == "") {
                            $email_address = $temp;
                        }
                        $mycrmid = $pid;
                        $rmodule = $pmodule;
                        $saved_toid = $email_address;
                    } else {
                        if ($temp == "-1") {
                            $rmodule = "Users";
                        } else {
                            $rmodule = getSalesEntityType($mycrmid);
                        }

                        $saved_toid = "";
                        if ($temp == "-1") {
                            if ($email_address == "") {
                                $email_address = $adb->query_result($adb->pquery("select email1 from vtiger_users where id=?", array($mycrmid)), 0, 'email1');
                            }
                            $user_full_name = getUserFullName($mycrmid);
                            $saved_toid = $user_full_name . "<" . $email_address . ">";
                        } elseif ($mycrmid != "") {
                            if ($email_address == "") {
                                $email_address = $this->EMAILMaker->getEmailToAdressat($mycrmid, $temp, $rmodule);
                            }

                            $entityNames = getEntityName($rmodule, $mycrmid);
                            $pname = $entityNames[$mycrmid];

                            $saved_toid = $pname . "<" . $email_address . ">";
                        } else {
                            $saved_toid = $email_address;
                        }
                    }

                    if ($pid != "" && $pid != "0") {
                        $formodule = getSalesEntityType($pid);
                    } else {
                        $formodule = "";
                    }

                    $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstance($formodule, $pid, $language, $mycrmid, $rmodule);
                    $EMAILContentModel->setSubject($load_subject);
                    $EMAILContentModel->setBody($load_body);
                    $EMAILContentModel->getContent();

                    $subject = $EMAILContentModel->getSubject();
                    $subject = html_entity_decode($subject, ENT_QUOTES, $default_charset);

                    $body = $EMAILContentModel->getBody();
                    $preview_body = $EMAILContentModel->getPreview();
                    $Email_Images = $EMAILContentModel->getEmailImages();

                    $focus = CRMEntity::getInstance("Emails");

                    if ($parent_id != "" && $parent_id != "0") {
                        $focus->retrieve_entity_info($parent_id, "Emails");
                        $focus->id = $parent_id;
                    }

                    $focus->column_fields["subject"] = $subject;
                    $focus->column_fields["description"] = $preview_body;
                    $focus->column_fields["date_start"] = date('Y-m-d');
                    $focus->column_fields["time_start"] = gmdate("H:i:s");
                    $focus->column_fields['from_email'] = $from_email;

                    if ($parent_id == "" || $parent_id == "0") {
                        $focus->filename = $focus->parent_id = $focus->parent_type = "";
                        $focus->column_fields["assigned_user_id"] = $current_user->id;
                        $focus->column_fields["activitytype"] = "Emails";

                        $focus->column_fields["parent_id"] = $mycrmid;
                        $focus->column_fields["saved_toid"] = $saved_toid;
                        $focus->column_fields["ccmail"] = $cc;
                        $focus->column_fields["bccmail"] = $bcc;
                        $focus->save("Emails");

                        if ($mycrmid != "") {
                            $Inserted_Emails[] = $mycrmid;
                            $rel_sql = 'replace into vtiger_seactivityrel values(?,?)';
                            $rel_params = array($mycrmid, $focus->id);
                            $adb->pquery($rel_sql, $rel_params);
                        }

                        if ($cc_ids != "") {
                            $CC_IDs = explode(";", $cc_ids);

                            foreach ($CC_IDs as $email_crm_id) {
                                if (!in_array($email_crm_id, $Inserted_Emails)) {
                                    $Inserted_Emails[] = $email_crm_id;
                                    $rel_sql_2 = 'replace into vtiger_seactivityrel values(?,?)';
                                    $rel_params_2 = array($email_crm_id, $focus->id);
                                    $adb->pquery($rel_sql_2, $rel_params_2);
                                }
                            }
                        }

                        if ($bcc_ids != "") {
                            $BCC_IDs = explode(";", $bcc_ids);

                            foreach ($BCC_IDs as $email_crm_id) {
                                if (!in_array($email_crm_id, $Inserted_Emails)) {
                                    $Inserted_Emails[] = $email_crm_id;
                                    $rel_sql_3 = 'replace into vtiger_seactivityrel values(?,?)';
                                    $rel_params_3 = array($email_crm_id, $focus->id);
                                    $adb->pquery($rel_sql_3, $rel_params_3);
                                }
                            }
                        }
                        $parent_id = $focus->id;
                        $adb->pquery("UPDATE vtiger_emakertemplates_emails SET parent_id = ? WHERE emailid = ?", array($parent_id, $semailid));
                    }

                    if ($attachments == "1") {
                        $result_attch = $adb->pquery("SELECT * FROM vtiger_emakertemplates_attch WHERE esentid = ?", array($esentid));
                        $num_attch = $adb->num_rows($result_attch);

                        if ($num_attch > 0) {
                            while ($row_attch = $adb->fetchByAssoc($result_attch)) {
                                $this->SaveAttachmentIntoEmail($current_user, $parent_id, $row_attch["filename"], $row_attch["type"], $row_attch["file_desc"]);
                            }
                        }
                    }

                    if ($att_documents != "") {
                        $this->saveDocumentsIntoEmail($parent_id, $att_documents);
                    }

                    $pos = strpos($body, '$logo$');
                    if ($pos !== false) {
                        $body = str_replace('$logo$', '<img src="cid:logo" />', $body);
                        $logo = 1;
                    }

                    if ($temp == "-1") {
                        $rmodule = 'Users';

                        $mailer = $this->send_mail($email_address, $from_name, $from_email, $from_email_field, $subject, $body, $cc, $bcc, $parent_id, $logo, $Email_Images, $phpmailer_version);
                        $mail_status = $mailer->Send(true);
                        $mail_status_str .= $email_address . "=" . $mail_status . "&&&";
                    } else {
                        $emailid = $parent_id;

                        if ($mycrmid == "") {
                            $mailer = $this->send_mail($email_address, $from_name, $from_email, $from_email_field, $subject, $body, $cc, $bcc, $parent_id, $logo, $Email_Images, $phpmailer_version);
                            $mail_status = $mailer->Send(true);
                            $mail_status_str .= $email_address . "=" . $mail_status . "&&&";

                            if ($mail_status != 1) {
                                $errorheader2 = 1;
                            }
                        } else {
                            $body .= $this->EMAILMaker->getTrackImageDetails($mycrmid, $emailid);

                            if ($email_address != '') {
                                $mail_status = false;
                                if (isPermitted($rmodule, 'DetailView', $mycrmid) == 'yes') {
                                    $mailer = $this->send_mail($email_address, $from_name, $from_email, $from_email_field, $subject, $body, $cc, $bcc, $parent_id, $logo, $Email_Images, $phpmailer_version);
                                    $mail_status = $mailer->Send(true);
                                } else {
                                    $mail_error = "Permission denied";
                                }

                                $mail_status_str .= $email_address . "=" . $mail_status . "&&&";

                                if ($mail_status != 1) {
                                    $errorheader2 = 1;
                                }
                            }
                        }
                    }

                    $adb->pquery("INSERT INTO vtiger_emakertemplates_contents (activityid,emailid,content) VALUES (?,?,?)", array($parent_id, $semailid, $preview_body));
                    $new_body = '<script>jQuery(document).ready(function() { showEMAILMakerEmailPreview(' . $parent_id . ')});</script><a href="javascript:showEMAILMakerEmailPreview(' . $parent_id . ');">Email content</a>';

                    $adb->pquery("UPDATE vtiger_crmentity SET description = ? WHERE crmid = ?", array($new_body, $parent_id));

                    $sql_u3 = "UPDATE vtiger_emakertemplates_emails SET status = status + 1, email_send_date = now()";
                    if (!$mail_status) {
                        if ($mailer) {
                            $mail_error = $mailer->getError();
                        }
                        $sql_u3 .= ", error = '" . $mail_error . "'";
                    }
                    $sql_u3 .= " WHERE emailid = ?";
                    $adb->pquery($sql_u3, array($semailid));

                    if ($mail_status) {
                        $adb->pquery("UPDATE vtiger_emaildetails SET email_flag = 'SENT' WHERE emailid = ?", array($parent_id));
                        $adb->pquery("UPDATE vtiger_emakertemplates_sent SET total_sent_emails = total_sent_emails + 1 WHERE esentid = ?", array($esentid));
                    }

                    if (class_exists(EMAILMaker_AfterSend_Helper)) {
                        EMAILMaker_AfterSend_Helper::runAfterSend($parent_id, $mail_status, $mailer);
                    }

                    unset($focus);
                    if ($email_sending_pause > 0) {
                        sleep($email_sending_pause);
                    }
                }
            }
        }
    }

    public function getMELimitForSQL($ED)
    {
        $adb = PearDatabase::getInstance();
        $adb->setDebug(true);
        $limit = $ED["max_limit"];
        $esentid = $ED["esentid"];

        if ($limit > 0) {
            $sql = "SELECT count(emailid) as total_emails FROM vtiger_emakertemplates_emails WHERE esentid = ? AND status = 1 AND deleted = '0' AND email_send_date > date_add(now(),interval -45 minute) GROUP BY esentid";  //interval -1 hour
            $result = $adb->pquery($sql, array($esentid));
            $num_rows = $adb->num_rows($result);

            if ($num_rows > 0) {
                $total_emails = $adb->query_result($result, 0, "total_emails");
                $new_limit = $limit - $total_emails;

                if (!$new_limit) {
                    $new_limit = -1;
                }
                return $new_limit;
            }
        } else {
            $limit = "";
        }

        return $limit;
    }

    public function SaveAttachmentIntoEmail($current_user, $id, $file_name, $filetype, $filetmp_name)
    {
        $adb = PearDatabase::getInstance();
        $adb->setDebug(true);
        $date_var = date("Y-m-d H:i:s");
        $ownerid = $current_user->id;
        $current_id = $adb->getUniqueID("vtiger_crmentity");
        $filename = ltrim(basename(" " . $file_name));
        $upload_file_path = decideFilePath();

        if (copy($filetmp_name, $upload_file_path . $current_id . "_" . $file_name)) {
            $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
            $params1 = array($current_id, $current_user->id, $ownerid, "Email Attachment", "", $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));

            $adb->pquery($sql1, $params1);

            if (EMAILMaker_EMAILMaker_Model::isStoredName()) {
                $sql2 = "insert into vtiger_attachments(attachmentsid, name, storedname, type, path) values(?, ?, ?, ?, ?)";
                $params2 = array($current_id, $filename, $filename, $filetype, $upload_file_path);
            } else {
                $sql2 = "insert into vtiger_attachments(attachmentsid, name, type, path) values(?, ?, ?, ?)";
                $params2 = array($current_id, $filename, $filetype, $upload_file_path);
            }

            $adb->pquery($sql2, $params2);

            $sql3 = 'replace into vtiger_seattachmentsrel values(?,?)';
            $adb->pquery($sql3, array($id, $current_id));
        }
    }

    public function saveDocumentsIntoEmail($id, $documents)
    {
        $adb = PearDatabase::getInstance();
        $adb->setDebug(true);
        $Documents = explode(",", $documents);

        foreach ($Documents as $document_id) {
            if ($document_id != "") {
                $sql1 = 'replace into vtiger_seattachmentsrel values(?,?)';
                $adb->pquery($sql1, array($id, $document_id));
            }
        }
    }

    public function send_mail($emailadd, $from_name, $from_email, $from_email_field, $subject, $body, $cc, $bcc, $parent_id, $logo, $Email_Images, $phpmailer_version)
    {
        $mailer = new Emails_Mailer_Model();
        $mailer->IsHTML(true);
        $replyToEmail = $from_email;

        if (!empty($from_email_field)) {
            $from_email = $from_email_field;
        }
        $mailer->ConfigSenderInfo($from_email, $from_name, $replyToEmail);
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->AddAddress($emailadd);
        $mailer = $this->EMAILMaker->addAllAttachments($mailer, $parent_id);

        if (count($Email_Images) > 0) {
            foreach ($Email_Images as $cid => $cdata) {
                $mailer->AddEmbeddedImage($cdata["path"], $cid, $cdata["name"]);
            }
        }
        if ($logo) {
            $mailer->AddEmbeddedImage(vimage_path('logo_mail.jpg'), 'logo', 'logo.jpg', 'base64', 'image/jpg');
        }

        $ccs = empty($cc_string) ? array() : explode(',', $cc_string);
        $bccs = empty($bcc_string) ? array() : explode(',', $bcc_string);

        foreach ($ccs as $cc) {
            $mailer->AddCC($cc);
        }
        foreach ($bccs as $bcc) {
            $mailer->AddBCC($bcc);
        }

        return $mailer;
    }

    public function controlIfIsSent($emailid)
    {
        $adb = PearDatabase::getInstance();
        $adb->setDebug(true);
        $sql_email_d = "SELECT emailid FROM vtiger_emakertemplates_emails WHERE emailid = ? AND (deleted = '1' OR status > 0)";
        $result_email_d = $adb->pquery($sql_email_d, array($emailid));
        $num_rows_email_d = $adb->num_rows($result_email_d);

        if ($num_rows_email_d > 0) {
            return true;
        } else {
            return false;
        }
    }
}
