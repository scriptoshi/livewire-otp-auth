<?php

namespace Scriptoshi\LivewireOtpAuth\Services;

class OtpEncryption
{
    /**
     * The encryption method used.
     * 
     * @var string
     */
    private string $method = 'AES-128-ECB';

    /**
     * Encrypt data.
     *
     * @param string $data
     * @return string
     */
    public function encrypt(string $data): string
    {
        $encrypted = openssl_encrypt(
            $data,
            $this->method,
            config('app.key'),
            OPENSSL_RAW_DATA
        );

        // Convert to URL-safe base64
        return $this->base64UrlEncode($encrypted);
    }

    /**
     * Decrypt data.
     *
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        // Convert from URL-safe base64
        $decoded = $this->base64UrlDecode($data);
        
        return openssl_decrypt(
            $decoded,
            $this->method,
            config('app.key'),
            OPENSSL_RAW_DATA
        );
    }

    /**
     * Encode data to URL-safe base64.
     *
     * @param string $data
     * @return string
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decode URL-safe base64 data.
     *
     * @param string $data
     * @return string
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
