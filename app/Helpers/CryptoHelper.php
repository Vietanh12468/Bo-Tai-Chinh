<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class CryptoHelper
{
    public static function encryptData($data)
    {
        $key = base64_decode(config('app.encryption_key'));
        $ivLength = openssl_cipher_iv_length(config('app.encryption_algorithm'));
        $iv = random_bytes($ivLength); // new IV per encryption

        $jsonData = json_encode($data);
        $encrypted = openssl_encrypt($jsonData, config('app.encryption_algorithm'), $key, OPENSSL_RAW_DATA, $iv);

        // prefix IV before ciphertext, base64 the entire result
        return base64_encode($iv . $encrypted);
    }

    public static function decryptData($payload)
    {
        $key = base64_decode(config('app.encryption_key'));
        $data = base64_decode($payload);

        $ivLength = openssl_cipher_iv_length(config('app.encryption_algorithm'));
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        $decrypted = openssl_decrypt($encrypted, config('app.encryption_algorithm'), $key, OPENSSL_RAW_DATA, $iv);
        return json_decode($decrypted, true);
    }
}
