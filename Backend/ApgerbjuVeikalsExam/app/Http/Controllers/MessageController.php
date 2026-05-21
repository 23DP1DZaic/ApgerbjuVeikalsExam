<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Listing;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::with([
                'listing.images',
                'buyer',
                'seller',
                'latestMessage.sender',
            ])
            ->where(function ($query) use ($user) {
                $query->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->withMax('messages', 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->get()
            ->map(function ($conversation) use ($user) {
                $unreadCount = $conversation->messages()
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->count();

                $conversation->unread_count = $unreadCount;
                $conversation->last_message = $conversation->latestMessage;

                return $conversation;
            });

        return response()->json($conversations);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if (
            $conversation->buyer_id !== $user->id &&
            $conversation->seller_id !== $user->id
        ) {
            return response()->json([
                'message' => 'No permission',
            ], 403);
        }

        $conversation->load([
            'listing.images',
            'buyer',
            'seller',
            'messages.sender',
        ]);

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return response()->json($conversation);
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $conversationIds = Conversation::where(function ($query) use ($user) {
                $query->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->pluck('id');

        $count = \App\Models\Message::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    public function start(Request $request, Listing $listing)
    {
        $user = $request->user();

        if ($listing->user_id === $user->id) {
            return response()->json([
                'message' => 'You cannot message yourself',
            ], 422);
        }

        $conversation = Conversation::firstOrCreate([
            'listing_id' => $listing->id,
            'buyer_id' => $user->id,
            'seller_id' => $listing->user_id,
        ]);

        return response()->json(
            $conversation->load([
                'listing.images',
                'buyer',
                'seller',
                'messages.sender',
            ])
        );
    }

    public function send(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if (
            $conversation->buyer_id !== $user->id &&
            $conversation->seller_id !== $user->id
        ) {
            return response()->json([
                'message' => 'No permission',
            ], 403);
        }

        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $data['body'],
        ]);

        $conversation->touch();

        return response()->json(
            $message->load('sender'),
            201
        );
    }
}