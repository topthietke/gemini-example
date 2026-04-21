@extends('layouts.app')

@section('title', 'Gemini Chat')
@section('topbar-title', 'Gemini Chat')

@push('styles')
<style>
    .chat-layout {
        display: flex;
        flex: 1;
        height: calc(100vh - 57px);
        overflow: hidden;
    }

    /* ── MESSAGES AREA ── */
    .messages-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .messages-scroll {
        flex: 1;
        overflow-y: auto;
        padding: 24px 28px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* ── WELCOME SCREEN ── */
    .welcome-screen {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px;
        text-align: center;
        gap: 24px;
    }

    .welcome-orb {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: conic-gradient(from 0deg, var(--accent), var(--accent2), var(--accent));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        animation: orbit-spin 8s linear infinite;
        box-shadow: 0 0 40px var(--glow);
        position: relative;
    }

    @keyframes orbit-spin {
        to { filter: hue-rotate(360deg); }
    }

    .welcome-title {
        font-family: 'Syne', sans-serif;
        font-size: 28px;
        font-weight: 800;
        background: linear-gradient(135deg, var(--text), var(--accent));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .welcome-subtitle {
        color: var(--muted);
        font-size: 15px;
        max-width: 480px;
        line-height: 1.6;
    }

    .quick-prompts {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        max-width: 560px;
        width: 100%;
        margin-top: 8px;
    }

    .quick-prompt-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 14px 16px;
        cursor: pointer;
        text-align: left;
        transition: all .2s;
        font-family: 'IBM Plex Sans', sans-serif;
    }

    .quick-prompt-card:hover {
        border-color: var(--accent);
        background: var(--glow);
        transform: translateY(-2px);
    }

    .quick-prompt-card .qp-icon {
        font-size: 20px;
        margin-bottom: 6px;
        display: block;
    }

    .quick-prompt-card .qp-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 2px;
    }

    .quick-prompt-card .qp-desc {
        font-size: 12px;
        color: var(--muted);
        line-height: 1.4;
    }

    /* ── MESSAGES ── */
    .message {
        display: flex;
        gap: 12px;
        animation: msg-in .25s ease;
    }

    @keyframes msg-in {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .message.user-msg {
        flex-direction: row-reverse;
    }

    .avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .avatar.ai-avatar {
        background: linear-gradient(135deg, var(--accent), var(--accent2));
    }

    .avatar.user-avatar {
        background: var(--card);
        border: 1px solid var(--border);
    }

    .bubble {
        max-width: 70%;
        padding: 14px 18px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.65;
    }

    .bubble.ai-bubble {
        background: var(--card);
        border: 1px solid var(--border);
        border-top-left-radius: 4px;
    }

    .bubble.user-bubble {
        background: linear-gradient(135deg, var(--accent), #5a4ae0);
        color: white;
        border-top-right-radius: 4px;
    }

    .bubble pre {
        background: rgba(0,0,0,.4);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px;
        overflow-x: auto;
        margin: 10px 0;
        font-family: 'Space Mono', monospace;
        font-size: 12px;
    }

    .bubble code {
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        background: rgba(0,0,0,.3);
        padding: 1px 5px;
        border-radius: 3px;
    }

    .bubble p { margin-bottom: 8px; }
    .bubble p:last-child { margin-bottom: 0; }
    .bubble ul, .bubble ol { padding-left: 20px; margin: 8px 0; }
    .bubble h1, .bubble h2, .bubble h3 {
        font-family: 'Syne', sans-serif;
        margin: 12px 0 6px;
        color: var(--accent);
    }
    .bubble strong { color: var(--accent2); }

    .file-preview-bubble {
        max-width: 70%;
        margin-bottom: 6px;
        border-radius: var(--radius);
        overflow: hidden;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        background: var(--card);
    }

    .file-preview-bubble.user-side {
        margin-left: auto;
    }

    .file-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: var(--glow);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .file-meta { flex: 1; min-width: 0; }
    .file-name { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .file-size { font-size: 11px; color: var(--muted); font-family: 'Space Mono', monospace; }

    .bubble-meta {
        font-size: 11px;
        color: var(--muted);
        margin-top: 6px;
        font-family: 'Space Mono', monospace;
    }

    /* Loading dots */
    .thinking-bubble {
        display: flex;
        gap: 5px;
        align-items: center;
        padding: 16px 20px;
    }

    .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--accent);
        animation: thinking .9s infinite;
    }

    .dot:nth-child(2) { animation-delay: .2s; }
    .dot:nth-child(3) { animation-delay: .4s; }

    @keyframes thinking {
        0%, 80%, 100% { transform: scale(.6); opacity: .4; }
        40% { transform: scale(1); opacity: 1; }
    }

    /* ── INPUT AREA ── */
    .input-area {
        padding: 16px 28px 20px;
        border-top: 1px solid var(--border);
        background: var(--surface);
    }

    .file-preview-bar {
        display: none;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 10px;
        align-items: center;
        gap: 10px;
        font-size: 13px;
    }

    .file-preview-bar.visible { display: flex; }

    .input-box {
        display: flex;
        gap: 8px;
        align-items: flex-end;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 10px 10px 10px 16px;
        transition: border-color .2s;
    }

    .input-box:focus-within {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--glow);
    }

    #messageInput {
        flex: 1;
        background: none;
        border: none;
        color: var(--text);
        font-size: 14px;
        font-family: 'IBM Plex Sans', sans-serif;
        resize: none;
        outline: none;
        max-height: 180px;
        min-height: 24px;
        line-height: 1.5;
    }

    #messageInput::placeholder { color: var(--muted); }

    .input-actions {
        display: flex;
        gap: 6px;
        align-items: flex-end;
    }

    .icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--muted);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        transition: all .18s;
        flex-shrink: 0;
    }

    .icon-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    .send-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--accent), #5a4ae0);
        border: none;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        transition: all .18s;
        flex-shrink: 0;
    }

    .send-btn:hover { transform: scale(1.05); box-shadow: 0 4px 16px rgba(124,106,255,.4); }
    .send-btn:disabled { opacity: .5; transform: none; cursor: not-allowed; }

    .input-hints {
        display: flex;
        gap: 12px;
        margin-top: 10px;
        font-size: 12px;
        color: var(--muted);
    }

    .input-hint-item { display: flex; align-items: center; gap: 4px; }
    .input-hint-item kbd {
        font-family: 'Space Mono', monospace;
        font-size: 10px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 1px 5px;
        color: var(--text);
    }

    /* ── SIDEBAR PANEL (history) ── */
    .chat-sidebar {
        width: 280px;
        border-left: 1px solid var(--border);
        background: var(--surface);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }

    .chat-sidebar-header {
        padding: 16px 16px 12px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chat-sidebar-header h3 {
        font-family: 'Syne', sans-serif;
        font-size: 13px;
        font-weight: 700;
    }

    .token-counter {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px 16px;
        margin: 12px;
    }

    .token-label {
        font-size: 11px;
        color: var(--muted);
        font-family: 'Space Mono', monospace;
        margin-bottom: 4px;
    }

    .token-value {
        font-family: 'Space Mono', monospace;
        font-size: 18px;
        font-weight: 700;
        color: var(--accent);
    }

    .settings-panel {
        padding: 12px;
        border-top: 1px solid var(--border);
        margin-top: auto;
    }

    .settings-label {
        font-size: 11px;
        color: var(--muted);
        font-family: 'Space Mono', monospace;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .slider-row {
        margin-bottom: 12px;
    }

    .slider-label {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        margin-bottom: 4px;
        color: var(--text);
    }

    .slider-label span {
        font-family: 'Space Mono', monospace;
        color: var(--accent);
    }

    input[type=range] {
        width: 100%;
        accent-color: var(--accent);
        cursor: pointer;
    }

    #fileInput { display: none; }

    /* Image preview in chat */
    .bubble img {
        max-width: 100%;
        border-radius: 8px;
        margin-top: 8px;
    }
</style>
@endpush

@section('topbar-actions')
<button class="btn btn-secondary" onclick="clearChat()" style="font-size:13px;padding:6px 14px;">
    🗑 Xóa chat
</button>
@endsection

@section('content')
<div class="chat-layout">

    <!-- MESSAGES -->
    <div class="messages-area">
        <div class="messages-scroll" id="messagesScroll">

            <!-- Welcome Screen -->
            <div class="welcome-screen" id="welcomeScreen">
                <div class="welcome-orb">✦</div>
                <div>
                    <h1 class="welcome-title">Xin chào! Tôi là Gemini</h1>
                    <p class="welcome-subtitle">Trợ lý AI mạnh mẽ từ Google. Hãy gửi tin nhắn, upload file, hoặc đặt câu hỏi bất kỳ.</p>
                </div>
                <div class="quick-prompts">
                    <div class="quick-prompt-card" onclick="setPrompt('Viết một đoạn code Python để đọc file CSV và tính tổng cột số')">
                        <span class="qp-icon">💻</span>
                        <div class="qp-title">Lập trình</div>
                        <div class="qp-desc">Viết code Python xử lý file CSV</div>
                    </div>
                    <div class="quick-prompt-card" onclick="setPrompt('Giải thích cách hoạt động của Large Language Model bằng ngôn ngữ đơn giản')">
                        <span class="qp-icon">🧠</span>
                        <div class="qp-title">Giải thích AI</div>
                        <div class="qp-desc">LLM hoạt động như thế nào?</div>
                    </div>
                    <div class="quick-prompt-card" onclick="setPrompt('Dịch sang tiếng Anh và cải thiện văn phong: Chúng tôi rất vui được hợp tác cùng bạn')">
                        <span class="qp-icon">🌍</span>
                        <div class="qp-title">Dịch thuật</div>
                        <div class="qp-desc">Dịch và cải thiện văn phong</div>
                    </div>
                    <div class="quick-prompt-card" onclick="setPrompt('Phân tích SWOT cho một startup công nghệ giáo dục tại Việt Nam')">
                        <span class="qp-icon">📊</span>
                        <div class="qp-title">Phân tích</div>
                        <div class="qp-desc">SWOT cho EdTech startup</div>
                    </div>
                </div>
            </div>

            <!-- Messages will be appended here -->
        </div>

        <!-- Input Area -->
        <div class="input-area">
            <!-- File preview -->
            <div class="file-preview-bar" id="filePreviewBar">
                <div class="file-icon-box" id="filePreviewIcon">📄</div>
                <div class="file-meta">
                    <div class="file-name" id="filePreviewName">file.pdf</div>
                    <div class="file-size" id="filePreviewSize">0 KB</div>
                </div>
                <button class="icon-btn" onclick="clearFile()" title="Xóa file">✕</button>
            </div>

            <!-- Input box -->
            <div class="input-box">
                <textarea
                    id="messageInput"
                    rows="1"
                    placeholder="Gửi tin nhắn tới Gemini... (Shift+Enter để xuống dòng)"
                    onkeydown="handleKeyDown(event)"
                    oninput="autoResize(this)"
                ></textarea>

                <div class="input-actions">
                    <label for="fileInput" class="icon-btn" title="Upload file (ảnh, PDF, video, audio...)">
                        📎
                    </label>
                    <input type="file" id="fileInput" onchange="handleFileSelect(event)"
                        accept=".pdf,.txt,.docx,.jpg,.jpeg,.png,.gif,.webp,.mp4,.mov,.mp3,.wav,.ogg">

                    <button class="send-btn" id="sendBtn" onclick="sendMessage()" title="Gửi (Enter)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="input-hints">
                <div class="input-hint-item"><kbd>Enter</kbd> Gửi</div>
                <div class="input-hint-item"><kbd>Shift+Enter</kbd> Xuống dòng</div>
                <div class="input-hint-item"><kbd>📎</kbd> Upload file</div>
            </div>
        </div>
    </div>

    <!-- SIDEBAR INFO -->
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <h3>Thống kê & Cài đặt</h3>
        </div>

        <div class="token-counter">
            <div class="token-label">Tổng tokens đã dùng</div>
            <div class="token-value" id="totalTokens">0</div>
            <div class="token-label" style="margin-top:8px;">Tin nhắn trong phiên</div>
            <div class="token-value" style="font-size:14px;color:var(--accent2);" id="msgCount">0</div>
        </div>

        <div class="settings-panel">
            <div class="settings-label">Cài đặt</div>

            <div class="slider-row">
                <div class="slider-label">Temperature <span id="tempVal">0.9</span></div>
                <input type="range" min="0" max="1" step="0.1" value="0.9" id="tempSlider"
                    oninput="document.getElementById('tempVal').textContent = this.value">
            </div>

            <div style="margin-top:16px;">
                <div class="settings-label">File được hỗ trợ</div>
                <div style="font-size:12px;color:var(--muted);line-height:1.8;">
                    📷 Ảnh: JPG, PNG, GIF, WebP<br>
                    📄 Tài liệu: PDF, TXT, DOCX<br>
                    🎬 Video: MP4, MOV<br>
                    🎵 Audio: MP3, WAV, OGG
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

let conversationHistory = [];
let selectedFile = null;
let totalTokens = 0;
let msgCount = 0;
let isLoading = false;

// ── FILE HANDLING ──────────────────────────────────────
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    selectedFile = file;
    const bar = document.getElementById('filePreviewBar');
    const icons = { 'image': '🖼️', 'video': '🎬', 'audio': '🎵', 'application/pdf': '📄', 'text': '📝' };
    const iconEl = document.getElementById('filePreviewIcon');
    const key = Object.keys(icons).find(k => file.type.startsWith(k) || file.type === k) || '📎';

    iconEl.textContent = icons[key] || '📎';
    document.getElementById('filePreviewName').textContent = file.name;
    document.getElementById('filePreviewSize').textContent = formatBytes(file.size);
    bar.classList.add('visible');
}

function clearFile() {
    selectedFile = null;
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreviewBar').classList.remove('visible');
}

function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// ── MESSAGE SENDING ────────────────────────────────────
async function sendMessage() {
    if (isLoading) return;

    const input = document.getElementById('messageInput');
    const message = input.value.trim();

    if (!message && !selectedFile) return;

    // Hide welcome screen
    const welcome = document.getElementById('welcomeScreen');
    if (welcome) welcome.remove();

    isLoading = true;
    toggleSendBtn(true);
    input.value = '';
    input.style.height = 'auto';

    // Add user message to UI
    if (selectedFile) {
        addFilePreviewMessage(selectedFile, 'user');
    }
    if (message) {
        addMessage('user', message);
    }

    // Add to history
    if (message) {
        conversationHistory.push({ role: 'user', content: message });
    }

    // Add loading indicator
    const loadingId = addLoadingMessage();

    try {
        let result;

        if (selectedFile) {
            // Upload with file
            const formData = new FormData();
            formData.append('message', message || 'Hãy phân tích nội dung file này.');
            formData.append('file', selectedFile);
            formData.append('_token', CSRF_TOKEN);

            const resp = await fetch('/api/chat/upload', {
                method: 'POST',
                body: formData,
            });
            result = await resp.json();
        } else {
            // Text only
            const resp = await fetch('/api/chat/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({
                    message,
                    history: conversationHistory.slice(0, -1),
                }),
            });
            result = await resp.json();
        }

        removeLoadingMessage(loadingId);

        if (result.success) {
            addMessage('model', result.text);
            conversationHistory.push({ role: 'model', content: result.text });

            // Update stats
            if (result.usage) {
                totalTokens += result.usage.total_tokens || 0;
                document.getElementById('totalTokens').textContent = totalTokens.toLocaleString();
            }
        } else {
            addErrorMessage(result.error || 'Đã xảy ra lỗi. Vui lòng thử lại.');
        }

        clearFile();
        msgCount++;
        document.getElementById('msgCount').textContent = msgCount;

    } catch (err) {
        removeLoadingMessage(loadingId);
        addErrorMessage('Lỗi kết nối: ' + err.message);
    }

    isLoading = false;
    toggleSendBtn(false);
    input.focus();
}

// ── UI HELPERS ─────────────────────────────────────────
function addMessage(role, text) {
    const scroll = document.getElementById('messagesScroll');
    const div = document.createElement('div');
    div.className = `message ${role === 'user' ? 'user-msg' : 'ai-msg'}`;

    const avatar = role === 'user'
        ? `<div class="avatar user-avatar">👤</div>`
        : `<div class="avatar ai-avatar">✦</div>`;

    const bubbleClass = role === 'user' ? 'user-bubble' : 'ai-bubble';
    const formattedText = role === 'model' ? formatMarkdown(text) : escapeHtml(text).replace(/\n/g, '<br>');

    div.innerHTML = `
        ${avatar}
        <div class="bubble ${bubbleClass}">${formattedText}</div>
    `;

    scroll.appendChild(div);
    scroll.scrollTop = scroll.scrollHeight;
    return div;
}

function addFilePreviewMessage(file, role) {
    const scroll = document.getElementById('messagesScroll');
    const div = document.createElement('div');
    div.className = `message ${role === 'user' ? 'user-msg' : ''}`;

    const icons = {'image':'🖼️','video':'🎬','audio':'🎵'};
    const icon = Object.keys(icons).find(k => file.type.startsWith(k)) ? icons[Object.keys(icons).find(k => file.type.startsWith(k))] : '📄';

    div.innerHTML = `
        <div class="avatar user-avatar">👤</div>
        <div class="file-preview-bubble user-side">
            <div class="file-icon-box">${icon}</div>
            <div class="file-meta">
                <div class="file-name">${escapeHtml(file.name)}</div>
                <div class="file-size">${formatBytes(file.size)}</div>
            </div>
        </div>
    `;

    scroll.appendChild(div);
    scroll.scrollTop = scroll.scrollHeight;
}

function addLoadingMessage() {
    const scroll = document.getElementById('messagesScroll');
    const id = 'loading-' + Date.now();
    const div = document.createElement('div');
    div.id = id;
    div.className = 'message ai-msg';
    div.innerHTML = `
        <div class="avatar ai-avatar">✦</div>
        <div class="bubble ai-bubble">
            <div class="thinking-bubble">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>
    `;
    scroll.appendChild(div);
    scroll.scrollTop = scroll.scrollHeight;
    return id;
}

function removeLoadingMessage(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

function addErrorMessage(msg) {
    const scroll = document.getElementById('messagesScroll');
    const div = document.createElement('div');
    div.className = 'message ai-msg';
    div.innerHTML = `
        <div class="avatar ai-avatar">✦</div>
        <div class="bubble ai-bubble" style="border-color:var(--accent3);color:var(--accent3);">
            ⚠️ ${escapeHtml(msg)}
        </div>
    `;
    scroll.appendChild(div);
    scroll.scrollTop = scroll.scrollHeight;
}

function toggleSendBtn(loading) {
    const btn = document.getElementById('sendBtn');
    btn.disabled = loading;
    btn.innerHTML = loading
        ? `<div class="spinner" style="width:14px;height:14px;border-color:rgba(255,255,255,.3);border-top-color:white;"></div>`
        : `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/></svg>`;
}

function clearChat() {
    if (!confirm('Xóa toàn bộ cuộc trò chuyện?')) return;
    conversationHistory = [];
    totalTokens = 0;
    msgCount = 0;
    document.getElementById('totalTokens').textContent = '0';
    document.getElementById('msgCount').textContent = '0';

    const scroll = document.getElementById('messagesScroll');
    scroll.innerHTML = `
        <div class="welcome-screen" id="welcomeScreen">
            <div class="welcome-orb">✦</div>
            <div>
                <h1 class="welcome-title">Xin chào! Tôi là Gemini</h1>
                <p class="welcome-subtitle">Trợ lý AI mạnh mẽ từ Google. Hãy gửi tin nhắn, upload file, hoặc đặt câu hỏi bất kỳ.</p>
            </div>
            <div class="quick-prompts">
                <div class="quick-prompt-card" onclick="setPrompt('Viết một đoạn code Python để đọc file CSV và tính tổng cột số')">
                    <span class="qp-icon">💻</span>
                    <div class="qp-title">Lập trình</div>
                    <div class="qp-desc">Viết code Python xử lý file CSV</div>
                </div>
                <div class="quick-prompt-card" onclick="setPrompt('Giải thích cách hoạt động của Large Language Model bằng ngôn ngữ đơn giản')">
                    <span class="qp-icon">🧠</span>
                    <div class="qp-title">Giải thích AI</div>
                    <div class="qp-desc">LLM hoạt động như thế nào?</div>
                </div>
                <div class="quick-prompt-card" onclick="setPrompt('Dịch sang tiếng Anh và cải thiện văn phong: Chúng tôi rất vui được hợp tác cùng bạn')">
                    <span class="qp-icon">🌍</span>
                    <div class="qp-title">Dịch thuật</div>
                    <div class="qp-desc">Dịch và cải thiện văn phong</div>
                </div>
                <div class="quick-prompt-card" onclick="setPrompt('Phân tích SWOT cho một startup công nghệ giáo dục tại Việt Nam')">
                    <span class="qp-icon">📊</span>
                    <div class="qp-title">Phân tích</div>
                    <div class="qp-desc">SWOT cho EdTech startup</div>
                </div>
            </div>
        </div>
    `;
}

function setPrompt(text) {
    const input = document.getElementById('messageInput');
    input.value = text;
    input.focus();
    autoResize(input);
}

function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 180) + 'px';
}

// Simple markdown formatter
function formatMarkdown(text) {
    return text
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/```(\w*)\n([\s\S]*?)```/g, '<pre><code>$2</code></pre>')
        .replace(/`([^`]+)`/g, '<code>$1</code>')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/^### (.+)$/gm, '<h3>$1</h3>')
        .replace(/^## (.+)$/gm, '<h2>$1</h2>')
        .replace(/^# (.+)$/gm, '<h1>$1</h1>')
        .replace(/^\* (.+)$/gm, '<li>$1</li>')
        .replace(/^(\d+)\. (.+)$/gm, '<li>$2</li>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>')
        .replace(/^(?!<[hliup])(.+)/, '<p>$1</p>');
}

function escapeHtml(text) {
    return text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
@endpush
