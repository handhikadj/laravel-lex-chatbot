<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\AmazonLexChatBot;

class ConversationController extends Controller
{
    public function sendMessage()
    {
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
