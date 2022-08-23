<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConversationRequest;
use App\Models\Conversation;
use App\Services\AmazonLexChatBot;

class ConversationController extends Controller
{
    public function sendMessage(ConversationRequest $request)
    {
        if (request()->bot_message_status) {
            if (request()->bot_message_status == 'asking' && preg_match('/[\s]+/i', request()->message)) {
                return response()->json([
                    'message' => 'Message contains spaces are not allowed on this state'
                ], 422);
            }
        }

        $lexChatBot = new AmazonLexChatBot();

        $result = $lexChatBot->sendMessage();

        return [
            'messageId' => $result->{'@metadata'}['headers']['x-amzn-requestid'],
            'message' => $result->message,
            'status' => $result->dialogState,
            'meta' => $result
        ];
    }

    public function store()
    {
        foreach (request()->messages as $message) {
            Conversation::create(['message' => $message]);
        }
    }
}
