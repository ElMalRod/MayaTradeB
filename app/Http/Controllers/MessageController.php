<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    // Enviar un mensaje
    public function sendMessage(Request $request)
    {
        $request->validate([
            'sender_name' => 'required|string',
            'recipient_name' => 'required|string',
            'content' => 'required|string',
        ]);

        $sender = User::where('name', $request->sender_name)->firstOrFail();
        $recipient = User::where('name', $request->recipient_name)->firstOrFail();

        $message = new Message();
        $message->sender_id = $sender->id;
        $message->recipient_id = $recipient->id;
        $message->content = $request->content;
        $message->save();

        return response()->json(['message' => 'Mensaje enviado correctamente.'], 200);
    }

    // Obtener mensajes entre dos usuarios especificados por nombre
    public function getMessages(Request $request)
    {
        $request->validate([
            'user_one_name' => 'required|string',  // Nombre del primer usuario
            'user_two_name' => 'required|string',  // Nombre del segundo usuario
        ]);

        $userOne = User::where('name', $request->user_one_name)->firstOrFail();
        $userTwo = User::where('name', $request->user_two_name)->firstOrFail();

        $messages = Message::where(function($query) use ($userOne, $userTwo) {
            $query->where('sender_id', $userOne->id)->where('recipient_id', $userTwo->id);
        })->orWhere(function($query) use ($userOne, $userTwo) {
            $query->where('sender_id', $userTwo->id)->where('recipient_id', $userOne->id);
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    // Obtener todos los mensajes de un usuario con cualquier otro usuario
    public function getAllUserMessages(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string',  // Nombre del usuario
        ]);

        $user = User::where('name', $request->user_name)->firstOrFail();

        $messages = Message::where('sender_id', $user->id)
                           ->orWhere('recipient_id', $user->id)
                           ->orderBy('created_at', 'desc')
                           ->get();

        // Agrupar mensajes por el otro usuario involucrado
        $conversations = [];
        foreach ($messages as $message) {
            $otherUserId = $message->sender_id == $user->id ? $message->recipient_id : $message->sender_id;
            $otherUser = User::find($otherUserId);
            $conversations[$otherUser->name][] = $message;
        }

        return response()->json($conversations);
    }
}
