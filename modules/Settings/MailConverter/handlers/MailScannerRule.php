<?php
/*********************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/

/**
 * Scanner Rule
 */
class Settings_MailConverter_MailScannerRule_Handler {
    // id of this instance
    public $ruleid = false;
    // scanner to which this rule is linked
    public $scannerid = false;
    // from address criteria
    public $fromaddress = false;
    // to address criteria
    public $toaddress = false;
    // cc address criteria
    public $cc = false;
    // bcc address criteria
    public $bcc = false;
    // subject criteria operator
    public $subjectop = false;
    // subject criteria
    public $subject = false;
    // body criteria operator
    public $bodyop = false;
    // body criteria
    public $body = false;
    // order of this rule
    public $sequence = false;
    // is this action valid
    public $isvalid = false;
    // match criteria ALL or ANY
    public $matchusing = false;
    // assigned to user id
    public $assigned_to = false;
    // associated actions for this rule
    public $actions = false;
    // TODO we are restricting one action for one rule right now
    public $useaction = false;

    /** DEBUG functionality **/
    public $debug = false;
    function log($message) {
	global $log;
		if($log && $this->debug) { $log->debug($message); }
		else if($this->debug) echo "$message\n";
    }

    /**
     * Constructor
     */
    function __construct($forruleid) {
        $this->initialize($forruleid);
    }

    /**
     * String representation of this instance
     */
    function __toString() {
        $tostring = '';
        $tostring .= "FROM $this->fromaddress, TO $this->toaddress, CC $this->cc, BCC $this->bcc";
        $tostring .= ",SUBJECT $this->subjectop $this->subject, BODY $this->bodyop $this->body, MATCH USING, $this->matchusing";
        return $tostring;
    }

    /**
     * Initialize this instance
     */
    function initialize($forruleid) {
        global $adb;
        $result = $adb->pquery("SELECT * FROM vtiger_mailscanner_rules WHERE ruleid=? ORDER BY sequence", Array($forruleid));

        if ($adb->num_rows($result)) {
            $this->ruleid = $adb->query_result($result, 0, 'ruleid');
            $this->scannerid = $adb->query_result($result, 0, 'scannerid');
            $this->fromaddress = $adb->query_result($result, 0, 'fromaddress');
            $this->toaddress = $adb->query_result($result, 0, 'toaddress');
            $this->cc = $adb->query_result($result, 0, 'cc');
            $this->bcc = $adb->query_result($result, 0, 'bcc');
            $this->subjectop = $adb->query_result($result, 0, 'subjectop');
            $this->subject = $adb->query_result($result, 0, 'subject');
            $this->bodyop = $adb->query_result($result, 0, 'bodyop');
            $this->body = $adb->query_result($result, 0, 'body');
            $this->sequence = $adb->query_result($result, 0, 'sequence');
            $this->matchusing = $adb->query_result($result, 0, 'matchusing');
            $this->assigned_to = $adb->query_result($result, 0, 'assigned_to');
            $this->isvalid = true;
            $this->initializeActions();
            // At present we support only one action for a rule
                if(!empty($this->actions)) $this->useaction = $this->actions[0];
        }
    }

    /**
     * Initialize the actions
     */
    function initializeActions() {
        global $adb;
            if($this->ruleid) {
            $this->actions = Array();
                $actionres = $adb->pquery("SELECT actionid FROM vtiger_mailscanner_ruleactions WHERE ruleid=?",Array($this->ruleid));
            $actioncount = $adb->num_rows($actionres);
                if($actioncount) {
                    for($index = 0; $index < $actioncount; ++$index) {
                $actionid = $adb->query_result($actionres, $index, 'actionid');
                $ruleaction = new Settings_MailConverter_MailScannerAction_Handler($actionid);
                $ruleaction->debug = $this->debug;
                $this->actions[] = $ruleaction;
            }
            }
        }
    }

    /**
     * Is body rule defined?
     */
    function hasBodyRule() {
        return (!empty($this->bodyop));
    }

    /**
     * Check if the rule criteria is matching
     */
	function isMatching($matchFound1, $matchFound2 = null) {
        if ($matchFound2 === null)
            return $matchFound1;

        if ($this->matchusing == 'AND')
            return ($matchFound1 && $matchFound2);
        if ($this->matchusing == 'OR')
            return ($matchFound1 || $matchFound2);
        return false;
    }

    /**
     * Apply all the criteria.
     * @param $mailRecord
     * @param $includingBody
     * @returns false if not match is found or else all matching result found
     */
    public function applyAll($mailRecord, $includingBody = true)
    {
        $matchResults = [];
        $matchFound = null;

        if ($this->hasACondition()) {
            $subrules = ['FROM', 'TO', 'CC', 'BCC', 'SUBJECT', 'BODY'];

            foreach ($subrules as $subrule) {
                // Body rule could be defered later to improve performance
                // in that case skip it.
                if ($subrule == 'BODY' && !$includingBody) {
                    continue;
                }

                $checkMatch = $this->apply($subrule, $mailRecord);
                $matchFound = $this->isMatching($checkMatch, $matchFound);
                // Collect matching result array
                if ($matchFound && is_array($checkMatch)) {
                    $matchResults[] = $checkMatch;
                }
            }
        } else {
            $matchFound = false;
            if ($this->matchusing == 'OR') {
                $matchFound = true;
                $matchResults[] = $this->__CreateMatchResult('BLANK', '', '', '');
            }
        }

        return ($matchFound) ? $matchResults : false;
    }

    /**
     * Check if at least one condition is set for this rule.
     */
    function hasACondition() {
        $hasFromAddress = $this->fromaddress ? true : false;
        $hasToAddress = $this->toaddress ? true : false;
        $hasCcAddress = $this->cc ? true : false;
        $hasBccAddress = $this->bcc ? true : false;
        $hasSubjectOp = $this->subjectop ? true : false;
        $hasBodyOp = $this->bodyop ? true : false;
        return ($hasFromAddress || $hasToAddress || $hasCcAddress || $hasBccAddress || $hasSubjectOp || $hasBodyOp);
    }

    /**
     * Apply required condition on the mail record.
     */
    function apply($subrule, $mailRecord)
    {
        $matchFound = false;
        
        if ($this->isvalid) {
            switch (strtoupper($subrule)) {
                case 'FROM':
                    if ($this->fromaddress) {
                        if (strpos($this->fromaddress, '*') == 0) {
                            $this->fromaddress = trim($this->fromaddress, '*');
                        }
                        $matchFound = $this->find($subrule, 'Contains', $mailRecord->getFrom()[0], $this->fromaddress);
                    } else {
                        $matchFound = $this->__CreateDefaultMatchResult($subrule);
                    }
                    break;
                case 'TO':
                    if ($this->toaddress) {
                        foreach ($mailRecord->getTo() as $toEmail) {
                            $matchFound = $this->find($subrule, 'Contains', $toEmail, $this->toaddress);
                            if ($matchFound) {
                                break;
                            }
                        }
                    } else {
                        $matchFound = $this->__CreateDefaultMatchResult($subrule);
                    }
                    break;
                case 'CC':
                    if ($this->cc) {
                        foreach ($mailRecord->getCC() as $toEmail) {
                            $matchFound = $this->find($subrule, 'Contains', $toEmail, $this->cc);
                            if ($matchFound) {
                                break;
                            }
                        }
                    } else {
                        $matchFound = $this->__CreateDefaultMatchResult($subrule);
                    }
                    break;
                case 'BCC':
                    if ($this->bcc) {
                        foreach ($mailRecord->getBCC() as $toEmail) {
                            $matchFound = $this->find($subrule, 'Contains', $toEmail, $this->bcc);
                            if ($matchFound) {
                                break;
                            }
                        }
                    } else {
                        $matchFound = $this->__CreateDefaultMatchResult($subrule);
                    }
                    break;
                case 'SUBJECT':
                    if ($this->subjectop) {
                        $matchFound = $this->find($subrule, $this->subjectop, $mailRecord->getSubject(), $this->subject);
                    } else {
                        $matchFound = $this->__CreateDefaultMatchResult($subrule);
                    }
                    break;
                case 'BODY':
                    if ($this->bodyop) {
                        $matchFound = $this->find($subrule, $this->bodyop, trim(strip_tags($mailRecord->getBody())), trim($this->body));
                    } else {
                        $matchFound = $this->__CreateDefaultMatchResult($subrule);
                    }
                    break;
            }
        }

        return $matchFound;
    }

    /**
     * Find if the rule matches based on condition and parameters
     */
    function find($subrule, $condition, $input, $searchfor) {
        if (!$input)
            return false;
            $input = trim(preg_replace("/\r/", '', decode_html($input))); 
            $searchfor = decode_html($searchfor);
        $matchFound = false;
        $matches = false;

        switch ($condition) {
            case 'Contains':
            $matchFound = stripos($input, $searchfor);
            $matchFound = ($matchFound !== FALSE);
            $matches = $searchfor;
            break;
            case 'Not Contains':
            $matchFound = stripos($input, $searchfor);
            $matchFound = ($matchFound === FALSE);
            $matches = $searchfor;
            break;
            case 'Equals':
            $matchFound = strcasecmp($input, $searchfor);
            $matchFound = ($matchFound === 0);
            $matches = $searchfor;
            break;
            case 'Not Equals':
            $matchFound = strcasecmp($input, $searchfor);
            $matchFound = ($matchFound !== 0);
            $matches = $searchfor;
            break;
            case 'Begins With':
            $matchFound = stripos($input, $searchfor);
            $matchFound = ($matchFound === 0);
            $matches = $searchfor;
            break;
            case 'Ends With':
            $matchFound = strripos($input, $searchfor);
            $matchFound = ($matchFound === strlen($input) - strlen($searchfor));
            $matches = $searchfor;
            break;
            case 'Regex':
            $regmatches = Array();
            $matchFound = false;
            $searchfor = str_replace('/', '\/', $searchfor);
                    $input = str_replace("_", " ", $input);
            if (preg_match("/$searchfor/i", $input, $regmatches)) {
                // Pick the last matching group
                $matches = $regmatches[php7_count($regmatches) - 1];
                $matchFound = true;
            }
            break;
            case 'Has Ticket Number':
            $regmatches = Array();
            $matchFound = false;
            $searchfor = "Ticket Id[^:]?: ([0-9]+)"; 
            $searchfor = str_replace('/', '\/', $searchfor);
            if (preg_match("/$searchfor/i", $input, $regmatches)) {
                // Pick the last matching group
                $matches = $regmatches[php7_count($regmatches) - 1];
                $matchFound = true;
            }
            break;
        }
        if($matchFound) $matchFound = $this->__CreateMatchResult($subrule, $condition, $searchfor, $matches);
        return $matchFound;
    }

    /**
     * Create matching result for the subrule.
     */
    function __CreateMatchResult($subrule, $condition, $searchfor, $matches) {
		return Array( 'subrule' => $subrule, 'condition' => $condition, 'searchfor' => $searchfor, 'matches' => $matches);
    }

    /**
     * Create default success matching result
     */
    function __CreateDefaultMatchResult($subrule) {
		if($this->matchusing == 'OR') return false;
		if($this->matchusing == 'AND') return $this->__CreateMatchResult($subrule, 'Contains', '', '');
    }

    /**
     * Detect if the rule match result has Regex condition
     * @param $matchresult result of apply obtained earlier
     * @returns matchinfo if Regex match is found, false otherwise
     */
    function hasRegexMatch($matchresult) {
		foreach($matchresult as $matchinfo) {
            $match_condition = $matchinfo['condition'];
            $match_string = $matchinfo['matches'];
                if(($match_condition == 'Regex' || $match_condition == 'Has Ticket Number') && $match_string) 
            return $matchinfo;
        }
        return false;
    }

    /**
     * Swap (reset) sequence of two rules.
     */
    static function resetSequence($ruleid1, $ruleid2) {
        global $adb;
            $ruleresult = $adb->pquery("SELECT ruleid, sequence FROM vtiger_mailscanner_rules WHERE ruleid = ? or ruleid = ?",
                Array($ruleid1, $ruleid2));
        $rule_partinfo = Array();
            if($adb->num_rows($ruleresult) != 2) {
            return false;
        } else {
            $rule_partinfo[$adb->query_result($ruleresult, 0, 'ruleid')] = $adb->query_result($ruleresult, 0, 'sequence');
            $rule_partinfo[$adb->query_result($ruleresult, 1, 'ruleid')] = $adb->query_result($ruleresult, 1, 'sequence');
            $adb->pquery("UPDATE vtiger_mailscanner_rules SET sequence = ? WHERE ruleid = ?", Array($rule_partinfo[$ruleid2], $ruleid1));
            $adb->pquery("UPDATE vtiger_mailscanner_rules SET sequence = ? WHERE ruleid = ?", Array($rule_partinfo[$ruleid1], $ruleid2));
        }
    }

    /**
     * Update rule information in database.
     */
    function update() {
        global $adb;
        if ($this->ruleid) {
            $adb->pquery("UPDATE vtiger_mailscanner_rules SET scannerid=?,fromaddress=?,toaddress=?,subjectop=?,subject=?,bodyop=?,body=?,matchusing=?,assigned_to=?,cc=?,bcc=?
                    WHERE ruleid=?", Array($this->scannerid, $this->fromaddress, $this->toaddress, $this->subjectop, $this->subject,
            $this->bodyop, $this->body, $this->matchusing, $this->assigned_to, $this->cc, $this->bcc, $this->ruleid));
        } else {
            $this->sequence = $this->__nextsequence();
            $adb->pquery("INSERT INTO vtiger_mailscanner_rules(scannerid,fromaddress,toaddress,subjectop,subject,bodyop,body,matchusing,sequence,assigned_to,cc,bcc)
                    VALUES(?,?,?,?,?,?,?,?,?,?,?,?)", Array($this->scannerid, $this->fromaddress, $this->toaddress, $this->subjectop, $this->subject,
            $this->bodyop, $this->body, $this->matchusing, $this->sequence, $this->assigned_to, $this->cc, $this->bcc));
            $this->ruleid = $adb->database->Insert_ID();
        }
    }

    /**
     * Get next sequence to use
     */
    function __nextsequence() {
        global $adb;
        $seqres = $adb->pquery("SELECT max(sequence) AS max_sequence FROM vtiger_mailscanner_rules", Array());
        $maxsequence = 0;
            if($adb->num_rows($seqres)) {
            $maxsequence = $adb->query_result($seqres, 0, 'max_sequence');
        }
        ++$maxsequence;
        return $maxsequence;
    }

    /**
     * Delete the rule and associated information.
     */
    function delete() {
        global $adb;

        // Delete dependencies
            if(!empty($this->actions)) {
                foreach($this->actions as $action) {
            $action->delete();
            }
        }
            if($this->ruleid) {
            $adb->pquery("DELETE FROM vtiger_mailscanner_ruleactions WHERE ruleid = ?", Array($this->ruleid));
            $adb->pquery("DELETE FROM vtiger_mailscanner_rules WHERE ruleid=?", Array($this->ruleid));
        }
    }

    /**
     * Update action linked to the rule.
     */
    function updateAction($actionid, $actiontext) {
        $action = $this->useaction;

            if($actionid != '' && $actiontext == '') {
                if($action) $action->delete();
        } else {
                if($actionid == '') {
            $action = new Settings_MailConverter_MailScannerAction_Handler($actionid);
            }
            $action->scannerid = $this->scannerid;
            $action->update($this->ruleid, $actiontext);
        }
    }

    /**
     * Take action on mail record
     */
    function takeAction($mailscanner, $mailRecord, $matchresult) {
		if(empty($this->actions)) return false;

        $action = $this->useaction; // Action is limited to One right now
        return $action->apply($mailscanner, $mailRecord, $this, $matchresult);
    }

}