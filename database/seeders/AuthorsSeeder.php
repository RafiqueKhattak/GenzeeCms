<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds a small editorial team so posts have a real "Posted by" byline
 * instead of every article showing the single admin account. These are
 * genuine `editor`-role accounts (same as any account created from
 * /admin/users) with a random, never-shared password — nobody is expected
 * to log in as them, they exist for author attribution. An admin can give
 * one a real password later via "Send password reset link" if a specific
 * writer needs to log in as themselves.
 *
 * Idempotent: users are firstOrCreate'd by email, and only posts that
 * still have no author_id are backfilled, so re-running never reassigns
 * an already-credited post or duplicates a writer.
 */
class AuthorsSeeder extends Seeder
{
    protected const AUTHORS = [
        'Ayesha Raza',
        'Bilal Ahmed',
        'Fatima Sheikh',
        'Hassan Malik',
        'Kiran Iqbal',
        'Omar Farooq',
        'Sana Tariq',
        'Usman Ghani',
        'Zara Khan',
        'Imran Siddiqui',
    ];

    public function run(): void
    {
        $authors = collect(self::AUTHORS)->map(function (string $name) {
            $email = Str::slug($name, '.').'@genzeelogics.com';

            return User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => 'editor',
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password' => Str::random(32),
                ]
            );
        });

        Post::whereNull('author_id')->get()->each(function (Post $post) use ($authors) {
            $post->update(['author_id' => $authors->random()->id]);
        });
    }
}
