<?php

namespace Otp;

/**
 * Google Authenticator
 *
 * Can be easy used with Google Authenticator
 * @link https://code.google.com/p/google-authenticator/
 *
 * @author Christian Riesen <chris.riesen@gmail.com>
 * @link http://christianriesen.com
 * @license MIT License see LICENSE file
 */

class GoogleAuthenticator
{
    protected static $allowedTypes = array('hotp', 'totp');

    protected static $height = 200;
    protected static $width = 200;

    /**
     * Returns the Key URI
     *
     * Format of encoded url is here:
     * https://code.google.com/p/google-authenticator/wiki/KeyUriFormat
     * Should be done in a better fashion
     *
     * @param string $type totp or hotp
     * @param string $label Label to display this as to the user
     * @param string $secret Base32 encoded secret
     * @param integer $counter Required by hotp, otherwise ignored
     * @param array $options Optional fields that will be set if present
     *
     * @return string Key URI
     */
    public static function getKeyUri($type, $label, $secret, $counter = null, $options = array())
    {
        // two types only..
        if (!in_array($type, self::$allowedTypes)) {
            throw new \InvalidArgumentException('Type has to be of allowed types list');
        }

        // Label can't be empty
        $label = trim($label);

        if (strlen($label) < 1) {
            throw new \InvalidArgumentException('Label has to be one or more printable characters');
        }

        if (substr_count($label, ':') > 2) {
        	throw new \InvalidArgumentException('Account name contains illegal colon characters');
        }

        // Secret needs to be here
        if (strlen($secret) < 1) {
            throw new \InvalidArgumentException('No secret present');
        }

        // check for counter on hotp
        if ($type == 'hotp' && is_null($counter)) {
            throw new \InvalidArgumentException('Counter required for hotp');
        }

        // This is the base, these are at least required
        $otpauth = 'otpauth://' . $type . '/' . str_replace(array(':', ' '), array('%3A', '%20'), $label) . '?secret=' . rawurlencode($secret);

        if ($type == 'hotp' && !is_null($counter)) {
            $otpauth .= '&counter=' . intval($counter);
        }

        // Now check the options array

        // algorithm (currently ignored by Authenticator)
        // Defaults to SHA1
        if (array_key_exists('algorithm', $options)) {
            $otpauth .= '&algorithm=' . rawurlencode($options['algorithm']);
        }

        // digits (currently ignored by Authenticator)
        // Defaults to 6
        if (array_key_exists('digits', $options) && intval($options['digits']) !== 6 && intval($options['digits']) !== 8) {
        	throw new \InvalidArgumentException('Digits can only have the values 6 or 8, ' . $options['digits'] . ' given');
        } elseif (array_key_exists('digits', $options)) {
            $otpauth .= '&digits=' . intval($options['digits']);
        }

        // period, only for totp (currently ignored by Authenticator)
        // Defaults to 30
        if ($type == 'totp' && array_key_exists('period', $options)) {
            $otpauth .= '&period=' . rawurlencode($options['period']);
        }

        // issuer
        // Defaults to none
        if (array_key_exists('issuer', $options)) {
            $otpauth .= '&issuer=' . rawurlencode($options['issuer']);
        }

        // image (to accepts images for freeotp)
        if (array_key_exists('image', $options)) {
            $otpauth .= '&image=' . $options['image'];
        }

        return $otpauth;
    }

    /**
     * Returns the QR code url
     *
     * Format of encoded url is here:
     * https://code.google.com/p/google-authenticator/wiki/KeyUriFormat
     * Should be done in a better fashion
     *
     * @param string $type totp or hotp
     * @param string $label Label to display this as to the user
     * @param string $secret Base32 encoded secret
     * @param integer $counter Required by hotp, otherwise ignored
     * @param array $options Optional fields that will be set if present
     *
     * @return string URL to the QR code
     */
    public static function getQrCodeUrl($type, $label, $secret, $counter = null, $options = array())
    {
        // Width and height can be overwritten
        $width = self::$width;

        if (array_key_exists('width', $options) && is_numeric($options['width'])) {
            $width = $options['width'];
        }

        $height = self::$height;

        if (array_key_exists('height', $options) && is_numeric($options['height'])) {
            $height = $options['height'];
        }

        $otpauth = self::getKeyUri($type, $label, $secret, $counter, $options);

        $url = 'https://chart.googleapis.com/chart?chs=' . $width . 'x'
             . $height . '&cht=qr&chld=M|0&chl=' . urlencode($otpauth);

        return $url;
    }

    /**
     * Creates a pseudo random Base32 string
     *
     * This could decode into anything. It's located here as a small helper
     * where code that might need base32 usually also needs something like this.
     *
     * @param integer $length Exact length of output string
     *
     * @return string Base32 encoded random
     */
    public static function generateRandom($length = 16)
    {
        $keys = array_merge(range('A','Z'), range(2,7)); // No padding char

        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $keys[self::getRand()];
        }

        return $string;
    }

    /**
     * Create recovery codes
     *
     * A pure helper function to make your life easier. Generates a list of codes, guaranteed to be unique in the set
     *
     * @param integer $count How many codes to return
     * @param integer $length How long each code should be
     *
     * @return array Array of codes
     */
    public static function generateRecoveryCodes($count = 1, $length = 9)
    {
        $count = intval($count);
        $length = intval($length);
        $codes = [];

        do {
            // Generate codes
            $code = '';
            for ($i = 1; $i <= $length; $i++) {
                $code .= self::getRand(9);
            }

            // To make sure no duplicates get in
            if (!in_array($code, $codes)) {
                $codes[] = $code;
            }
        } while (count($codes) < $count);

        return $codes;
    }

    /**
     * Get random number
     *
     * @return integer Random number between 0 and 31 (including)
     */
    private static function getRand($max = 31)
    {
        if (function_exists('random_int')) {
            // Uses either the PHP7 internal function or the polyfill if present
            return random_int(0, $max);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            // For those not wanting either PHP7 or the polyfill, this works well enough
            $bytes = openssl_random_pseudo_bytes(2);
            $number = hexdec(bin2hex($bytes));

            if ($number > $max) {
                $number = $number % ($max + 1);
            }

            return $number;
        } else {
            // And last case, this does the trick too
            return mt_rand(0, $max);
        }
    }
}
