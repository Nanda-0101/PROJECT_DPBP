<?php

namespace Tests\Feature;

use App\Models\MitraProfile;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MitraProfileUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('role', 20)->default('user');
            $table->string('status', 40)->default('Aktif');
            $table->string('avatar')->nullable();
            $table->timestamps();
        });

        Schema::create('mitra_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('owner_name', 120);
            $table->char('nik', 16)->unique();
            $table->string('category', 50);
            $table->string('city', 80);
            $table->string('bank_account', 120);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mitra_documents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('mitra_id');
            $table->string('photo_url', 500);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function test_guest_registration_activates_mitra_account_immediately(): void
    {
        Storage::fake('public');

        $this->post(route('mitra.store'), [
            'nama' => 'Studio Budi',
            'pemilik' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'studio@example.test',
            'password' => 'password',
            'nik' => '3201234567890001',
            'kategori' => 'Kamera',
            'kota' => 'Bandung',
            'rekening' => 'BCA 123 a.n. Budi',
            'foto' => [UploadedFile::fake()->createWithContent(
                'alat.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lS8AAAAASUVORK5CYII=')
            )],
        ])->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Pendaftaran mitra berhasil! Akunmu sekarang aktif sebagai mitra.');

        $user = User::where('email', 'studio@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'mitra',
            'status' => 'Terverifikasi',
        ]);
        $profile = MitraProfile::where('user_id', $user->id)->firstOrFail();
        $this->assertNotNull($profile->verified_at);
        $this->assertDatabaseHas('mitra_documents', ['mitra_id' => $profile->id]);
    }

    public function test_existing_user_becomes_active_mitra_immediately(): void
    {
        Storage::fake('public');
        $user = $this->createUser(['role' => 'user', 'status' => 'Aktif']);

        $this->actingAs($user)->post(route('mitra.store'), [
            'nama' => 'Studio Budi',
            'pemilik' => 'Budi Santoso',
            'nik' => '3201234567890001',
            'kategori' => 'Kamera',
            'kota' => 'Bandung',
            'rekening' => 'BCA 123 a.n. Budi',
            'foto' => [UploadedFile::fake()->createWithContent(
                'alat.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lS8AAAAASUVORK5CYII=')
            )],
        ])->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'mitra',
            'status' => 'Terverifikasi',
        ]);
        $profile = MitraProfile::where('user_id', $user->id)->firstOrFail();
        $this->assertNotNull($profile->verified_at);
    }

    public function test_mitra_can_update_own_profile_without_waiting_for_verification(): void
    {
        $user = $this->createUser(['role' => 'mitra', 'status' => 'Menunggu Verifikasi']);
        $profile = $this->createProfile($user, ['verified_at' => null]);
        $profile->documents()->create([
            'photo_url' => 'mitra/foto-lama.jpg',
            'sort_order' => 0,
        ]);

        $this->actingAs($user)->get(route('mitra.index'))
            ->assertOk()
            ->assertSee('Perbarui profil mitra')
            ->assertSee('value="3201234567890001"', false);

        $this->actingAs($user)->patch(route('mitra.update'), [
            'nama' => 'Budi Rental',
            'pemilik' => 'Budi Santoso',
            'nik' => '3201234567890002',
            'kategori' => 'Lighting',
            'kota' => 'Bandung',
            'rekening' => 'BCA 123 a.n. Budi',
        ])->assertRedirect(route('mitra.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Budi Rental',
            'role' => 'mitra',
            'status' => 'Terverifikasi',
        ]);
        $updatedProfile = MitraProfile::findOrFail($profile->id);
        $this->assertSame('Budi Santoso', $updatedProfile->owner_name);
        $this->assertSame('3201234567890002', $updatedProfile->nik);
        $this->assertSame('Lighting', $updatedProfile->category);
        $this->assertSame('Bandung', $updatedProfile->city);
        $this->assertSame('BCA 123 a.n. Budi', $updatedProfile->bank_account);
        $this->assertNotNull($updatedProfile->verified_at);
        $this->assertDatabaseHas('mitra_documents', [
            'mitra_id' => $profile->id,
            'photo_url' => 'mitra/foto-lama.jpg',
        ]);
    }

    public function test_unchanged_verified_profile_keeps_its_verification(): void
    {
        $user = $this->createUser(['role' => 'mitra', 'status' => 'Terverifikasi']);
        $profile = $this->createProfile($user);

        $this->actingAs($user)->patch(route('mitra.update'), [
            'nama' => $user->name,
            'pemilik' => $profile->owner_name,
            'nik' => $profile->nik,
            'kategori' => $profile->category,
            'kota' => $profile->city,
            'rekening' => $profile->bank_account,
        ])->assertRedirect(route('mitra.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'Terverifikasi',
        ]);
        $this->assertDatabaseHas('mitra_profiles', [
            'id' => $profile->id,
            'verified_at' => '2026-09-18 06:41:36',
        ]);
    }

    public function test_mitra_cannot_use_another_profiles_nik(): void
    {
        $user = $this->createUser(['role' => 'mitra']);
        $profile = $this->createProfile($user);
        $anotherUser = $this->createUser(['email' => 'other@example.test', 'role' => 'mitra']);
        $this->createProfile($anotherUser, ['nik' => '3201234567890002']);

        $this->actingAs($user)->patch(route('mitra.update'), [
            'nama' => $user->name,
            'pemilik' => $profile->owner_name,
            'nik' => '3201234567890002',
            'kategori' => $profile->category,
            'kota' => $profile->city,
            'rekening' => $profile->bank_account,
        ])->assertSessionHasErrors('nik');

        $this->assertDatabaseHas('mitra_profiles', [
            'id' => $profile->id,
            'nik' => $profile->nik,
        ]);
    }

    public function test_admin_role_cannot_be_changed_by_mitra_registration(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)->get(route('mitra.index'))
            ->assertOk()
            ->assertSee('Pengelolaan akun admin dilakukan melalui konfigurasi database.')
            ->assertDontSee('Daftar Sebagai Mitra');

        $this->actingAs($admin)->post(route('mitra.store'))
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
        $this->assertDatabaseMissing('mitra_profiles', [
            'user_id' => $admin->id,
        ]);
    }

    private function createUser(array $overrides = []): User
    {
        static $nextId = 0;
        $nextId++;

        return User::create(array_merge([
            'name' => 'Mitra Test',
            'email' => "mitra{$nextId}@example.test",
            'password' => 'unused',
            'phone' => '081234567890',
            'role' => 'mitra',
            'status' => 'Terverifikasi',
        ], $overrides));
    }

    private function createProfile(User $user, array $overrides = []): MitraProfile
    {
        return MitraProfile::create(array_merge([
            'user_id' => $user->id,
            'owner_name' => 'Budi Wijaya',
            'nik' => '3201234567890001',
            'category' => 'Campuran',
            'city' => 'Jakarta Selatan',
            'bank_account' => 'BCA 1234567890 a.n. Budi',
            'verified_at' => '2026-09-18 06:41:36',
        ], $overrides));
    }
}