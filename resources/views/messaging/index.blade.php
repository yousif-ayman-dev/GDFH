<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          الرسائل والمحادثات المباشرة (Direct Messaging)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          التواصل المباشر الآمن بين العملاء والمستقلين وأعضاء فريق العمل.
        </p>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-7xl space-y-6">

      <div class="gdfh-card overflow-hidden grid grid-cols-1 md:grid-cols-4 min-h-[600px]">
        
        {{-- Conversations Sidebar --}}
        <div class="border-e border-[rgb(var(--color-border))] p-4 space-y-3 bg-[rgb(var(--color-surface-soft)/0.3)]">
          <div class="border-b border-[rgb(var(--color-border))] pb-3">
            <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">المحادثات المباشرة</h3>
          </div>

          <div class="space-y-1 overflow-y-auto max-h-[500px]">
            @forelse ($conversations as $conv)
            @php
            $otherUser = $conv->getOtherUser(Auth::user());
            $unreadCount = $conv->unreadCountFor(Auth::user());
            $isActive = $activeConversation && $activeConversation->id === $conv->id;
            @endphp
            <a href="{{ route('messaging.index', ['conversation_id' => $conv->id]) }}" class="flex items-center justify-between p-3 rounded-xl transition text-xs {{ $isActive ? 'bg-[rgb(var(--color-surface))] font-bold text-[rgb(var(--color-copper))] shadow-sm border border-[rgb(var(--color-border))]' : 'text-[rgb(var(--color-text-primary))] hover:bg-[rgb(var(--color-surface)/0.6)]' }}">
              <div class="flex items-center gap-3 min-w-0">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-xs font-bold text-[rgb(var(--color-copper))]">
                  {{ mb_substr($otherUser->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                  <h4 class="font-bold text-[rgb(var(--color-text-primary))] truncate">{{ $otherUser->name }}</h4>
                  <p class="text-[11px] text-[rgb(var(--color-text-secondary))] truncate">
                    {{ $conv->lastMessage?->content ?? 'بداية المحادثة' }}
                  </p>
                </div>
              </div>

              @if ($unreadCount > 0)
              <span class="flex h-5 min-w-[20px] px-1.5 items-center justify-center rounded-full text-[10px] font-bold bg-[rgb(var(--color-copper))] text-white shrink-0">
                {{ $unreadCount }}
              </span>
              @endif
            </a>
            @empty
            <div class="p-8 text-center text-xs text-[rgb(var(--color-text-secondary))]">
              لا توجد محادثات نشطة حتى الآن.
            </div>
            @endforelse
          </div>
        </div>

        {{-- Active Chat Interface --}}
        <div class="md:col-span-3 flex flex-col justify-between bg-[rgb(var(--color-surface))]">
          
          @if ($activeConversation)
          @php
          $recipient = $activeConversation->getOtherUser(Auth::user());
          @endphp

          {{-- Recipient Header --}}
          <div class="border-b border-[rgb(var(--color-border))] p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-xs font-bold text-[rgb(var(--color-copper))]">
                {{ mb_substr($recipient->name, 0, 1) }}
              </div>
              <div>
                <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">{{ $recipient->name }}</h3>
                <p class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $recipient->username ? '@'.$recipient->username : 'مستخدم موثق' }}</p>
              </div>
            </div>

            <a href="{{ route('marketplace.freelancers.show', $recipient) }}" class="gdfh-btn gdfh-btn-secondary text-xs py-1.5 px-3">
              الملف الشخصي
            </a>
          </div>

          {{-- Messages List Window --}}
          <div class="p-6 space-y-4 overflow-y-auto max-h-[460px] flex-1">
            @forelse ($activeConversation->messages as $msg)
            @php
            $isMine = (int) $msg->sender_id === (int) Auth::id();
            @endphp
            <div class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }}">
              <div class="max-w-xl rounded-2xl p-4 text-xs leading-relaxed {{ $isMine ? 'bg-[rgb(var(--color-copper))] text-white rounded-br-none' : 'bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-primary))] border border-[rgb(var(--color-border))] rounded-bl-none' }}">
                <div class="whitespace-pre-line">{{ $msg->content }}</div>
              </div>
              <div class="flex items-center gap-1.5 mt-1 text-[10px] text-[rgb(var(--color-text-secondary))] px-1">
                <span>{{ $msg->created_at->format('H:i') }}</span>
                @if ($isMine)
                <span class="flex items-center text-blue-500 font-semibold">
                  <svg class="h-3.5 w-3.5 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5{{ $msg->isRead() ? 'm-10.5 0 6 6 9-13.5' : '' }}"/></svg>
                </span>
                @endif
              </div>
            </div>
            @empty
            <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))]">
              بداية المحادثة المباشرة مع {{ $recipient->name }}. أرسل أول رسالة الآن!
            </div>
            @endforelse
          </div>

          {{-- Message Input Form --}}
          <form method="POST" action="{{ route('messaging.send', $activeConversation) }}" class="p-4 border-t border-[rgb(var(--color-border))] flex items-center gap-3">
            @csrf
            <input type="text" name="content" required placeholder="اكتب رسالتك المباشرة هنا..." class="flex-1 rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft))] p-3 text-xs text-[rgb(var(--color-text-primary))] placeholder:text-[rgb(var(--color-text-secondary))] focus:outline-none focus:ring-1 focus:ring-[rgb(var(--color-copper))]">
            
            <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-3 px-6 font-bold">
              إرسال
            </button>
          </form>

          @else
          <div class="p-16 text-center text-xs text-[rgb(var(--color-text-secondary))] space-y-3">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))] mx-auto">
              <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008H8.625V12zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008h.008V12zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008h.008V12c0 3.728-4.03 6.75-9 6.75a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-3.728 4.03-6.75 9-6.75s9 3.022 9 6.75z"/></svg>
            </div>
            <p>اختر محادثة من القائمة الجانبية أو ابدأ محادثة جديدة من ملف أي مستقل أو صاحب عمل.</p>
          </div>
          @endif

        </div>

      </div>

    </div>
  </div>
</x-app-layout>
