@extends('layouts.app')

@section('content')
<div class="app-shell">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon">✦</span>
            <span class="brand-name">GeminiChat</span>
        </div>

        <div class="sidebar-section">
            <p class="sidebar-label">Model</p>
            <select id="modelSelect" class="model-select">
                @if(count($models))
                    @foreach($models as $model)
                        <option value="{{ $model['id'] }}" {{ $model['id'] === config('gemini.model') ? 'selected' : '' }}>
                            {{ $model['displayName'] }}
                        </option>
                    @endforeach
                @else
                    <option value="{{ config('gemini.model') }}">{{ config('gemini.model') }}</option>
                @endif
            </select>
        </div>

        <div class="sidebar-section">
            <p class="sidebar-label">Nhiệt độ (Creativity)</p>
            <div class="slider-wrap">
                <input type="range" id="tempSlider" min="0" max="1" step="0.1" value="0.7" class="slider">
                <span id="tempValue" class="slider-val">0.7</span>
            </div>
        </div>

        <div class="sidebar-section">
            <p class="sidebar-label">System Prompt</p>
            <textarea id="systemPrompt" class="sys-prompt" placeholder="Bạn là trợ lý AI thông minh..."></textarea>
        </div>

        <div class="sidebar-actions">
            <button id="testConn" class="btn-test">
                <span class="btn-icon">⚡</span> Kiểm tra kết nối
            </button>
            <button id="clearChat" class="btn-clear">
                <span class="btn-icon">↺</span> Xóa hội thoại
            </button>
        </div>

        <div class="sidebar-footer">
            <div id="connStatus" class="conn-status idle">
                <span class="status-dot"></span>
                <span class="status-text">Chưa kiểm tra</span>
            </div>
        </div>
    </aside>

    {{-- MAIN CHAT --}}
    <main class="chat-main">

        <header class="chat-header">
            <button class="menu-toggle" id="menuToggle">☰</button>
            <div class="header-info">
                <h1>Gemini AI Chat</h1>
                <span class="header-sub">Powered by Google Gemini API</span>
            </div>
            <div id="tokenMeta" class="token-meta"></div>
        </header>

        <div class="messages-wrap" id="messagesWrap">
            <div class="welcome-state" id="welcomeState">
                <div class="welcome-icon">✦</div>
                <h2>Xin chào!</h2>
                <p>Bắt đầu cuộc trò chuyện với Gemini AI.<br>Nhập tin nhắn bên dưới để bắt đầu.</p>
                <div class="example-prompts">
                    <button class="example-btn" data-prompt="Giải thích machine learning cho người mới bắt đầu">💡 Giải thích Machine Learning</button>
                    <button class="example-btn" data-prompt="Viết một đoạn code Python đọc file CSV và tính tổng một cột">🐍 Code Python đọc CSV</button>
                    <button class="example-btn" data-prompt="Kể cho tôi nghe một câu chuyện ngắn thú vị">📖 Kể chuyện ngắn</button>
                    <button class="example-btn" data-prompt="So sánh ưu và nhược điểm của Vue.js và React">⚛️ Vue.js vs React</button>
                </div>
            </div>
            <div id="messagesList"></div>
        </div>

        <div class="input-area">
            <div id="filePreviews" class="file-previews"></div>
            <div class="input-wrap">
                <input type="file" id="fileInput" accept="image/*,video/*" multiple hidden>
                <button id="uploadBtn" class="upload-btn" title="Tải lên tệp">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                    </svg>
                </button>
                <textarea
                    id="promptInput"
                    class="prompt-input"
                    placeholder="Nhập tin nhắn... (Enter để gửi, Shift+Enter xuống dòng)"
                    rows="1"
                ></textarea>
                <button id="sendBtn" class="send-btn" title="Gửi">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 2L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <p class="input-hint">Gemini có thể mắc lỗi. Hãy kiểm tra lại thông tin quan trọng.</p>
        </div>

    </main>
</div>
@endsection

@push('scripts')
<script>
    window.CHAT_CONFIG = {
        sendUrl: "{{ route('chat.send') }}",
        testUrl:  "{{ route('chat.test') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
@endpush
