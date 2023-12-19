<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pusher\Pusher;
use App\Models\Chat;

class PusherController extends Controller
{
    public function sendMessage(Request $request)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required',
                'message' => 'required|string',
            ]);

            // Save the message to the chats table
            $chat = Chat::create([
                'user_id' => $request->user_id,
                'message' => $request->message,
            ]);

            // Broadcast the message using Pusher
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                [
                    'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                    'encrypted' => true,
                ]
            );

            // Broadcast the message to the chat-channel
            $pusher->trigger('chat-channel', 'message', [
                'user_id' => $chat->user_id,
                'message' => $chat->message,
            ]);

            return response(['message' => 'Message sent and saved successfully']);
        } catch (\Exception $e) {
            return response(['message' => 'Error sending and saving message', 'error' => $e->getMessage()], 500);
        }
    }
}
