# Livewire OTP Authentication

A simple and easy-to-use OTP (One-Time Password) Email authentication package for Laravel 12 using Livewire 3.

## Features

- OTP-based authentication for your Laravel application
- Simple email-based verification
- Support for login, registration, and email validation flows
- Rate limiting for OTP requests
- Customizable expiration times
- Responsive UI with live input validation
- Easy integration with existing Laravel applications

## Requirements

- PHP 8.2+
- Laravel 12.x
- Livewire 3.x

## Installation

You can install the package via composer:

```bash
composer require scriptoshi/livewire-otp-auth
```

After installing the package, you need to run the migrations:

```bash
php artisan migrate
```

This will add the necessary columns to your users table.

## Configuration

You can publish the configuration file:

```bash
php artisan vendor:publish --tag=livewire-otp-auth
```

This will create a `livewire-otp-auth.php` file in your `config` directory. You can modify the following settings:

- **expiration_time**: How long an OTP is valid for in minutes (default: 10)
- **otp_length**: The length of the OTP code (default: 6)
- **resend_cooldown**: Cooldown time in seconds before a user can request another OTP (default: 60)
- **rate_limit_attempts**: Number of OTP attempts before rate limiting is applied (default: 5)
- **rate_limit_duration**: Duration in minutes for which rate limiting is applied (default: 5)

## Usage

### Add the trait to your User model

First, add the `HasOtpAuth` trait to your User model:

```php
use Scriptoshi\LivewireOtpAuth\Traits\HasOtpAuth;

class User extends Authenticatable
{
    use HasOtpAuth;
    
    // ...
}
```

### Add the component to your views

You can now add the OTP authentication component to your views:

```blade
<livewire:otp-authentication />
```

By default, this will use the `login` type and the `request` step. You can customize this by passing parameters:

```blade
<livewire:otp-authentication type="register" />
```

Available types:
- `login`: For existing users (default)
- `register`: For new users (requires a name field)
- `validate`: For validating emails of logged-in users

### Customizing the UI

If you want to customize the UI, you can publish the views:

```bash
php artisan vendor:publish --tag=livewire-otp-auth
```

Then you can edit the views in `resources/views/vendor/livewire-otp-auth`.

## Routes

The package automatically registers a route for verifying OTPs via email links:

```
GET /otp/verify/{code}
```

This route is named `otp.verify` and can be used to verify OTPs sent via email.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
