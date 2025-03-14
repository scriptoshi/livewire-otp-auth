
<div>
    <!-- OTP Form -->
    <div class="space-y-6">
        <!-- Error/Success Messages -->
        @if($error)
            <div class="text-red-500 text-sm font-medium">{{ $error }}</div>
        @endif
        
        @if($success)
            <div class="text-emerald-500 text-sm font-medium">{{ $success }}</div>
        @endif
        
        <!-- Request OTP Step -->
        @if($step === 'request')
            <form wire:submit="sendOtp" class="space-y-4">
                @if($type === 'register')
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Your Name</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <input wire:model="name" type="text" id="name" required autofocus
                                   class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                   placeholder="Enter your full name">
                        </div>
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </div>
                        <input wire:model="email" type="email" id="email" required autofocus
                               class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                               placeholder="Enter your email">
                    </div>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="flex justify-end">
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendOtp">Continue</span>
                        <span wire:loading wire:target="sendOtp">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </form>
            
        <!-- Verify OTP Step -->
        @else
            <form wire:submit="verifyOtp" class="space-y-6">
                <!-- OTP Input -->
                <div 
                    x-data="{
                        focusInput(index) {
                            $nextTick(() => {
                                document.getElementById('otp-input-' + index)?.focus();
                            });
                        },
                        handleKeyDown(e, index) {
                            if (e.key === 'Backspace' && !$wire.otpInputs[index] && index > 0) {
                                this.focusInput(index - 1);
                            }
                        },
                        handlePaste(e) {
                            e.preventDefault();
                            const pastedText = e.clipboardData.getData('text');
                            $wire.handleOtpPaste(pastedText);
                            // Focus last filled input or first empty
                            for (let i = 0; i < 6; i++) {
                                if (!$wire.otpInputs[i]) {
                                    this.focusInput(i);
                                    break;
                                }
                                if (i === 5) {
                                    this.focusInput(5);
                                }
                            }
                        }
                    }"
                    @focusinput.window="focusInput($event.detail.index)"
                    class="flex justify-between mb-6 gap-2"
                >
                    @for($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            id="otp-input-{{ $i }}"
                            class="w-10 h-12 text-center text-xl font-bold border rounded-md focus:outline-none focus:ring focus:border-blue-300 dark:bg-gray-900 dark:text-white {{ $error && str_contains(strtolower($error), 'otp') ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-700' }}"
                            wire:model.live="otpInputs.{{ $i }}"
                            @keydown="handleKeyDown($event, {{ $i }})"
                            @paste="handlePaste"
                            {{ $i === 0 ? 'autofocus' : '' }}
                        >
                    @endfor
                </div>
                
                <div class="flex items-center justify-between">
                    <button
                        type="button"
                        wire:click="resetForm"
                        class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition"
                    >
                        ← Back
                    </button>
                    
                    <button
                        type="button"
                        wire:click="resendOtp"
                        wire:loading.attr="disabled"
                        wire:target="resendOtp"
                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        x-data="{ 
                            countdown: @entangle('countdown'),
                            init() {
                                if (this.countdown > 0) {
                                    this.startTimer();
                                }
                            },
                            startTimer() {
                                const interval = setInterval(() => {
                                    if (this.countdown > 0) {
                                        this.countdown--;
                                    } else {
                                        clearInterval(interval);
                                    }
                                }, 1000);
                            }
                        }"
                        @startcountdown.window="startTimer()"
                        :disabled="countdown > 0 || $wire.resendLoading"
                    >
                        <span wire:loading.remove wire:target="resendOtp" x-show="countdown === 0">Resend Code</span>
                        <span wire:loading.remove wire:target="resendOtp" x-show="countdown > 0">
                            Resend in <span x-text="countdown"></span>s
                        </span>
                        <span wire:loading wire:target="resendOtp">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600 dark:text-blue-400 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        </span>
                    </button>
                </div>
                
                <div>
                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        :disabled="$wire.loading || (strlen($otp) !== 6)"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="verifyOtp">Verify</span>
                        <span wire:loading wire:target="verifyOtp">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verifying...
                        </span>
                    </button>
                </div>
                
                <p class="text-xs text-center text-gray-600 dark:text-gray-400">
                    Please enter the 6-digit code sent to your email
                </p>
            </form>
        @endif
    </div>
</div>
