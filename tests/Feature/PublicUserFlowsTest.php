<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicUserFlowsTest extends TestCase
{
    public function test_guests_can_open_login_and_customer_support_pages(): void
    {
        $this->createTicketsTable();
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        $this->get('/login')->assertOk();
        $this->get('/cs')->assertOk();
        $this->get('/dashboard')->assertOk();
    }

    public function test_guest_cannot_submit_a_support_ticket_without_an_account(): void
    {
        $this->createTicketsTable();

        $this->get('/cs')
            ->assertOk()
            ->assertSee(route('login'))
            ->assertDontSee('Kirim Pengaduan');

        $this->post('/cs', [
            'category' => 'Lainnya',
            'name' => 'Pengunjung',
            'contact' => '081234567890',
            'description' => 'Mohon bantuan terkait layanan.',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Silakan masuk terlebih dahulu.');

        $this->assertDatabaseMissing('tickets', [
            'name' => 'Pengunjung',
            'contact' => '081234567890',
        ]);
    }

    public function test_signed_in_user_can_submit_a_support_ticket(): void
    {
        $this->createTicketsTable();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('role', 20)->default('user');
            $table->string('status', 40)->default('Aktif');
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        $user = User::create([
            'name' => 'Pengguna Terdaftar',
            'email' => 'pengguna@example.test',
            'password' => 'password',
            'phone' => '081234567890',
            'role' => 'user',
            'status' => 'Aktif',
        ]);

        $this->actingAs($user)->post('/cs', [
            'category' => 'Lainnya',
            'name' => $user->name,
            'contact' => $user->phone,
            'description' => 'Mohon bantuan terkait layanan.',
        ])->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'name' => $user->name,
            'contact' => $user->phone,
        ]);
    }

    private function createTicketsTable(): void
    {
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 20);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('category', 80);
            $table->string('name', 120);
            $table->string('contact', 150);
            $table->text('description');
            $table->string('status', 30)->default('Diproses');
            $table->timestamps();
        });
    }

    public function test_legacy_plaintext_password_is_upgraded_after_login(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('role', 20)->default('user');
            $table->string('status', 40)->default('Aktif');
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        User::create([
            'name' => 'Pengguna Lama',
            'email' => 'legacy@example.test',
            'password' => '123456',
            'phone' => '081234567890',
            'role' => 'user',
            'status' => 'Aktif',
        ]);

        $this->post('/login', [
            'email' => 'legacy@example.test',
            'password' => '123456',
        ])->assertRedirect('/dashboard');

        $this->assertTrue(Hash::check(
            '123456',
            User::where('email', 'legacy@example.test')->value('password')
        ));

        $this->post('/login', [
            'email' => 'legacy@example.test',
            'password' => '123456',
        ])->assertRedirect('/dashboard');
    }

    public function test_registration_always_creates_a_user_and_mitra_signup_has_one_route(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('role', 20)->default('user');
            $table->string('status', 40)->default('Aktif');
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        $this->get('/login')
            ->assertOk()
            ->assertSee(route('mitra.index'))
            ->assertDontSee('name="role"', false);

        $this->post(route('register'), [
            'name' => 'Calon Mitra',
            'email' => 'calon@example.test',
            'phone' => '081234567890',
            'password' => 'password123',
            'role' => 'mitra',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'calon@example.test',
            'role' => 'user',
            'status' => 'Aktif',
        ]);
    }
}
