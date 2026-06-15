<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdminUser extends Command
{
    protected $signature = 'admin:make
                                {email : The user email address}
                                {--name= : Display name}
                                {--password= : Password (prompted if omitted)}';

    protected $description = 'Grant admin panel access to an existing user, or create a new admin.';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user  = User::where('email', $email)->first();

        if ($user) {
            $user->update(['is_admin' => true]);
            $this->info("✓ Admin access granted to existing user: {$user->name} <{$email}>");
            return self::SUCCESS;
        }

        // Create a new admin user
        $name     = $this->option('name') ?? $this->ask('Full name');
        $password = $this->option('password') ?? $this->secret('Password');

        if (! $password) {
            $this->error('Password is required.');
            return self::FAILURE;
        }

        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->info("✓ New admin user created: {$user->name} <{$email}>");
        $this->line("  Login at: /admin");

        return self::SUCCESS;
    }
}
