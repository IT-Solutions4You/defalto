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
 * How the messages of an open conversation show (ITS4YouMailManager 4.71): the list row
 * stands for the customer's latest message, so that one opens; mail of ours newer than
 * it shows above it as one-line bars, older messages stay collapsed.
 */
class MailManager_ProConversation_Helper
{
    public const VIEW_OPEN = 'open';
    public const VIEW_BAR = 'bar';
    public const VIEW_COLLAPSED = 'collapsed';

    /** Characters of a bar's text */
    private const SNIPPET_CHARS = 160;

    /**
     * Sets how each message shows. With the customer's message the newest, or no customer
     * message at all, the newest one opens.
     *
     * @param array $messages timeline rows, newest first
     *
     * @return array the rows with 'view_state' and 'snippet_display'
     */
    public static function prepareMessages(array $messages): array
    {
        $messages = array_values($messages);
        $focus = self::getFocusIndex($messages);

        foreach ($messages as $index => $message) {
            if ($index === $focus) {
                $state = self::VIEW_OPEN;
            } elseif ($index < $focus) {
                $state = self::VIEW_BAR;
            } else {
                $state = self::VIEW_COLLAPSED;
            }

            $messages[$index]['view_state'] = $state;
            $messages[$index]['snippet_display'] = self::getSnippet($message);
        }

        return $messages;
    }

    /**
     * @param array $messages newest first
     *
     * @return int index of the customer's latest message when mail of ours is newer, 0 otherwise
     */
    private static function getFocusIndex(array $messages): int
    {
        if (!$messages || 'out' !== ($messages[0]['direction'] ?? '')) {
            return 0;
        }

        foreach ($messages as $index => $message) {
            if (self::isCustomerMessage($message)) {
                return $index;
            }
        }

        return 0;
    }

    /**
     * A received mail – not a ticket comment, not a draft.
     *
     * @param array $message
     *
     * @return bool
     */
    private static function isCustomerMessage(array $message): bool
    {
        return 'in' === ($message['direction'] ?? '') && empty($message['is_note']) && empty($message['is_draft']);
    }

    /**
     * @param array $message
     *
     * @return string one line of the message's text (the snippet is stored HTML encoded,
     *                ticket comments have none and use their plain body)
     */
    private static function getSnippet(array $message): string
    {
        $text = decode_html((string)($message['snippet'] ?? ''));

        if ('' === $text) {
            $text = (string)($message['body_plain'] ?? '');
        }

        return mb_substr(trim((string)preg_replace('/\s+/u', ' ', $text)), 0, self::SNIPPET_CHARS);
    }
}
