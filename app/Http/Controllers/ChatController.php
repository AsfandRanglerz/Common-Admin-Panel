<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\SubAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function fetchMessages($receiver_id)
    {
        // Debugging - check if user is logged in
        if (!auth()->check()) {
            return response()->json(['error' => 'User not logged in'], 401);
        }

        $user_id = Auth::id();

        $messages = Message::where(function ($q) use ($user_id, $receiver_id) {
                $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
            })
            ->orWhere(function ($q) use ($user_id, $receiver_id) {
                $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    { 
        // Check authentication
        if (!auth()->check()) {
            return response()->json(['error' => 'User not logged in'], 401);
        }

        // Debug request
        \Log::info('Send message request:', $request->all());

        try {
            $message = new Message();
            $message->sender_id = auth()->id();
            $message->receiver_id = $request->receiver_id;
            $message->message = $request->message;
            $message->language = $request->language;
            $message->save();

            // Event trigger
            event(new \App\Events\MessageSent(auth()->id(), $request->receiver_id, $request->message));

            return response()->json([
                'status' => 'success', 
                'message' => 'Message sent!',
                'data' => $message
            ]);

        } catch (\Exception $e) {
            \Log::error('Message send error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    public function chatPage($id)    
    {
        // Check authentication
        if (!auth()->guard('subadmin')) {
            return redirect('admin/dashboard')->with('error', 'Please login first');
        }

        $receiver = SubAdmin::findOrFail($id);
        $current_user = auth()->user();

        $messages = Message::where(function ($query) use ($id) {
            $query->where('sender_id', auth()->id())
                  ->where('receiver_id', $id);
        })->orWhere(function ($query) use ($id) {
            $query->where('sender_id', $id)
                  ->where('receiver_id', auth()->id());
        })->get();

        $receiver_id = $id;

        return view('admin.chat', compact('receiver', 'messages', 'receiver_id', 'current_user'));
    }
}