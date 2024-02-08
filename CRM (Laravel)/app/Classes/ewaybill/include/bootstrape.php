<?php

if (!function_exists('generateRandomString')) {
    /**
     * Generate random string.
     * @param int $length
     * @return string
     */
    function generateRandomString($length = 32)
    {
        // Alphabets (Capitals & Smalls), numeric values and special characters should be there
        $capital = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 1);
        $small = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 1);
//        $number = substr(str_shuffle('0123456789'), 0, 1);
//        $special = substr(str_shuffle("!#$%&*-@_"), 0, 1);
//        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!#$%&*-@_';
        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $string = substr(str_shuffle(str_repeat($pool, 5)), 0, $length - 2) . $capital . $small;
        return $string;
    }
}

if (!function_exists('generateRsaEncryption')) {
    function generateRsaEncryption($string, $publicKeyPath)
    {
        $fp = fopen($publicKeyPath, 'r');
        $pub_key = fread($fp, 8192);
        fclose($fp);
        openssl_public_encrypt($string, $crypttext, $pub_key);
        return base64_encode($crypttext);
    }
}

if (!function_exists('generateAesEncryption')) {
    function generateAesEncryption($string, $keyString, $isDecrypt = 0)
    {

       
        /**
         * CBC has an IV and thus needs randomness every time a message is encrypted
         * openssl_get_cipher_methods($method)
         * // Most secure key
         * $key = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
         *
         */
        $method = 'aes-256-ecb';
        $ivlen = openssl_cipher_iv_length($method);
        // Most secure iv Never ever use iv=0 in real live. Better use this:
        $iv = openssl_random_pseudo_bytes($ivlen);
        if ($isDecrypt) {
            $string = openssl_decrypt(base64_decode($string), $method, $keyString, OPENSSL_RAW_DATA, $iv);
        } else {
            $string = base64_encode(openssl_encrypt($string, $method, $keyString, OPENSSL_RAW_DATA, $iv));
        }
        return $string;
    }
}

if (!function_exists('connect')) {
    function connect($url, $method = 'get', array $payload, $headers = [], $isDebug = 0)
    {
        $timeout = 30;
        $redirect = 3;
        $verboseLog = '';
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'status' => 0,
                'response' => null,
                'verbose' => $verboseLog
            ];
        }
        $curl = curl_init();
        $userAgent = 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.43 Safari/537.31';
        $options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_MAXREDIRS => $redirect,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ];

        if (strtolower($method) == 'post') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = json_encode($payload);
        }

        if (!is_null($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        if ($isDebug) {
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            $verbose = fopen('php://temp', 'w+');
            $options[CURLOPT_VERBOSE] = true;
            $options[CURLOPT_STDERR] = $verbose;
        }
        curl_setopt_array($curl, $options);
        curl_setopt($curl, CURLOPT_URL, $url);
        $server_output = curl_exec($curl);

        if ($isDebug) {
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
        }
        curl_close($curl);

        if (!$server_output) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        }
        $returnResponse = [
            'status' => 1,
            'response' => $server_output,
            'verbose' => $verboseLog
        ];

        return $returnResponse;

    }
}

if (!function_exists('is_json')) {
    /**
     * Check weather given sting is json
     *
     * @param $string
     * @return bool
     */
    function is_json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}