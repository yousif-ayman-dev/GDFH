<div x-data="{
    open: false,
    inputMessage: '',
    isSending: false,
    messages: [
        { role: 'assistant', text: 'أهلاً بك! أنا بوت الذكاء الاصطناعي الخاص بك في Tasker 🤖. يمكنني إجابتك عن أي سؤال أو تحليل مشاريعك ومهامك حياً. كيف يمكنني مساعدتك اليوم؟' }
    ],
    async sendMessage() {
        if (!this.inputMessage.trim() || this.isSending) return;
        const msg = this.inputMessage.trim();
        this.messages.push({ role: 'user', text: msg });
        this.inputMessage = '';
        this.isSending = true;

        $nextTick(() => {
            if ($refs.chatScroll) $refs.chatScroll.scrollTop = $refs.chatScroll.scrollHeight;
        });

        try {
            const res = await fetch('{{ route('ai.quick-chat') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: msg })
            });

            if (res.ok) {
                const data = await res.json();
                this.messages.push({ role: 'assistant', text: data.response });
            } else {
                this.messages.push({ role: 'assistant', text: 'حدث خطأ في الاتصال بالذكاء الاصطناعي. يرجى المحاولة لاحقاً.' });
            }
        } catch(e) {
            this.messages.push({ role: 'assistant', text: 'تعذر الاتصال بالخادم حالياً.' });
        } finally {
            this.isSending = false;
            $nextTick(() => {
                if ($refs.chatScroll) $refs.chatScroll.scrollTop = $refs.chatScroll.scrollHeight;
            });
        }
    }
}" class="fixed bottom-6 end-6 z-50">

    {{-- Floating Toggle Button --}}
    <button @click="open = !open" type="button" class="group relative flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-[rgb(var(--color-copper))] to-amber-500 text-white shadow-xl hover:scale-105 active:scale-95 transition-all duration-200" title="بوت الذكاء الاصطناعي">
        <span class="absolute -top-1 -right-1 flex h-4 w-4">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white"></span>
        </span>
        <svg class="h-7 w-7 transition-transform duration-300 group-hover:rotate-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
        </svg>
    </button>

    {{-- Chat Drawer Window --}}
    <div x-cloak x-show="open" 
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        @click.outside="open = false"
        class="absolute bottom-16 end-0 w-[92vw] sm:w-[380px] h-[520px] rounded-3xl bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))] shadow-2xl flex flex-col overflow-hidden">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-3.5 bg-gradient-to-r from-[rgb(var(--color-surface))] to-[rgb(var(--color-copper-soft)/0.2)] border-b border-[rgb(var(--color-border))]">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] shadow-inner">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">Tasker AI Bot 🤖</h4>
                    <p class="text-[10px] text-[rgb(var(--color-text-secondary))] flex items-center gap-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>مساعد الذكاء الاصطناعي الحي</span>
                    </p>
                </div>
            </div>

            <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Messages Container --}}
        <div x-ref="chatScroll" class="flex-1 p-4 space-y-3 overflow-y-auto bg-[rgb(var(--color-background))] text-xs">
            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.role === 'user' ? 'flex flex-col items-end' : 'flex flex-col items-start'">
                    <div :class="m.role === 'user' ? 'bg-[rgb(var(--color-copper))] text-white rounded-2xl rounded-br-none p-3 max-w-[85%] shadow-sm' : 'bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))] border border-[rgb(var(--color-border))] rounded-2xl rounded-bl-none p-3 max-w-[88%] shadow-sm whitespace-pre-line'">
                        <span x-text="m.text"></span>
                    </div>
                </div>
            </template>

            <template x-if="isSending">
                <div class="flex items-center gap-2 text-[rgb(var(--color-text-secondary))] text-[11px] p-2">
                    <svg class="h-4 w-4 animate-spin text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>جاري التفكير والإجابة...</span>
                </div>
            </template>
        </div>

        {{-- Input Form --}}
        <div class="p-3 border-t border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))]">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                <input type="text" x-model="inputMessage" placeholder="اسأل البوت عن أي شيء..." class="flex-1 rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-background))] px-3 py-2 text-xs text-[rgb(var(--color-text-primary))] placeholder-[rgb(var(--color-text-secondary))] focus:outline-none focus:ring-1 focus:ring-[rgb(var(--color-copper))]">
                <button type="submit" :disabled="isSending" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[rgb(var(--color-copper))] text-white shadow hover:opacity-90 active:scale-95 transition">
                    <svg class="h-4 w-4 rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5m0 0l-7 7m7-7l7 7"/></svg>
                </button>
            </form>
        </div>

    </div>
</div>
