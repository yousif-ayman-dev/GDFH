<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          مساعد الذكاء الاصطناعي للمؤسسة (Enterprise AI Assistant)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          تحليلات ذكية وتوصيات مباشرة لرفع كفاءة وسريان العمل.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <span class="gdfh-badge text-xs font-bold" style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">
          مؤشر صحة الأداء (Health Score): {{ $analysis['health_score'] }}/100
        </span>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-8">
    <div class="mx-auto max-w-7xl space-y-8">

      {{-- 1. AI Health & Workspace Analysis Dashboard --}}
      <section class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        
        {{-- Health Score Card --}}
        <div class="gdfh-card p-6 flex flex-col justify-between space-y-4 border-s-4 border-s-[rgb(var(--color-copper))]">
          <div>
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">صحة وبيئة العمل الحالية</span>
            <div class="mt-2 text-4xl font-extrabold text-[rgb(var(--color-copper))]">{{ $analysis['health_score'] }} <span class="text-sm font-normal text-[rgb(var(--color-text-secondary))]">/ 100</span></div>
          </div>

          <div class="space-y-1 text-xs text-[rgb(var(--color-text-secondary))]">
            <div>• المشاريع النشطة: {{ $analysis['total_projects'] }}</div>
            <div>• إجمالي المهام: {{ $analysis['total_tasks'] }}</div>
            <div>• المهام المتأخرة: <span class="{{ $analysis['overdue_tasks'] > 0 ? 'text-red-500 font-bold' : '' }}">{{ $analysis['overdue_tasks'] }}</span></div>
          </div>
        </div>

        {{-- Strengths & Insights --}}
        <div class="gdfh-card p-6 space-y-3">
          <h3 class="text-xs font-bold text-emerald-500 flex items-center gap-1.5">
            <span>✨ نقاط القوة والإنجازات</span>
          </h3>
          <ul class="space-y-2 text-xs text-[rgb(var(--color-text-primary))]">
            @forelse ($analysis['strengths'] as $st)
            <li class="flex items-start gap-1.5">
              <span class="text-emerald-500">✓</span>
              <span>{{ $st }}</span>
            </li>
            @empty
            <li class="text-[rgb(var(--color-text-secondary))]">لا توجد ملاحظات قوة مسجلة حتى الآن.</li>
            @endforelse
          </ul>
        </div>

        {{-- Recommendations & Suggestions --}}
        <div class="gdfh-card p-6 space-y-3">
          <h3 class="text-xs font-bold text-[rgb(var(--color-copper))] flex items-center gap-1.5">
            <span>💡 التوصيات الذكية</span>
          </h3>
          <ul class="space-y-2 text-xs text-[rgb(var(--color-text-primary))]">
            @forelse ($analysis['recommendations'] as $rec)
            <li class="flex items-start gap-1.5">
              <span class="text-[rgb(var(--color-copper))]">←</span>
              <span>{{ $rec }}</span>
            </li>
            @empty
            <li class="text-[rgb(var(--color-text-secondary))]">كل الأمور تسير وفق الخطط المعتمدة.</li>
            @endforelse
          </ul>
        </div>

        {{-- Risk Alerts & Warnings --}}
        <div class="gdfh-card p-6 space-y-3">
          <h3 class="text-xs font-bold text-red-500 flex items-center gap-1.5">
            <span>⚠️ تنبيهات المخاطر والتحذيرات</span>
          </h3>
          <ul class="space-y-2 text-xs text-[rgb(var(--color-text-primary))]">
            @forelse ($analysis['warnings'] as $warn)
            <li class="flex items-start gap-1.5">
              <span class="text-red-500">!</span>
              <span class="text-red-500 font-bold">{{ $warn }}</span>
            </li>
            @empty
            <li class="text-[rgb(var(--color-text-secondary))]">لا توجد مخاطر حرجة مكتشفة.</li>
            @endforelse
          </ul>
        </div>

      </section>

      {{-- 2. Interactive AI Chat Module --}}
      <section class="gdfh-card overflow-hidden grid grid-cols-1 md:grid-cols-4 min-h-[550px]">
        
        {{-- Conversations Sidebar --}}
        <div class="border-e border-[rgb(var(--color-border))] p-4 space-y-4 bg-[rgb(var(--color-surface-soft)/0.3)]">
          <form method="POST" action="{{ route('ai.conversations.store') }}">
            @csrf
            <button type="submit" class="w-full gdfh-btn gdfh-btn-brand text-xs py-2">
              + محادثة جديدة
            </button>
          </form>

          <div class="space-y-1 overflow-y-auto max-h-[450px]">
            <h4 class="text-[11px] font-bold text-[rgb(var(--color-text-secondary))] px-2 uppercase">المحادثات السابقة</h4>

            @foreach ($conversations as $conv)
            <div class="flex items-center justify-between group rounded-xl p-2.5 transition text-xs {{ $activeConversation && $activeConversation->id === $conv->id ? 'bg-[rgb(var(--color-surface))] font-bold text-[rgb(var(--color-copper))] shadow-sm' : 'text-[rgb(var(--color-text-primary))] hover:bg-[rgb(var(--color-surface)/0.6)]' }}">
              <a href="{{ route('ai.index', ['conversation_id' => $conv->id]) }}" class="truncate flex-1">
                💬 {{ $conv->title }}
              </a>

              <form method="POST" action="{{ route('ai.conversations.destroy', $conv) }}" onsubmit="return confirm('حذف هذه المحادثة؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition px-1">
                  ✕
                </button>
              </form>
            </div>
            @endforeach
          </div>
        </div>

        {{-- Active Chat Interface --}}
        <div class="md:col-span-3 flex flex-col justify-between bg-[rgb(var(--color-surface))]">
          
          {{-- Chat Header --}}
          <div class="border-b border-[rgb(var(--color-border))] p-4 flex items-center justify-between">
            <div>
              <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                {{ $activeConversation?->title ?? 'مساعد المؤسسة الذكي' }}
              </h3>
              <p class="text-[10px] text-[rgb(var(--color-text-secondary))]">محرك استدلال وقواعد النظام الداخلي</p>
            </div>
          </div>

          {{-- Messages History Container --}}
          <div class="p-6 space-y-4 overflow-y-auto max-h-[420px] flex-1">
            @if ($activeConversation && $activeConversation->messages->count() > 0)
              @foreach ($activeConversation->messages as $msg)
              <div class="flex flex-col {{ $msg->role === 'user' ? 'items-end' : 'items-start' }}">
                <div class="max-w-xl rounded-2xl p-4 text-xs leading-relaxed {{ $msg->role === 'user' ? 'bg-[rgb(var(--color-copper))] text-white rounded-br-none' : 'bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-primary))] border border-[rgb(var(--color-border))] rounded-bl-none' }}">
                  <div class="font-bold mb-1 text-[10px] opacity-80">
                    {{ $msg->role === 'user' ? 'أنت' : 'المساعد الذكي' }}
                  </div>
                  <div class="whitespace-pre-line">{{ $msg->content }}</div>
                </div>
                <span class="mt-1 text-[10px] text-[rgb(var(--color-text-secondary))] px-1">
                  {{ $msg->created_at->format('H:i') }}
                </span>
              </div>
              @endforeach
            @else
              <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))] space-y-2">
                <div class="text-2xl">🤖</div>
                <p>مرحباً بك! يمكنك كتابة أي استفسار حول حالة المهام والمشاريع والإنتاجية وسأقوم بتحليلها فوراً.</p>
              </div>
            @endif
          </div>

          {{-- Input Prompt Form --}}
          @if ($activeConversation)
          <form method="POST" action="{{ route('ai.conversations.messages.store', $activeConversation) }}" class="p-4 border-t border-[rgb(var(--color-border))] flex items-center gap-3">
            @csrf
            <input type="text" name="message" required placeholder="اسأل المساعد الذكي عن المهام، المشاريع، أو نصائح لزيادة الإنتاجية..." class="flex-1 rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft))] p-3 text-xs text-[rgb(var(--color-text-primary))] placeholder:text-[rgb(var(--color-text-secondary))] focus:outline-none focus:ring-1 focus:ring-[rgb(var(--color-copper))]">
            
            <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-3 px-5 font-bold">
              إرسال
            </button>
          </form>
          @endif

        </div>

      </section>

    </div>
  </div>
</x-app-layout>
