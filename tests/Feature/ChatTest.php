<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('role')->default('user');
            $table->string('status')->default('Aktif');
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('chat_conversations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable();
            $table->string('guest_token_hash', 64)->nullable()->index();
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id');
            $table->string('sender', 20);
            $table->text('message');
            $table->timestamps();
        });
    }

    public function test_guest_chat_uses_fake_ollama_and_saves_history(): void
    {
        Http::fake([
            '*/api/chat' => function ($request) {
                $payload = $request->data();
                $systemPrompt = collect($payload['messages'])
                    ->firstWhere('role', 'system')['content'] ?? '';

                $this->assertStringContainsString('rental peralatan kreatif', strtolower($systemPrompt));
                $this->assertStringContainsString('bukan platform properti', strtolower($systemPrompt));
                $this->assertStringContainsString('Informasi tersebut belum tersedia', $systemPrompt);
                $this->assertSame(false, $payload['stream']);
                $this->assertCount(2, $payload['messages']);

                return Http::response([
                'message' => ['role' => 'assistant', 'content' => 'Untuk menyewa, pilih alat di Marketplace lalu lanjutkan checkout.'],
                ]);
            },
        ]);

        $this->postJson('/cs/chat', ['message' => 'Bagaimana cara menyewa kamera?'])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Untuk menyewa, pilih alat di Marketplace lalu lanjutkan checkout.']);

        $this->getJson('/cs/chat/history')
            ->assertOk()
            ->assertJsonCount(2, 'messages');

        $this->assertDatabaseHas('chat_messages', [
            'sender' => 'user',
            'message' => 'Bagaimana cara menyewa kamera?',
        ]);
    }

    public function test_user_message_is_saved_when_ollama_is_offline(): void
    {
        Http::fake([
            '*/api/chat' => Http::response([], 500),
        ]);

        $this->postJson('/cs/chat', ['message' => 'Pesan yang tetap tersimpan'])
            ->assertStatus(503);

        $this->assertDatabaseHas('chat_messages', [
            'sender' => 'user',
            'message' => 'Pesan yang tetap tersimpan',
        ]);
    }

    public function test_one_user_cannot_read_another_users_history(): void
    {
        $first = User::create([
            'name' => 'User Satu',
            'email' => 'satu@example.test',
            'password' => 'password',
            'role' => 'user',
            'status' => 'Aktif',
        ]);
        $second = User::create([
            'name' => 'User Dua',
            'email' => 'dua@example.test',
            'password' => 'password',
            'role' => 'user',
            'status' => 'Aktif',
        ]);

        Http::fake([
            '*/api/chat' => Http::response([
                'message' => ['role' => 'assistant', 'content' => 'Jawaban rahasia satu.'],
            ]),
        ]);

        $this->actingAs($first)
            ->postJson('/cs/chat', ['message' => 'Pesan satu'])
            ->assertOk();

        $this->actingAs($second)
            ->getJson('/cs/chat/history')
            ->assertOk()
            ->assertJsonCount(0, 'messages');
    }

    public function test_empty_and_too_long_messages_are_rejected(): void
    {
        $this->postJson('/cs/chat', ['message' => ''])
            ->assertUnprocessable();

        $this->postJson('/cs/chat', ['message' => str_repeat('a', 2001)])
            ->assertUnprocessable();
    }
}
