<?php

namespace Scriptoshi\LivewireOtpAuth\Traits;

use Illuminate\Support\Facades\Config;

trait HasOtpAuth
{
    /**
     * Generate a new OTP for the user.
     *
     * @return string
     */
    public function generateOTP()
    {
        $length = Config::get('livewire-otp-auth.otp_length', 6);
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;
        
        $otp = mt_rand($min, $max);
        $this->otp = $otp;
        $this->otp_expires_at = now()->addMinutes(Config::get('livewire-otp-auth.expiration_time', 10));
        $this->save();
        
        return $otp;
    }

    /**
     * Verify the given OTP.
     *
     * @param int $otp
     * @return bool
     */
    public function verifyOTP($otp)
    {
        if ($this->otp === $otp && $this->otp_expires_at > now()) {
            $this->otp = null;
            $this->otp_expires_at = null;
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Check if the user has a valid OTP.
     *
     * @return bool
     */
    public function hasValidOTP()
    {
        return $this->otp !== null && $this->otp_expires_at > now();
    }
}
