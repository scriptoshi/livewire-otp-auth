<?php

namespace Scriptoshi\LivewireOtpAuth\Components;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Scriptoshi\LivewireOtpAuth\Notifications\OTPNotification;
use Scriptoshi\LivewireOtpAuth\Services\OtpEncryption;

class OtpAuthentication extends Component
{
    // Form inputs
    public ?string $email = null;
    public ?string $name = null;
    public ?string $otp = null;

    // Component states
    public string $step = 'request'; // 'request' or 'verify'
    public ?string $type = 'login'; // 'login', 'register', or 'validate'
    public ?string $error = null;
    public ?string $success = null;
    public bool $loading = false;
    public bool $resendLoading = false;
    public ?string $to = null;

    // Countdown for resend
    public int $countdown = 0;

    // OTP input handling
    public array $otpInputs = [];



    /**
     * Initialize the component.
     */
    public function mount(string $type = 'login', string $step = 'request', ?string $to = null): void
    {
        $this->type = $type;
        $this->step = $step;
        $this->to = $to ?? request()->url();
        $this->otpInputs = array_fill(0, 6, '');

        // If user already logged in and type is validate, set user email
        if ($this->type === 'validate' && auth()->check()) {
            $this->email = auth()->user()->email;
        }

        // If there is a stored user ID in session and step is verify, move to verify step
        if (Session::has('otp-user-id') && $this->step === 'request') {
            $this->step = 'verify';
        }
    }

    /**
     * Process sending OTP.
     */
    public function sendOtp(): void
    {
        $this->error = null;
        $this->success = null;
        $this->loading = true;

        try {
            $this->validate([
                'email' => 'required_if:step,request|email',
                'name' => 'nullable|required_if:type,register',
            ]);

            // Different validations based on type
            if ($this->type === 'validate') {
                $user = auth()->user();
                if (!$user) {
                    throw ValidationException::withMessages(['email' => 'Please login first']);
                }
            } elseif ($this->type === 'login') {
                $user = User::where('email', $this->email)->first();
                if (!$user) {
                    throw ValidationException::withMessages(['email' => 'User not found. Please register']);
                }
            } elseif ($this->type === 'register') {
                $user = User::firstOrCreate(
                    ['email' => $this->email],
                    [
                        'name' => $this->name,
                        'email_verified_at' => null
                    ]
                );
            }

            // Generate and send OTP
            $otp = $user->generateOTP();
            $user->notify(new OTPNotification(
                $otp,
                'Connect Your Email',
                route('otp.verify', ['code' => app(OtpEncryption::class)->encrypt($otp)])
            ));

            // Store data in session
            Session::put('url', $this->to);
            Session::put('otp-user-id', $user->id);

            // Update component state
            $this->step = 'verify';
            $this->success = 'OTP has been sent to your email.';
            $this->startCountdown();
        } catch (ValidationException $e) {
            $this->error = collect($e->errors())->flatten()->first();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Start countdown for resend.
     */
    public function startCountdown(): void
    {
        $this->countdown = Config::get('livewire-otp-auth.resend_cooldown', 60);
        $this->dispatch('startCountdown');
    }

    /**
     * Verify OTP entered by user.
     */
    public function verifyOtp(): void
    {
        $this->error = null;
        $this->success = null;
        $this->loading = true;

        // Compile OTP from inputs if not directly set
        if (empty($this->otp) && !empty($this->otpInputs)) {
            $this->otp = implode('', $this->otpInputs);
        }

        try {
            $this->validate([
                'otp' => 'required|digits:6'
            ]);

            $user = auth()->user() ?? User::find(Session::get('otp-user-id'));

            if (!$user) {
                throw new \Exception('OTP session expired. Please try again.');
            }

            if (!$user->verifyOTP((int)$this->otp)) {
                throw ValidationException::withMessages(['otp' => 'Invalid OTP code']);
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
            Session::remove('otp-user-id');

            // Redirect
            $url = Session::pull('url', '/');
            $this->redirect($url);
        } catch (ValidationException $e) {
            $this->error = collect($e->errors())->flatten()->first();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Resend OTP with rate limiting.
     */
    public function resendOtp(): void
    {
        $this->error = null;
        $this->success = null;
        $this->resendLoading = true;

        try {
            // Apply rate limiting
            $key = 'otp_resend_' . (request()->ip() ?? '0');
            $maxAttempts = Config::get('livewire-otp-auth.rate_limit_attempts', 5);
            $decayMinutes = Config::get('livewire-otp-auth.rate_limit_duration', 5);

            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($key);
                throw new \Exception("Please wait {$seconds} seconds before requesting another OTP.");
            }
            RateLimiter::hit($key, $decayMinutes); // Lock for configured minutes

            // Get user
            $user = auth()->user() ?? User::find(Session::get('otp-user-id'));

            if (!$user) {
                throw new \Exception('OTP session expired. Please try again.');
            }

            // Generate and send new OTP
            $otp = $user->generateOTP();
            $user->notify(new OTPNotification(
                $otp,
                'Login to Your Account',
                route('otp.verify', ['code' => app(OtpEncryption::class)->encrypt($otp)])
            ));

            $this->success = 'OTP has been resent to your email.';
            $this->startCountdown();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->resendLoading = false;
        }
    }

    /**
     * Handle OTP input changes.
     */
    public function updatedOtpInputs($value, $key): void
    {
        // Move focus to next input if a digit is entered
        if ($value && is_numeric($key) && $key < 5) {
            $this->dispatch('focusInput', ['index' => $key + 1]);
        }

        // Compile OTP from inputs
        $this->otp = implode('', $this->otpInputs);
    }

    /**
     * Handle paste event for OTP input.
     */
    public function handleOtpPaste($pastedText): void
    {
        $pastedText = preg_replace('/[^0-9]/', '', $pastedText);
        $chars = str_split(substr($pastedText, 0, 6));

        for ($i = 0; $i < 6; $i++) {
            $this->otpInputs[$i] = $chars[$i] ?? '';
        }

        $this->otp = implode('', $this->otpInputs);
    }

    /**
     * Reset form to initial state.
     */
    public function resetForm(): void
    {
        $this->step = 'request';
        $this->email = null;
        $this->name = null;
        $this->otp = null;
        $this->error = null;
        $this->success = null;
        $this->otpInputs = array_fill(0, 6, '');
        $this->countdown = 0;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire-otp-auth::otp-authentication');
    }
}
