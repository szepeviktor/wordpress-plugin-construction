<?php

namespace Otp;

/**
 * Interface for HOTP and TOTP
 *
 * HMAC-Based One-time Password(HOTP) algorithm specified in RFC 4226
 * @link https://tools.ietf.org/html/rfc4226
 *
 * Time-based One-time Password (TOTP) algorithm specified in RFC 6238
 * @link https://tools.ietf.org/html/rfc6238
 *
 * @author Christian Riesen <chris.riesen@gmail.com>
 * @link http://christianriesen.com
 * @license MIT License see LICENSE file
 */

interface OtpInterface
{
    /**
     * Returns OTP using the HOTP algorithm (counter based)
     *
     * @param string  $secret  Base32 Secret String
     * @param integer $counter Counter
     *
     * @return string One Time Password
     */
    function hotp($secret, $counter);

    /**
     * Returns OTP using the TOTP algorithm (time based)
     *
     * @param string  $secret      Base32 Secret String
     * @param integer $timecounter Optional: Uses current time if null
     *
     * @return string One Time Password
     */
    function totp($secret, $timecounter = null);

    /**
     * Checks Hotp against a key
     *
     * This is a helper function, but is here to ensure the Totp can be checked
     * in the same manner.
     *
     * @param string  $secret  Base32 Secret String
     * @param integer $counter Counter
     * @param string  $key     User supplied key
     *
     * @return boolean True if key is correct
     */
    function checkHotp($secret, $counter, $key);

    /**
     * Checks Hotp against a key for a provided counter window
     *
     * @param string  $secret        Base32 Secret String
     * @param integer $counter       Counter
     * @param string  $key           User supplied key
     * @param integer $counterwindow (optional) Size of the look-ahead window. Default value is 2
     *
     * @return int|boolean the counter if key is correct else false
     */
    function checkHotpResync($secret, $counter, $key, $counterwindow = 2);

    /**
     * Checks Totp agains a key
     *
     * @param string  $secret    Base32 Secret String
     * @param integer $key       User supplied key
     * @param integer $timedrift How large a drift to use beyond exact match
     *
     * @return boolean True if key is correct within time drift
     */
    function checkTotp($secret, $key, $timedrift = 1);
}
