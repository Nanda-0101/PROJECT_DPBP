<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\OllamaChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct(private OllamaChatService $ollama)
    {
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.max' => 'Pesan terlalu panjang. Maksimal 2.000 karakter.',
        ]);

        $message = trim($validated['message']);
        if ($message === '') {
            return response()->json(['message' => 'Pesan tidak boleh kosong.'], 422);
        }

        $conversation = $this->ownedConversation($request);
        $conversation->messages()->create([
            'sender' => 'user',
            'message' => $message,
        ]);

        $history = $conversation->messages()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $result = $this->ollama->chat($history->all());

        if (!$result['ok']) {
            return response()->json([
                'message' => $result['message'],
            ], 503);
        }

        $conversation->messages()->create([
            'sender' => 'assistant',
            'message' => $result['message'],
        ]);

        return response()->json([
            'message' => $result['message'],
            'conversation_uuid' => $conversation->uuid,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $conversation = $this->ownedConversation($request);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit(100)
            ->get(['sender', 'message', 'created_at'])
            ->map(fn (ChatMessage $message) => [
                'sender' => $message->sender,
                'message' => $message->message,
                'created_at' => $message->created_at,
            ]);

        return response()->json([
            'conversation_uuid' => $conversation->uuid,
            'messages' => $messages,
        ]);
    }

    private function ownedConversation(Request $request): ChatConversation
    {
        $user = Auth::user();

        if ($user) {
            $guestHash = $this->guestHash($request);
            $guestConversation = ChatConversation::where('guest_token_hash', $guestHash)->latest()->first();
            if ($guestConversation) {
                $guestConversation->update([
                    'user_id' => $user->id,
                    'guest_token_hash' => null,
                ]);
                $accountConversation = ChatConversation::where('user_id', $user->id)
                    ->where('id', '!=', $guestConversation->id)
                    ->latest()
                    ->first();

                if ($accountConversation) {
                    ChatMessage::where('conversation_id', $guestConversation->id)
                        ->update(['conversation_id' => $accountConversation->id]);
                    $guestConversation->delete();
                    return $accountConversation;
                }

                return $guestConversation;
            }

            return ChatConversation::where('user_id', $user->id)->latest()->first()
                ?? ChatConversation::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $user->id,
                    'guest_token_hash' => null,
                ]);
        }

        $guestHash = $this->guestHash($request);

        return ChatConversation::firstOrCreate(
            ['guest_token_hash' => $guestHash],
            ['uuid' => (string) Str::uuid(), 'user_id' => null]
        );
    }

    private function guestHash(Request $request): string
    {
        $guestToken = $request->session()->get('camrent_chat_guest_token');
        if (!$guestToken) {
            $guestToken = (string) Str::uuid();
            $request->session()->put('camrent_chat_guest_token', $guestToken);
        }

        return hash('sha256', 'camrent-chat-'.$guestToken);
    }
}
