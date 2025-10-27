<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class CryptoHelper
{
    public static function encryptData($data)
    {
        $key = base64_decode(env('ENCRYPTION_KEY'));
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = random_bytes($ivLength); // new IV per encryption

        $jsonData = json_encode($data);
        $encrypted = openssl_encrypt($jsonData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        // prefix IV before ciphertext, base64 the entire result
        return base64_encode($iv . $encrypted);
    }

    public static function decryptData($payload)
    {
        $key = base64_decode(env('ENCRYPTION_KEY'));
        $data = base64_decode($payload);

        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return json_decode($decrypted, true);
    }
}
