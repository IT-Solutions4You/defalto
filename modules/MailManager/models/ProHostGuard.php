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
 * Where the CRM server may be sent by a mail server address somebody typed.
 * Every user sets up an own mailbox and tests the connection — without a
 * limit that is a port scanner of the internal network run from the CRM
 * server ("connect failed" / "no greeting" tell the ports apart). The admin
 * is trusted with any address (an internal mail server is a normal setup)
 */
class MailManager_ProHostGuard_Model
{
    /** Ports mail servers listen on — what a non-admin may test */
    private const MAIL_PORTS = [25, 110, 143, 465, 587, 993, 995, 2525];

    /**
     * @param string $host
     * @param int $port
     * @return bool the current user may make the CRM server connect there
     */
    public static function isAllowed($host, $port): bool
    {
        $port = (int)$port;
        if ($port < 1 || $port > 65535) {
            return false;
        }
        if (Users_Record_Model::getCurrentUserModel()->isAdminUser()) {
            return true;
        }
        if (!in_array($port, self::MAIL_PORTS, true)) {
            return false;
        }

        $host = trim((string)$host, " \t[]");
        $addresses = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);
        if (!$addresses) {
            // a host that does not resolve (or resolves to IPv6 only) cannot be checked – refuse it
            return false;
        }
        foreach ($addresses as $address) {
            // loopback, private, link-local and reserved ranges are the internal network
            if (!filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }
}
