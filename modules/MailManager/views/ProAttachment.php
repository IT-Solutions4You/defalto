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
 * Attachment streaming endpoint – fetches one MIME part from the mail server on demand
 * and streams it to the browser (nothing is stored on disk). disposition=inline serves
 * images (cid: images of HTML bodies, the image preview), disposition=preview a PDF for
 * the PDF viewer of the attachment preview.
 */
class MailManager_ProAttachment_View extends MailManager_ProAbstract_View
{
    /**
     * The endpoint streams a file; no page header.
     *
     * @param Vtiger_Request $request
     * @param bool           $display
     *
     * @return void
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
    }

    /**
     * The endpoint streams a file; no page footer.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    /**
     * Streams one attachment of a mailbox the user may access.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        MailManager_ProInbox_Model::useUtf8mb4();

        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();
        $attachment = MailManager_ProAttachment_Model::getSource((int)$request->get('attachment'));

        if (!$attachment) {
            $this->fail(404, vtranslate('LBL_ATTACHMENT_NOT_FOUND', 'MailManager'));
        }

        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$attachment['mailboxid'], $userId);

        if (!$mailbox) {
            $this->fail(403, vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        $result = MailManager_ProAttachment_Model::retrieveContent($attachment, $mailbox);

        if (!isset($result['content'])) {
            $this->fail($result['code'], $result['message']);
        }

        $this->send($request, $attachment, $result['content']);
    }

    /**
     * Sends the content with the safe headers of MailManager_ProAttachment_Model.
     *
     * @param Vtiger_Request $request
     * @param array          $attachment attachment row
     * @param string         $content
     *
     * @return void
     */
    protected function send(Vtiger_Request $request, array $attachment, string $content): void
    {
        $headers = MailManager_ProAttachment_Model::getResponseHeaders(
            $attachment,
            (string)$request->get('disposition'),
            strlen($content)
        );

        foreach ($headers as $header) {
            header($header);
        }

        echo $content;
        exit;
    }

    /**
     * Ends the request with an HTTP error status and a plain message.
     *
     * @param int    $code    HTTP status
     * @param string $message translated message
     *
     * @return never
     */
    protected function fail(int $code, string $message): never
    {
        http_response_code($code);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        exit;
    }
}
