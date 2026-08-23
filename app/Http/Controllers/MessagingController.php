<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use App\Services\MessagingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessagingController extends Controller
{
    public function __construct(
        protected MessagingService $messagingService
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $conversations = $this->messagingService->getUserConversations($user);

        $activeConversationId = $request->query('conversation_id');
        $activeConversation = null;

        if ($activeConversationId) {
            $activeConversation = Conversation::query()
                ->where('id', $activeConversationId)
                ->where(function ($q) use ($user) {
                    $q->where('user_one_id', $user->id)
                      ->orWhere('user_two_id', $user->id);
                })
                ->with(['userOne', 'userTwo', 'messages.sender'])
                ->first();
        }

        if (! $activeConversation && $conversations->count() > 0) {
            $activeConversation = Conversation::query()
                ->where('id', $conversations->first()->id)
                ->with(['userOne', 'userTwo', 'messages.sender'])
                ->first();
        }

        if ($activeConversation) {
            $this->messagingService->markConversationAsRead($activeConversation, $user);
        }

        return view('messaging.index', compact(
            'conversations',
            'activeConversation'
        ));
    }

    public function startConversation(User $user): RedirectResponse
    {
        try {
            $conversation = $this->messagingService->getOrCreateConversation(Auth::user(), $user);

            return redirect()->route('messaging.index', ['conversation_id' => $conversation->id]);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['messaging' => $e->getMessage()]);
        }
    }

    public function sendMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = Auth::user();

        if ((int) $conversation->user_one_id !== (int) $user->id && (int) $conversation->user_two_id !== (int) $user->id) {
            abort(403, 'غير مصرح لك بإرسال رسائل في هذه المحادثة.');
        }

        $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $this->messagingService->sendMessage($conversation, $user, $request->input('content'));

        return redirect()->route('messaging.index', ['conversation_id' => $conversation->id]);
    }

    public function pollMessages(Conversation $conversation): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if ((int) $conversation->user_one_id !== (int) $user->id && (int) $conversation->user_two_id !== (int) $user->id) {
            abort(403, 'غير مصرح لك باستعراض هذه المحادثة.');
        }

        $this->messagingService->markConversationAsRead($conversation, $user);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender?->name,
                    'sender_avatar' => $msg->sender?->avatar_url,
                    'content' => $msg->content,
                    'is_mine' => (int) $msg->sender_id === (int) $user->id,
                    'created_at_human' => $msg->created_at->format('H:i'),
                ];
            });

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function sendMessageJson(Request $request, Conversation $conversation): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if ((int) $conversation->user_one_id !== (int) $user->id && (int) $conversation->user_two_id !== (int) $user->id) {
            abort(403, 'غير مصرح لك بإرسال رسائل في هذه المحادثة.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $msg = $this->messagingService->sendMessage($conversation, $user, $validated['content']);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $msg->id,
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'sender_avatar' => $user->avatar_url,
                'content' => $msg->content,
                'is_mine' => true,
                'created_at_human' => $msg->created_at->format('H:i'),
            ],
        ]);
    }
}
