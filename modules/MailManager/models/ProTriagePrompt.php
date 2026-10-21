<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * The triage conversation with the model — system prompt (labels,
 * priority, topic tags, resolution, sender entities) and the email prompt
 */
class MailManager_ProTriagePrompt_Model
{
    private const BODY_CHARS = 4000;

    /** Subject tag a server-side spam filter (SpamAssassin and alike) puts before the subject */
    private const SERVER_SPAM_TAG = '/^\s*(\*{2,}\s*SPAM\s*\*{2,}|\[\s*SPAM\s*\])/i';

    /**
     * @return string
     */
    public static function buildSystemPrompt($withEntities = false)
    {
        // topic_tags normalize synonyms AND languages at write time — two
        // differently worded (or differently spoken) mails about the same
        // problem land on the same canonical English tags, so the
        // similar-cases search stays a cheap deterministic overlap
        $json = '{"label": "<label name or null>", "priority": "urgent"|"high"|"normal"|"low", '
            . '"topic_tags": ["<canonical lowercase English keywords of the PROBLEM — always English '
            . 'regardless of the mail language. ' . MailManager_ProAiTopics_Model::VOCAB_HINT . '>"], '
            // detection only — the Solution summary is generated with full
            // conversation context when the user accepts the close suggestion
            . '"language": "<ISO 639-1 code of the language the sender wrote this email in, e.g. en, de, sk>", '
            . '"case_resolved": true|false (true ONLY when this email confirms that a previously '
            . 'reported problem is now solved — "thanks, it works", "fixed", "problem gone"), '
            // the cascade repeats an unsure sorting with the strong model
            . '"confidence": <number 0..1, how sure you are of the label and the priority>';
        // the request radar groups what customers keep asking for
        if (MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_RADAR)) {
            $json .= ', "request_type": "<what the sender wants in 2-4 lowercase English words, e.g. price request, '
                . 'invoice copy, login problem, delivery date; null for newsletters and automatic mail>"';
        }
        $entityInstruction = '';
        if ($withEntities) {
            // Same call, same reading — the entity keys cost a handful of
            // output tokens and feed the deterministic CRM matching
            // A reply quotes the previous mail with OUR signature — the model
            // took our own company for the sender's and the CRM linked it
            $entityInstruction = 'Also extract the sender\'s identity and company details from the '
                . 'sender\'s OWN newest text and signature only (null for anything not stated there — never '
                . 'guess). Ignore quoted earlier messages (below lines like "On ... wrote:", "From:/Od:/Sent:", '
                . '"-----Original Message-----" or ">" quoting) and never report the recipient\'s company: ';
            $json .= ', "sender_company": "<company name or null>", "sender_firstname": "<or null>", '
                . '"sender_lastname": "<or null>", "sender_phone": "<or null>", "sender_position": "<or null>", '
                . '"sender_website": "<company website or null>", "sender_street": "<or null>", '
                . '"sender_city": "<or null>", "sender_zip": "<or null>", "sender_country": "<or null>"';
        }
        $json .= '}';

        return 'You are an email triage assistant inside a CRM. Classify the email into exactly one '
            . 'of the given labels (or none when nothing fits) and assess its priority. '
            // a description is the user's own rule — "PT + number" must not
            // match "TT002730" just because both look like a task code
            . 'A label description is a rule written by the user. A concrete condition it states — a sender '
            . 'address or domain, a subject prefix or code, an exact word — must hold LITERALLY (character by '
            . 'character for prefixes and codes; a similar-looking one does not count); when such a condition '
            . 'is not met, that label does not apply, however well the rest fits. '
            // the strictness is for the literal conditions only
            . 'A description that names a kind of mail instead ("newsletters", "price inquiries") applies by '
            . 'its meaning: every email of that kind gets the label. '
            // a broad description ("automated notifications") on a label that
            // throws mail away sent invoices and login alerts to Spam
            . 'A label marked "(discards the mail)" moves the email out of the inbox by itself, so it needs '
            . 'certainty: never give such a label to transactional or security mail — invoices and billing '
            . 'documents, receipts, order, payment or subscription confirmations, renewal reminders, login or '
            . 'security alerts, password or account changes — whatever its description says; such mail gets '
            . 'another fitting label or none. '
            // the server's own verdict is a signal, not a sentence: it flags
            // wanted mail too (the user's own task notifications)
            . 'When the email is noted as flagged by the mail server\'s spam filter, give it the label meant '
            . 'for spam or unsolicited mail, unless a literal condition of another label or an earlier decision '
            . 'of the user clearly matches it (spam filters flag wanted mail by mistake). '
            // the user's corrections generalise — the next ticket of the same
            // kind follows them although its subject differs
            . 'When earlier decisions of the user are listed with the email, they outrank the descriptions: '
            . 'mail of the same kind as a listed one (same sender or company, the same sort of subject — '
            . 'subjects are shown lowercased and without numbers) gets the same label, and "no label" means '
            . 'such mail gets none. '
            . 'Priority reflects urgency and sentiment: production outages or an angry customer are "urgent", '
            . 'time-sensitive business requests are "high", regular correspondence is "normal", '
            . 'newsletters and automated notifications are "low"; spam, phishing and cold outreach are always "low" '
            . 'however urgent they pretend to be. '
            . self::priorityRules()
            . $entityInstruction
            . 'Respond with a single JSON object only, no other text: ' . $json;
    }

    /**
     * @param string $subject
     * @return bool the mail server's spam filter tagged the subject
     *              ("*** SPAM ***", "*****SPAM*****", "[SPAM]")
     */
    public static function isServerSpam($subject)
    {
        return (bool)preg_match(self::SERVER_SPAM_TAG, $subject);
    }

    /**
     * One line per earlier decision — what the mail looked like and the
     * label the user gave it (or took away)
     *
     * @param array $examples rows of MailManager_ProLabelRule_Model::getExamples
     * @return string[]
     */
    private static function exampleLines(array $examples)
    {
        $lines = [];
        foreach ($examples as $example) {
            if ($example['rule_type'] === MailManager_ProLabelRule_Model::TYPE_DOMAIN) {
                $what = 'any mail from the domain @' . $example['domain'];
            } elseif ($example['rule_type'] === MailManager_ProLabelRule_Model::TYPE_SENDER) {
                $what = 'any mail from ' . $example['from_email'];
            } else {
                $what = 'from ' . $example['from_email'] . ', subject like "' . $example['subject_key'] . '"';
            }
            $lines[] = '- ' . $what . ' -> '
                . ((string)$example['label_name'] !== '' ? $example['label_name'] : 'no label');
        }

        return $lines;
    }

    /**
     * The admin's own priority rules (Settings, Features) — they refine the
     * built-in ones for this company
     *
     * @return string prompt sentence, '' when none are set
     */
    private static function priorityRules()
    {
        $rules = trim(MailManager_ProAiConfig_Model::get('ai_priority_rules'));

        return $rules !== ''
            ? 'The administrator\'s priority rules for this company take precedence over the general ones: '
                . mb_substr($rules, 0, 2000) . ' '
            : '';
    }

    /**
     * @param array $labels
     * @param array $message from_email / from_name / subject / body_plain
     * @param array $examples the user's earlier decisions (MailManager_ProLabelRule_Model::getExamples)
     * @return string
     */
    public static function buildEmailPrompt(array $labels, array $message, array $examples = [])
    {
        $lines = ['Labels:'];
        foreach ($labels as $label) {
            $description = trim((string)$label['ai_description']);
            // A label without an AI description is manual-only (personal
            // workflow states like "Later") — the AI never assigns those
            if ($description === '') {
                continue;
            }
            // the AI must know which labels throw mail away (Move to Spam / Trash)
            $discards = strpos((string)($label['suggested_module'] ?? ''), 'action:') === 0;
            $lines[] = '- ' . $label['name'] . ($discards ? ' (discards the mail)' : '') . ': ' . $description;
        }
        $exampleLines = self::exampleLines($examples);
        if ($exampleLines) {
            // the subjects and addresses in them come from mail — wrapped like the mail (I16)
            $lines[] = '';
            $lines[] = 'Earlier decisions of the user in this mailbox:';
            $lines[] = MailManager_ProAiGuard_Helper::wrap(MailManager_ProAiGuard_Helper::SOURCE_DATA, implode("\n", $exampleLines), 'earlier decisions');
        }
        $lines[] = '';
        if (self::isServerSpam((string)$message['subject'])) {
            $lines[] = 'Note: the mail server\'s spam filter flagged this email as spam.';
        }
        // the mail itself — third-party text, the visible part only (I16)
        $lines[] = 'Email:';
        $lines[] = MailManager_ProAiGuard_Helper::wrap(
            MailManager_ProAiGuard_Helper::SOURCE_MAIL,
            'From: ' . trim($message['from_name'] . ' <' . $message['from_email'] . '>')
            . "\nSubject: " . $message['subject'] . "\n\n"
            . mb_substr(
                MailManager_ProAiGuard_Helper::visibleMailText((string)$message['body_plain'], (string)($message['body_html'] ?? '')),
                0,
                self::BODY_CHARS
            )
        );

        return implode("\n", $lines);
    }
}
