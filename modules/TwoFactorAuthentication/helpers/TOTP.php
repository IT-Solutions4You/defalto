<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

use Mpdf\QrCode\Output\Svg;
use Mpdf\QrCode\QrCode;

/**
 * Time-based One-Time Password (RFC 6238) helper: secret generation, code
 * verification, the otpauth provisioning URI and an inline SVG QR code.
 *
 * The QR code is produced locally by the mpdf/qrcode package already required
 * by Defalto, so the secret never leaves the server.
 */
class TwoFactorAuthentication_TOTP_Helper
{
    /** Code length. */
    public const DIGITS = 6;
    /** Time step in seconds. */
    public const PERIOD = 30;
    /** RFC 4648 base32 alphabet. */
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * @param string $secret base32 string
     * @return string raw binary
     */
    private static function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $secret));

        if ($secret === '') {
            return '';
        }

        $bits = '';

        foreach (str_split($secret) as $char) {
            $bits .= str_pad(decbin(strpos(self::BASE32_ALPHABET, $char)), 5, '0', STR_PAD_LEFT);
        }

        $output = '';

        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $output .= chr(bindec($byte));
            }
        }

        return $output;
    }

    /**
     * @param string $data raw binary
     * @return string base32-encoded (no padding)
     */
    private static function base32Encode(string $data): string
    {
        if ($data === '') {
            return '';
        }

        $bits = '';

        foreach (str_split($data) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $output = '';

        foreach (str_split($bits, 5) as $chunk) {
            $output .= self::BASE32_ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $output;
    }

    /**
     * @param int $bytes number of random bytes (20 = 160-bit secret)
     * @return string base32-encoded secret
     */
    public static function generateSecret(int $bytes = 20): string
    {
        return self::base32Encode(random_bytes($bytes));
    }

    /**
     * @param string $secret base32 secret
     * @param int $timeSlice unix time divided by the period
     * @return string the code for the given time slice
     */
    public static function getCode(string $secret, int $timeSlice): string
    {
        $key = self::base32Decode($secret);
        $binTime = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $binTime, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;

        $truncated = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        return str_pad((string)($truncated % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Builds the otpauth:// URI scanned by authenticator apps.
     *
     * @param string $secret base32 secret
     * @param string $account user identifier shown in the app (e.g. user name)
     * @param string $issuer service name shown in the app (e.g. company name)
     * @return string
     */
    public static function getProvisioningUri(string $secret, string $account, string $issuer): string
    {
        $label = rawurlencode($issuer) . ':' . rawurlencode($account);
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ]);

        return 'otpauth://totp/' . $label . '?' . $params;
    }

    /**
     * Like verify(), but returns the matched time slice (unix time / period) so
     * the caller can reject a code that was already used (replay within the
     * validity window). Returns PHP_INT_MIN when no slice matches.
     *
     * @param string $secret base32 secret
     * @param string $code submitted code
     * @param int $window number of steps to check on each side
     * @return int matched time slice, or PHP_INT_MIN on no match
     */
    public static function matchSlice(string $secret, string $code, int $window = 1): int
    {
        $code = preg_replace('/\s+/', '', $code);

        if ($code === '' || !ctype_digit($code)) {
            return PHP_INT_MIN;
        }

        $code = str_pad($code, self::DIGITS, '0', STR_PAD_LEFT);
        $timeSlice = (int)floor(time() / self::PERIOD);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::getCode($secret, $timeSlice + $i), $code)) {
                return $timeSlice + $i;
            }
        }

        return PHP_INT_MIN;
    }

    /**
     * Renders the given text as an inline SVG QR code.
     *
     * @param string $text
     * @param int $size output width and height in pixels
     * @return string SVG markup
     */
    public static function renderQrSvg(string $text, int $size = 220): string
    {
        $qrCode = new QrCode($text, QrCode::ERROR_CORRECTION_MEDIUM);
        $output = new Svg();
        $svg = $output->output($qrCode, $size, 'white', 'black');

        return (string)preg_replace('/^<\?xml[^>]+>\s*/', '', $svg);
    }

    /**
     * Verifies a submitted code against the secret within a small time window
     * (tolerates +/- one step of clock drift).
     *
     * @param string $secret base32 secret
     * @param string $code submitted code
     * @param int $window number of steps to check on each side
     * @return bool
     */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        return self::matchSlice($secret, $code, $window) !== PHP_INT_MIN;
    }
}
