<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[rgb(var(--color-copper))]">
          <span class="flex h-2 w-2 rounded-full bg-[rgb(var(--color-copper))] animate-pulse"></span>
          المساعد الذكي للمؤسسة (Tasker Copilot)
        </div>
        <h1 class="mt-1 text-2xl font-black tracking-tight text-[rgb(var(--color-text-primary))] sm:text-3xl">
          مساعد الذكاء الاصطناعي للمؤسسة (Enterprise AI Assistant)
        </h1>

        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          تحليلات ذكية وتوصيات مباشرة لرفع كفاءة وسريان العمل.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <span class="gdfh-badge gdfh-badge-copper text-xs font-bold px-3 py-1.5 shadow-sm">
          مؤشر صحة الأداء (Health Score): {{ $analysis['health_score'] }}/100
        </span>
      </div>
    </div>
  </x-slot>

  <div class="space-y-8 py-6">

    {{-- 1. AI Health & Workspace Analysis Cards --}}
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-4">
      
      {{-- Health Score Card --}}
      <div class="gdfh-card p-6 flex flex-col justify-between space-y-4 border-s-4 border-s-[rgb(var(--color-copper))] shadow-sm">
        <div>
          <span class="text-xs font-semibold text-[rgb(var(--color-text-secondary))]">صحة وبيئة العمل الحالية</span>
          <div class="mt-2 text-4xl font-black text-[rgb(var(--color-copper))]">{{ $analysis['health_score'] }} <span class="text-sm font-normal text-[rgb(var(--color-text-secondary))]">/ 100</span></div>
        </div>

        <div class="space-y-1 text-xs text-[rgb(var(--color-text-secondary))] border-t border-[rgb(var(--color-border))] pt-3">
          <div>• المشاريع النشطة: <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $analysis['total_projects'] }}</span></div>
          <div>• إجمالي المهام: <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $analysis['total_tasks'] }}</span></div>
          <div>• المهام المتأخرة: <span class="{{ $analysis['overdue_tasks'] > 0 ? 'text-red-500 font-bold' : '' }}">{{ $analysis['overdue_tasks'] }}</span></div>
        </div>
      </div>

      {{-- Strengths & Insights --}}
      <div class="gdfh-card p-6 space-y-3 shadow-sm">
        <h3 class="text-xs font-bold text-emerald-500 flex items-center gap-1.5">
          <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
          <span>نقاط القوة والإنجازات</span>
        </h3>
        <ul class="space-y-2 text-xs text-[rgb(var(--color-text-primary))]">
          @forelse ($analysis['strengths'] as $st)
          <li class="flex items-start gap-1.5">
            <svg class="h-4 w-4 text-emerald-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            <span>{{ $st }}</span>
          </li>
          @empty
          <li class="text-[rgb(var(--color-text-secondary))]">لا توجد ملاحظات قوة مسجلة حتى الآن.</li>
          @endforelse
        </ul>
      </div>

      {{-- Recommendations & Suggestions --}}
      <div class="gdfh-card p-6 space-y-3 shadow-sm">
        <h3 class="text-xs font-bold text-[rgb(var(--color-copper))] flex items-center gap-1.5">
          <svg class="h-4 w-4 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-1.5m0 0a6 6 0 10-6-6c0 2.5 1.5 4.5 3.5 5.5m2.5 2h-5m2.5 2.5h-2.5"/></svg>
          <span>التوصيات الذكية</span>
        </h3>
        <ul class="space-y-2 text-xs text-[rgb(var(--color-text-primary))]">
          @forelse ($analysis['recommendations'] as $rec)
          <li class="flex items-start gap-1.5">
            <span class="text-[rgb(var(--color-copper))] font-bold">←</span>
            <span>{{ $rec }}</span>
          </li>
          @empty
          <li class="text-[rgb(var(--color-text-secondary))]">كل الأمور تسير وفق الخطط المعتمدة.</li>
          @endforelse
        </ul>
      </div>

      {{-- Risk Alerts & Warnings --}}
      <div class="gdfh-card p-6 space-y-3 shadow-sm">
        <h3 class="text-xs font-bold text-red-500 flex items-center gap-1.5">
          <svg class="h-4 w-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
          <span>تنبيهات المخاطر والتحذيرات</span>
        </h3>
        <ul class="space-y-2 text-xs text-[rgb(var(--color-text-primary))]">
          @forelse ($analysis['warnings'] as $warn)
          <li class="flex items-start gap-1.5">
            <span class="text-red-500 font-bold">!</span>
            <span class="text-red-500 font-bold">{{ $warn }}</span>
          </li>
          @empty
          <li class="text-[rgb(var(--color-text-secondary))]">لا توجد مخاطر حرجة مكتشفة.</li>
          @endforelse
        </ul>
      </div>

    </section>

    {{-- 2. Commercial ChatGPT / Claude Level Chat Module --}}
    <section class="gdfh-card overflow-hidden grid grid-cols-1 md:grid-cols-4 min-h-[580px] shadow-sm">
      
      {{-- Conversations Sidebar --}}
      <div class="border-e border-[rgb(var(--color-border))] p-4 space-y-4 bg-[rgb(var(--color-surface-soft)/0.3)]">
        <form method="POST" action="{{ route('ai.conversations.store') }}">
          @csrf
          <button type="submit" class="w-full gdfh-btn gdfh-btn-brand text-xs py-2.5 shadow-sm font-bold">
            + محادثة جديدة
          </button>
        </form>

        <div class="space-y-1 overflow-y-auto max-h-[460px]">
          <h3 class="text-[10px] font-bold text-[rgb(var(--color-text-secondary))] px-2 uppercase tracking-wider">المحادثات السابقة</h3>

          @foreach ($conversations as $conv)
          <div class="flex items-center justify-between group rounded-xl p-2.5 transition text-xs {{ $activeConversation && $activeConversation->id === $conv->id ? 'bg-[rgb(var(--color-surface))] font-bold text-[rgb(var(--color-copper))] shadow-sm border border-[rgb(var(--color-border))]' : 'text-[rgb(var(--color-text-primary))] hover:bg-[rgb(var(--color-surface)/0.6)]' }}">
            <a href="{{ route('ai.index', ['conversation_id' => $conv->id]) }}" class="truncate flex-1 flex items-center gap-2">
              <svg class="h-4 w-4 text-[rgb(var(--color-text-secondary))] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008H8.625V12zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008h.008V12zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008h.008V12c0 3.728-4.03 6.75-9 6.75a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-3.728 4.03-6.75 9-6.75s9 3.022 9 6.75z"/></svg>
              <span class="truncate">{{ $conv->title }}</span>
            </a>

            <form method="POST" action="{{ route('ai.conversations.destroy', $conv) }}" onsubmit="return confirm('حذف هذه المحادثة؟')">
              @csrf
              @method('DELETE')
              <button type="submit" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition px-1" title="حذف">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </form>
          </div>
          @endforeach
        </div>
      </div>

      {{-- Active Chat Interface --}}
      <div class="md:col-span-3 flex flex-col justify-between bg-[rgb(var(--color-surface))]">
        
        {{-- Chat Header --}}
        <div class="border-b border-[rgb(var(--color-border))] p-4 flex items-center justify-between bg-[rgb(var(--color-surface-soft)/0.2)]">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
            </div>
            <div>
              <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                {{ $activeConversation?->title ?? 'مساعد المؤسسة الذكي' }}
              </h3>
              <p class="text-[10px] text-[rgb(var(--color-text-secondary))] flex items-center gap-1">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                محرك استدلال وقواعد النظام الداخلي متصل
              </p>
            </div>
          </div>
          <span class="gdfh-badge gdfh-badge-copper text-[10px]">Copilot v2.5</span>
        </div>

        {{-- Messages History Container --}}
        <div class="p-6 space-y-4 overflow-y-auto max-h-[440px] flex-1">
          @if ($activeConversation && $activeConversation->messages->count() > 0)
            @foreach ($activeConversation->messages as $msg)
            <div class="flex flex-col {{ $msg->role === 'user' ? 'items-end' : 'items-start' }}">
              <div class="max-w-xl rounded-2xl p-4 text-xs leading-relaxed {{ $msg->role === 'user' ? 'bg-[rgb(var(--color-copper))] text-white font-medium rounded-br-none shadow-sm' : 'bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-primary))] border border-[rgb(var(--color-border))] rounded-bl-none shadow-sm' }}">
                <div class="font-bold mb-1 text-[10px] opacity-80 flex items-center gap-1">
                  @if ($msg->role === 'user')
                    <span>أنت</span>
                  @else
                    <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg> Copilot</span>
                  @endif
                </div>
                <div class="whitespace-pre-line">{{ $msg->content }}</div>
              </div>
              <span class="mt-1 text-[10px] text-[rgb(var(--color-text-secondary))] px-1">
                {{ $msg->created_at->format('H:i') }}
              </span>
            </div>
            @endforeach
          @else
            <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))] space-y-3">
              <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] shadow-inner">
                <svg class="h-8 w-8 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
              </div>
              <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">مرحباً بك في مساعد Tasker الذكي</h3>
              <p class="max-w-md mx-auto leading-relaxed">يمكنك كتابة أي استفسار حول حالة المهام والمشاريع والإنتاجية وسأقوم بتحليلها فوراً وإعطائك توصيات مباشرة.</p>
            </div>
          @endif
        </div>

        {{-- Input Prompt Form --}}
        @if ($activeConversation)
        <div class="p-4 border-t border-[rgb(var(--color-border))] space-y-3 bg-[rgb(var(--color-surface-soft)/0.15)]">
          
          {{-- Suggested Prompts --}}
          <div class="flex items-center gap-2 overflow-x-auto pb-1 text-[11px]" x-data>
            <span class="text-[rgb(var(--color-text-secondary))] shrink-0 font-semibold">مقترحات:</span>
            <button type="button" @click="$refs.promptInput.value = 'ما هي حالة المهام المتأخرة وكيف نحلها؟'" class="px-3 py-1 rounded-full bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-text-primary))] transition shrink-0 border border-[rgb(var(--color-border))] flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
              <span>المهام المتأخرة</span>
            </button>
            <button type="button" @click="$refs.promptInput.value = 'كيف ترى مؤشر صحة بيئة العمل وتوصيات الإنتاجية؟'" class="px-3 py-1 rounded-full bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-text-primary))] transition shrink-0 border border-[rgb(var(--color-border))] flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-1.5m0 0a6 6 0 10-6-6c0 2.5 1.5 4.5 3.5 5.5m2.5 2h-5m2.5 2.5h-2.5"/></svg>
              <span>تحليل الإنتاجية</span>
            </button>
            <button type="button" @click="$refs.promptInput.value = 'أعطني ملخصاً سريعا عن تقدم المشاريع والعقود النشطة.'" class="px-3 py-1 rounded-full bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-text-primary))] transition shrink-0 border border-[rgb(var(--color-border))] flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
              <span>ملخص المشاريع</span>
            </button>
          </div>

          <form method="POST" action="{{ route('ai.conversations.messages.store', $activeConversation) }}" class="flex items-center gap-3">
            @csrf
            <input x-ref="promptInput" type="text" name="message" required placeholder="اسأل المساعد الذكي عن المهام، المشاريع، أو نصائح لزيادة الإنتاجية..." class="flex-1 rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-3 text-xs text-[rgb(var(--color-text-primary))] placeholder:text-[rgb(var(--color-text-secondary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--color-copper))] shadow-sm">
            
            <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-3 px-6 font-bold shadow-md">
              إرسال
            </button>
          </form>
        </div>
        @endif

      </div>

    </section>

  </div>
</x-app-layout>
