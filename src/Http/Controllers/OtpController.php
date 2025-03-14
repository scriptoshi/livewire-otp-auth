<?php

namespace Scriptoshi\LivewireOtpAuth\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Scriptoshi\LivewireOtpAuth\Services\OtpEncryption;

class OtpController extends Controller
{
    /**
     * The OTP encryption service.
     *
     * @var OtpEncryption
     */
    protected OtpEncryption $encryption;
    
    /**
     * Create a new controller instance.
     *
     * @param OtpEncryption $encryption
     */
    public function __construct(OtpEncryption $encryption)
    {
        $this->encryption = $encryption;
    }
    /**
     * Verify an OTP from a code in URL.
     *
     * @param Request $request
     * @param string $code
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, $code)
    {
        $otp = $this->encryption->decrypt($code);
        
        $user = $request->user() ?? User::query()->find($request->session()->get('otp-user-id'));
        
        if (!$user || !$user->verifyOTP((int)$otp)) {
            $url = $request->session()->pull('url', '/');
            return redirect()->to($url)->with('error', 'The provided OTP is invalid or has expired.');
        }
        
        // If not logged in, log in the user
        if (!Auth::check()) {
            Auth::login($user);
        }
        
        // Mark email as verified if not already
        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }
        
        // Clear session
        $request->session()->remove('otp-user-id');
        $url = $request->session()->pull('url', '/');
        
        return redirect()->to($url)->with('success', 'Email verified successfully.');
    }
    

}
