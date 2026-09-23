<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_seeder_is_idempotent_and_repairs_required_credentials_and_flags(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $this->assertTrue(Hash::check('admin123', $admin->password));

        $admin->update([
            'password' => Hash::make('incorrect'),
            'role' => 'customer',
            'status' => 'inactive',
        ]);

        $this->seed(AdminUserSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $admin->refresh();

        $this->assertSame(1, User::query()->where('username', 'admin')->count());
        $this->assertSame('admin', $admin->role);
        $this->assertSame('active', $admin->status);
        $this->assertTrue(Hash::check('admin123', $admin->password));
    }
}
