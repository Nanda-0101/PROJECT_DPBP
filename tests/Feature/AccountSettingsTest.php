<?php

namespace Tests\Feature;

use App\Helpers\ImageHelper;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
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
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function test_account_settings_require_login(): void
    {
        $this->get(route('account.settings'))->assertRedirect(route('login'));
        $this->patch(route('account.settings.update'))->assertRedirect(route('login'));
    }

    public function test_user_can_update_profile_without_changing_password(): void
    {
        $user = $this->createUser();
        $oldPassword = $user->password;

        $this->actingAs($user)->get(route('account.settings'))
            ->assertOk()
            ->assertSee('Pengaturan akun')
            ->assertSee('nav-profile', false);

        $this->patch(route('account.settings.update'), [
            'name' => 'Nama Baru',
            'email' => 'new@example.test',
            'phone' => '081234567899',
        ])->assertRedirect(route('account.settings'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'email' => 'new@example.test',
            'phone' => '081234567899',
            'password' => $oldPassword,
        ]);
    }

    public function test_user_must_verify_current_password_before_changing_it(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->patch(route('account.settings.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'wrong-password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->patch(route('account.settings.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect(route('account.settings'));

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_account_avatar_is_only_accessible_by_its_owner(): void
    {
        Storage::fake('public');
        $user = $this->createUser(['avatar' => 'avatars/profile.jpg']);
        $image = file_get_contents(storage_path('app/public/products/sony-a7iii.jpg'));
        Storage::disk('public')->put('avatars/profile.jpg', $image);
        $url = ImageHelper::url($user->avatar);

        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($user)->get($url)->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        $otherUser = $this->createUser([
            'email' => 'other@example.test',
            'avatar' => 'avatars/other.jpg',
        ]);
        $this->actingAs($otherUser)->get($url)->assertNotFound();
    }

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'email' => 'user@example.test',
            'password' => Hash::make('oldpassword123'),
            'phone' => '081111111111',
            'role' => 'user',
            'status' => 'Aktif',
        ], $overrides));
    }
}
