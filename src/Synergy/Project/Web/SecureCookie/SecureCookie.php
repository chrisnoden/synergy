<?php
/**
 * Created by Chris Noden using PhpStorm.
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  Class
 * @package   synergy
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

namespace Synergy\Project\Web\SecureCookie;

use Synergy\Tools\Tools;

/**
 * Class SecureCookie
 *
 * @category Synergy\Project\Web\SecureCookie
 * @package  synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class SecureCookie
{

    /**
     * @var encryption token
     */
    private static $token;


    /**
     * Set an encryption token to improve the security
     *
     * @param string $token
     */
    public static function setToken($token)
    {
        self::$token = $token;
    }


    /**
     * Store an encrypted cookie
     *
     * @param string $cookieName
     * @param mixed  $cookieValue
     * @param int    $expiry default stores just for the browser session
     */
    public static function set($cookieName, $cookieValue, $expiry = 0)
    {
        if (isset($_COOKIE['synsec'])) {
            $synsec = $_COOKIE['synsec'];
        } else {
            $synsec = Tools::randomString('12');
        }

        if ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') &&
            (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https')
        ) {
            $ssl = false;
        } else {
            $ssl = true;
        }

        setcookie('synsec', $synsec, time() + 60 * 60 * 24 * 30, '/', $_SERVER['HTTP_HOST'], $ssl, true);

        $synsec .= 'synErgy' . self::$token;

        /* Open the cipher */
        $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');

        /* Create the IV and determine the keysize length, use MCRYPT_RAND
         * on Windows instead */
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
        $ks = mcrypt_enc_get_key_size($td);

        /* Create key */
        $key = substr(md5($synsec), 0, $ks);

        /* Intialize encryption */
        mcrypt_generic_init($td, $key, $iv);

        /* Encrypt data */
        $encrypted = mcrypt_generic($td, serialize($cookieValue));

        # Store our secure cookie
        setcookie(
            $cookieName,
            trim(base64_encode($iv . '|' . $encrypted)),
            $expiry,
            '/',
            $_SERVER['HTTP_HOST'],
            $ssl,
            true
        );

        /* Terminate encryption handler */
        mcrypt_generic_deinit($td);
    }


    /**
     * Get the value of the encrypted cookie
     *
     * @param $cookieName
     *
     * @return mixed|null
     */
    public static function get($cookieName)
    {
        if (isset($_COOKIE['synsec'])) {
            $synsec = $_COOKIE['synsec'] . 'synErgy' . self::$token;
        } else {
            return null;
        }

        if (isset($_COOKIE[$cookieName])) {
            # Decode our string
            list ($iv, $cipherText) = explode('|', base64_decode($_COOKIE[$cookieName]));
        } else {
            return null;
        }

        /* Open the cipher */
        $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
        $ks = mcrypt_enc_get_key_size($td);

        /* Create key */
        $key = substr(md5($synsec), 0, $ks);
        /* Initialize encryption module for decryption */
        mcrypt_generic_init($td, $key, $iv);

        /* Decrypt encrypted string */
        $decrypted = mdecrypt_generic($td, $cipherText);

        /* Terminate decryption handle and close module */
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        # Return the clearText
        return unserialize(trim($decrypted));
    }
}
