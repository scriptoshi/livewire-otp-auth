# Livewire OTP Authentication

A simple and easy-to-use OTP (One-Time Password) Email authentication package for Laravel 12 using Livewire 3.

## Features

- OTP-based authentication for your Laravel application
- Simple email-based verification
- Support for login, registration, and email validation flows
- Rate limiting for OTP requests
- Customizable expiration times
- Responsive UI with live input validation using Flux components
- Easy integration with existing Laravel applications

## Requirements

- PHP 8.2+
- Laravel 12.x
- Livewire 3.x
- Flux 2.x

## Installation

You can install the package via composer:

```bash
composer require scriptoshi/livewire-otp-auth
```

This will automatically install Livewire and Flux if they're not already in your project.

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

-   **expiration_time**: How long an OTP is valid for in minutes (default: 10)
-   **otp_length**: The length of the OTP code (default: 6)
-   **resend_cooldown**: Cooldown time in seconds before a user can request another OTP (default: 60)
-   **rate_limit_attempts**: Number of OTP attempts before rate limiting is applied (default: 5)
-   **rate_limit_duration**: Duration in minutes for which rate limiting is applied (default: 5)

## Usage

### 1. Add the trait to your User model

First, add the `HasOtpAuth` trait to your User model:

```php
use Scriptoshi\LivewireOtpAuth\Traits\HasOtpAuth;

class User extends Authenticatable
{
    use HasOtpAuth;

    // ...
}
```

### 2. Make sure Flux is properly set up

If you haven't already set up Flux in your project, follow the [official Flux documentation](https://github.com/livewire/flux) to make sure it's properly configured.

### 3. Add the component to your views

You can now add the OTP authentication component to your views:

```blade
<livewire:otp-auth />
```

By default, this will use the `login` type and the `request` step. You can customize this by passing parameters:

```blade
<livewire:otp-auth type="register" />
```

Available types:

-   `login`: For existing users (default)
-   `register`: For new users (requires a name field)
-   `validate`: For validating emails of logged-in users

### 4. Customizing the UI

If you want to customize the UI, you can publish the views:

```bash
php artisan vendor:publish --tag=livewire-otp-auth
```

Then you can edit the views in `resources/views/vendor/livewire-otp-auth`.

### 5. Configure Tailwind CSS

To ensure that Tailwind CSS properly processes the component styles, add this package to your content sources in your CSS file (typically `resources/css/app.css`):

```css
/* Add this line with your other @source directives */
@source '../../vendor/scriptoshi/livewire-otp-auth/resources/views/**/*.blade.php';
```

For example, your CSS file might look similar to this:

```css
@import "tailwindcss";
@source "../views";
@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../vendor/scriptoshi/livewire-otp-auth/resources/views/**/*.blade.php';
/* Rest of your CSS file */
```

### 6. Flux Components

This package uses Flux components to provide a consistent and beautiful UI. The components include:

- `flux:input` - Used for email and name inputs
- `flux:button` - Used for action buttons

These components automatically inherit your application's theme and styling, providing a seamless integration with your Laravel application.

## Routes

The package automatically registers a route for verifying OTPs via email links:

```
GET /otp/verify/{code}
```

This route is named `otp.verify` and can be used to verify OTPs sent via email.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
