<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only add columns if they don't exist
            if (!Schema::hasColumn('users', 'otp')) {
                $table->unsignedInteger('otp')->nullable()
                    ->after('remember_token')
                    ->comment('One-time password for verification');
            }

            if (!Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()
                    ->after('otp')
                    ->comment('Expiration time for OTP');
            }

            // The following changes are optional and might depend on your app's requirements
            // so we'll add a check to avoid breaking existing apps
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->change();
            }

            if (!Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_expires_at']);
        });
    }
};
