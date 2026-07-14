@php $setting = \App\Models\AiSetting::where('tenant_id', $tenant->id)->first(); @endphp
@if ($setting && $setting->assistant_enabled)
<div id="ai-assistant" x-data="aiChat()" x-cloak>
    <button class="ai-chat-toggle" @click="open = !open" :class="{ 'is-open': open }">
        <template x-if="!open">
            <i class="ti ti-message-circle-2" style="font-size:22px;"></i>
        </template>
        <template x-if="open">
            <i class="ti ti-x" style="font-size:22px;"></i>
        </template>
    </button>

    <div class="ai-chat-panel" x-show="open" x-transition:enter="ai-enter" x-transition:leave="ai-leave">
        <div class="ai-chat-header">
            <div class="ai-chat-avatar">
                <i class="ti ti-sparkles"></i>
            </div>
            <div>
                <div class="ai-chat-title">AI Assistant</div>
                <div class="ai-chat-status">Online</div>
            </div>
        </div>

        <div class="ai-chat-messages" x-ref="messages">
            <template x-for="(msg, i) in messages" :key="i">
                <div class="ai-msg" :class="msg.role">
                    <div class="ai-msg-content" x-text="msg.content"></div>
                </div>
            </template>
            <div class="ai-msg assistant" x-show="loading">
                <div class="ai-msg-content">
                    <span class="ai-dot-pulse"></span>
                </div>
            </div>
        </div>

        <form class="ai-chat-input" @submit.prevent="send">
            <input type="text" x-model="input" placeholder="Ask anything..." class="ai-input">
            <button type="submit" class="ai-send-btn" :disabled="!input.trim() || loading">
                <i class="ti ti-send"></i>
            </button>
        </form>
    </div>
</div>

<script>
function aiChat() {
    return {
        open: false,
        input: '',
        loading: false,
        messages: JSON.parse(localStorage.getItem('ai_assistant_history') || '[]'),
        init() {
            this.$watch('messages', () => {
                localStorage.setItem('ai_assistant_history', JSON.stringify(this.messages));
            });
        },
        async send() {
            const msg = this.input.trim();
            if (!msg || this.loading) return;

            this.messages.push({ role: 'user', content: msg });
            this.input = '';
            this.loading = true;

            const history = this.messages.slice(-20).map(m => ({ role: m.role, content: m.content }));

            try {
                const res = await fetch('/ai-assistant/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ message: msg, history: history.slice(0, -1) }),
                });

                const data = await res.json();

                if (data.reply) {
                    this.messages.push({ role: 'assistant', content: data.reply });
                } else if (data.error) {
                    this.messages.push({ role: 'assistant', content: 'Sorry, ' + data.error });
                }
            } catch {
                this.messages.push({ role: 'assistant', content: 'Sorry, I could not connect right now.' });
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                });
            }
        },
    };
}
</script>

<style>
#ai-assistant {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}
.ai-chat-toggle {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    border: none;
    background: var(--accent, #6366f1);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(99,102,241,0.35);
    transition: transform .2s;
}
.ai-chat-toggle:hover { transform: scale(1.08); }
.ai-chat-toggle.is-open { background: #1e293b; }
.ai-chat-panel {
    position: absolute;
    bottom: 64px;
    right: 0;
    width: 340px;
    max-height: 480px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
@keyframes ai-slide-up { from { opacity:0;transform:translateY(12px) scale(.96); } to { opacity:1;transform:translateY(0) scale(1); } }
@keyframes ai-slide-down { from { opacity:1;transform:translateY(0) scale(1); } to { opacity:0;transform:translateY(12px) scale(.96); } }
.ai-enter { animation: ai-slide-up .2s ease-out; }
.ai-leave { animation: ai-slide-down .15s ease-in; }
.ai-chat-header {
    padding: 14px 16px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.ai-chat-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: var(--accent, #6366f1);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}
.ai-chat-title { font-size: 13px; font-weight: 600; color: #0f172a; }
.ai-chat-status { font-size: 11px; color: #22c55e; }
.ai-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: 200px;
    max-height: 320px;
}
.ai-msg { display: flex; }
.ai-msg.user { justify-content: flex-end; }
.ai-msg.assistant { justify-content: flex-start; }
.ai-msg-content {
    max-width: 80%;
    padding: 8px 14px;
    border-radius: 14px;
    font-size: 13px;
    line-height: 1.5;
    word-break: break-word;
}
.ai-msg.user .ai-msg-content {
    background: var(--accent, #6366f1);
    color: #fff;
    border-bottom-right-radius: 4px;
}
.ai-msg.assistant .ai-msg-content {
    background: #f1f5f9;
    color: #0f172a;
    border-bottom-left-radius: 4px;
}
.ai-dot-pulse { display:inline-block;width:8px;height:8px;border-radius:50%;background:#94a3b8;animation:ai-pulse 1s infinite; }
@keyframes ai-pulse { 0%,100%{opacity:.4} 50%{opacity:1} }
.ai-chat-input {
    display: flex;
    gap: 8px;
    padding: 10px 12px;
    border-top: 1px solid #e2e8f0;
    background: #fff;
}
.ai-input {
    flex: 1;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    padding: 8px 14px;
    font-size: 13px;
    outline: none;
}
.ai-input:focus { border-color: var(--accent, #6366f1); }
.ai-send-btn {
    width: 36px; height: 36px;
    border-radius: 50%;
    border: none;
    background: var(--accent, #6366f1);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.ai-send-btn:disabled { opacity: .5; cursor: not-allowed; }
</style>
@endif
