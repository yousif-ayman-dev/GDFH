<?php

namespace App\Http\Controllers;

use App\Models\AIConversation;
use App\Services\AIAssistantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AIController extends Controller
{
    public function __construct(
        protected AIAssistantService $aiService
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $analysis = $this->aiService->getWorkspaceAnalysis($user);
        $conversations = $this->aiService->getConversations($user);

        $activeConversationId = $request->query('conversation_id');
        $activeConversation = null;

        if ($activeConversationId) {
            $activeConversation = AIConversation::query()
                ->where('user_id', $user->id)
                ->where('id', $activeConversationId)
                ->with('messages')
                ->first();
        }

        if (! $activeConversation && $conversations->count() > 0) {
            $activeConversation = AIConversation::query()
                ->where('user_id', $user->id)
                ->where('id', $conversations->first()->id)
                ->with('messages')
                ->first();
        }

        if (! $activeConversation) {
            $activeConversation = $this->aiService->createConversation($user, 'المحادثة الرئيسية');
            $conversations = $this->aiService->getConversations($user);
        }

        return view('ai.index', compact(
            'analysis',
            'conversations',
            'activeConversation'
        ));
    }

    public function storeConversation(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $conversation = $this->aiService->createConversation($user, $request->input('title'));

        return redirect()->route('ai.index', ['conversation_id' => $conversation->id])
            ->with('success', 'تم إنشاء محادثة جديدة بنجاح.');
    }

    public function sendMessage(Request $request, AIConversation $conversation): RedirectResponse
    {
        if ((int) $conversation->user_id !== (int) Auth::id()) {
            abort(403, 'غير مصرح لك بالوصول لهذا السجل.');
        }

        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $this->aiService->sendMessage($conversation, $request->input('message'));

        return redirect()->route('ai.index', ['conversation_id' => $conversation->id]);
    }

    public function destroyConversation(AIConversation $conversation): RedirectResponse
    {
        if ((int) $conversation->user_id !== (int) Auth::id()) {
            abort(403, 'غير مصرح لك بإجراء هذه العملية.');
        }

        $this->aiService->deleteConversation($conversation);

        return redirect()->route('ai.index')
            ->with('success', 'تم حذف المحادثة بنجاح.');
    }

    public function quickChat(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
        $conversations = $this->aiService->getConversations($user);
        $conversation = $conversations->first();

        if (! $conversation) {
            $conversation = $this->aiService->createConversation($user, 'المحادثة السريعة');
        }

        $assistantMessage = $this->aiService->sendMessage($conversation, $request->input('message'));

        return response()->json([
            'status' => 'success',
            'response' => $assistantMessage->content,
        ]);
    }
}
