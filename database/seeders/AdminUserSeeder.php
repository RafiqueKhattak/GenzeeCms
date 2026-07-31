<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@genzeelogics.com');

        if (User::where('email', $email)->exists()) {
            return;
        }

        $password = env('ADMIN_PASSWORD') ?: Str::password(16);

        User::create([
            'name' => 'GenzeeLogics Admin',
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        if (! env('ADMIN_PASSWORD')) {
            $this->command->warn("Admin created — email: {$email} / password: {$password} (save this, it will not be shown again)");
        }
    }
}
