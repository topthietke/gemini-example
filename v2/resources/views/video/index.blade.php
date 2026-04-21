@extends('layouts.app')

@section('title', 'Text to Video')
@section('topbar-title', 'Text → Video (Google Veo)')

@push('styles')
<style>
    .video-layout {
        display: flex;
        flex: 1;
        gap: 0;
        height: calc(100vh - 57px);
        overflow: hidden;
    }

    /* ── FORM PANEL ── */
    .form-panel {
        width: 420px;
        flex-shrink: 0;
        border-right: 1px solid var(--border);
        overflow-y: auto;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .panel-section {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
    }

    .panel-section-header {
        padding: 12px 16px;
        background: rgba(255,255,255,.02);
        border-bottom: 1px solid var(--border);
        font-family: 'Syne', sans-serif;
        font-size: 12px;
        font-weight: 700;
        color: var(--muted);
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .panel-section-body { padding: 16px; }

    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--text);
        margin-bottom: 6px;
    }

    .form-label small {
        font-weight: 400;
        color: var(--muted);
        font-size: 11px;
        font-family: 'Space Mono', monospace;
    }

    .form-control {
        width: 100%;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: 13px;
        padding: 9px 12px;
        outline: none;
        transition: border-color .2s;
    }

    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--glow);
    }

    textarea.form-control { resize: vertical; min-height: 100px; line-height: 1.6; }
    select.form-control { cursor: pointer; }

    /* Style Cards */
    .style-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .style-card {
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        transition: all .18s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .style-card:hover { border-color: var(--accent); }

    .style-card.active {
        border-color: var(--accent);
        background: var(--glow);
    }

    .style-card input[type=radio] { display: none; }
    .style-card .style-icon { font-size: 18px; }
    .style-card .style-info .style-name { font-size: 12px; font-weight: 600; }
    .style-card .style-info .style-desc { font-size: 11px; color: var(--muted); }

    /* Aspect ratio */
    .ratio-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }

    .ratio-card {
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px 6px;
        cursor: pointer;
        transition: all .18s;
        text-align: center;
    }

    .ratio-card:hover { border-color: var(--accent); }
    .ratio-card.active { border-color: var(--accent); background: var(--glow); }
    .ratio-card input[type=radio] { display: none; }
    .ratio-card .ratio-preview {
        background: var(--border);
        border-radius: 4px;
        margin: 0 auto 6px;
    }
    .ratio-card .ratio-label { font-size: 11px; color: var(--muted); }
    .ratio-card.active .ratio-label { color: var(--accent); }

    /* ── RESULT PANEL ── */
    .result-panel {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .empty-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 40px;
        gap: 16px;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--card);
        border: 2px dashed var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
    }

    .empty-title {
        font-family: 'Syne', sans-serif;
        font-size: 20px;
        font-weight: 700;
    }

    .empty-subtitle {
        color: var(--muted);
        font-size: 14px;
        max-width: 360px;
        line-height: 1.6;
    }

    /* Progress */
    .progress-card {
        background: var(--card);
        border: 1px solid var(--accent);
        border-radius: var(--radius);
        padding: 24px;
        text-align: center;
        box-shadow: 0 0 30px var(--glow);
    }

    .progress-animation {
        width: 80px;
        height: 80px;
        margin: 0 auto 16px;
        position: relative;
    }

    .progress-ring {
        animation: spin 1.5s linear infinite;
    }

    .progress-label {
        font-family: 'Syne', sans-serif;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .progress-status {
        color: var(--muted);
        font-size: 13px;
        font-family: 'Space Mono', monospace;
    }

    .progress-bar-track {
        height: 4px;
        background: var(--border);
        border-radius: 2px;
        margin-top: 16px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--accent), var(--accent2));
        border-radius: 2px;
        animation: progress-anim 2s ease-in-out infinite alternate;
    }

    @keyframes progress-anim {
        from { width: 20%; }
        to   { width: 85%; }
    }

    /* Result */
    .result-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
    }

    .result-card-header {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .result-card-title {
        font-family: 'Syne', sans-serif;
        font-size: 14px;
        font-weight: 700;
    }

    .result-card-body { padding: 16px; }

    video {
        width: 100%;
        border-radius: 8px;
        background: #000;
    }

    .script-content {
        font-size: 13px;
        line-height: 1.8;
        color: var(--text);
        white-space: pre-wrap;
        font-family: 'IBM Plex Sans', sans-serif;
    }

    .script-content h1, .script-content h2, .script-content h3 {
        font-family: 'Syne', sans-serif;
        color: var(--accent);
        margin: 16px 0 8px;
    }

    .script-content strong { color: var(--accent2); }
    .script-content code {
        font-family: 'Space Mono', monospace;
        background: var(--surface);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
    }

    .veo-notice {
        background: linear-gradient(135deg, rgba(124,106,255,.08), rgba(0,212,170,.06));
        border: 1px solid rgba(124,106,255,.25);
        border-radius: var(--radius);
        padding: 16px;
        font-size: 13px;
        line-height: 1.7;
    }

    .veo-notice a { color: var(--accent); }

    .generate-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, var(--accent), #5a4ae0);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        font-family: 'Syne', sans-serif;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .generate-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(124,106,255,.4);
    }

    .generate-btn:disabled {
        opacity: .6;
        cursor: not-allowed;
        transform: none;
    }
</style>
@endpush

@section('content')
<div class="video-layout">

    <!-- FORM PANEL -->
    <div class="form-panel">

        <!-- Prompt -->
        <div class="panel-section">
            <div class="panel-section-header">📝 Mô tả video</div>
            <div class="panel-section-body">
                <div class="form-group">
                    <label class="form-label">Prompt <small>* Bắt buộc</small></label>
                    <textarea class="form-control" id="promptInput" rows="4"
                        placeholder="Mô tả chi tiết video bạn muốn tạo...

Ví dụ: Một chú mèo cam đang ngồi trên mái nhà lúc hoàng hôn, nhìn xuống thành phố đông đúc bên dưới. Ánh nắng chiều tà phản chiếu trên lông mèo tạo nên những đường viền vàng ấm áp..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tâm trạng / Mood</label>
                    <input type="text" class="form-control" id="moodInput"
                        placeholder="VD: dreamy, epic, peaceful, mysterious...">
                </div>

                <div class="form-group">
                    <label class="form-label">Góc máy / Camera</label>
                    <select class="form-control" id="cameraInput">
                        <option value="medium shot">Medium shot (Trung cảnh)</option>
                        <option value="close-up">Close-up (Cận cảnh)</option>
                        <option value="wide shot">Wide shot (Toàn cảnh)</option>
                        <option value="aerial drone shot">Aerial / Drone shot</option>
                        <option value="tracking shot">Tracking shot (Theo dõi)</option>
                        <option value="slow motion">Slow motion (Quay chậm)</option>
                        <option value="time-lapse">Time-lapse</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Style -->
        <div class="panel-section">
            <div class="panel-section-header">🎨 Phong cách</div>
            <div class="panel-section-body">
                <div class="style-grid" id="styleGrid">
                    @php
                    $styles = [
                        ['cinematic',   '🎬', 'Cinematic',   '4K, điện ảnh'],
                        ['anime',       '✨', 'Anime',       'Hoạt hình Nhật'],
                        ['realistic',   '📸', 'Realistic',   'Siêu thực tế'],
                        ['cartoon',     '🎠', 'Cartoon',     'Hoạt hình vui'],
                        ['documentary', '📹', 'Documentary', 'Phóng sự'],
                        ['artistic',    '🖼️',  'Artistic',    'Nghệ thuật'],
                    ];
                    @endphp

                    @foreach($styles as $i => [$val, $icon, $name, $desc])
                    <label class="style-card {{ $i === 0 ? 'active' : '' }}" onclick="selectStyle(this)">
                        <input type="radio" name="style" value="{{ $val }}" {{ $i === 0 ? 'checked' : '' }}>
                        <span class="style-icon">{{ $icon }}</span>
                        <div class="style-info">
                            <div class="style-name">{{ $name }}</div>
                            <div class="style-desc">{{ $desc }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="panel-section">
            <div class="panel-section-header">⚙️ Cấu hình</div>
            <div class="panel-section-body">

                <!-- Aspect Ratio -->
                <div class="form-group">
                    <label class="form-label">Tỉ lệ khung hình</label>
                    <div class="ratio-grid" id="ratioGrid">
                        @php
                        $ratios = [
                            ['16:9', 56, 32],
                            ['9:16', 22, 40],
                            ['1:1',  32, 32],
                            ['4:3',  40, 30],
                        ];
                        @endphp
                        @foreach($ratios as $i => [$ratio, $w, $h])
                        <label class="ratio-card {{ $i === 0 ? 'active' : '' }}" onclick="selectRatio(this)">
                            <input type="radio" name="aspect_ratio" value="{{ $ratio }}" {{ $i === 0 ? 'checked' : '' }}>
                            <div class="ratio-preview" style="width:{{$w}}px;height:{{$h}}px;"></div>
                            <div class="ratio-label">{{ $ratio }}</div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Duration -->
                <div class="form-group">
                    <label class="form-label">Thời lượng: <span id="durationLabel" style="color:var(--accent);font-family:'Space Mono',monospace;">8 giây</span></label>
                    <input type="range" min="4" max="30" step="2" value="8" id="durationSlider"
                        oninput="document.getElementById('durationLabel').textContent = this.value + ' giây'"
                        style="width:100%;accent-color:var(--accent);">
                    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted);margin-top:4px;">
                        <span>4s</span><span>30s</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generate Button -->
        <button class="generate-btn" id="generateBtn" onclick="generateVideo()">
            🎬 Tạo Video
        </button>

        <!-- Veo Notice -->
        <div class="veo-notice">
            <strong style="color:var(--accent);">ℹ️ Thông tin về Google Veo API</strong><br>
            Tính năng này sử dụng <strong>Google Veo 2</strong> — mô hình tạo video AI tiên tiến nhất của Google.<br><br>
            Nếu API key chưa có quyền truy cập Veo, hệ thống sẽ tự động tạo <strong>storyboard chi tiết</strong> thay thế bằng Gemini.<br><br>
            <a href="https://deepmind.google/technologies/veo/" target="_blank">→ Đăng ký Veo API Allowlist</a>
        </div>

    </div>

    <!-- RESULT PANEL -->
    <div class="result-panel" id="resultPanel">
        <div class="empty-state" id="emptyState">
            <div class="empty-icon">🎬</div>
            <div class="empty-title">Sẵn sàng tạo video</div>
            <div class="empty-subtitle">
                Điền prompt mô tả video bạn muốn tạo, chọn phong cách và nhấn <strong>"Tạo Video"</strong>.
                <br><br>
                Google Veo 2 sẽ tạo ra video chất lượng cao từ văn bản của bạn.
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
let pollingInterval = null;

function selectStyle(label) {
    document.querySelectorAll('.style-card').forEach(el => el.classList.remove('active'));
    label.classList.add('active');
}

function selectRatio(label) {
    document.querySelectorAll('.ratio-card').forEach(el => el.classList.remove('active'));
    label.classList.add('active');
}

async function generateVideo() {
    const prompt = document.getElementById('promptInput').value.trim();
    if (!prompt || prompt.length < 10) {
        showNotification('Vui lòng nhập mô tả video ít nhất 10 ký tự!', 'error');
        return;
    }

    const style = document.querySelector('input[name="style"]:checked')?.value || 'cinematic';
    const ratio = document.querySelector('input[name="aspect_ratio"]:checked')?.value || '16:9';
    const duration = document.getElementById('durationSlider').value;
    const mood = document.getElementById('moodInput').value.trim();
    const camera = document.getElementById('cameraInput').value;

    setGenerating(true);
    showProgress();

    try {
        const resp = await fetch('/api/video/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({ prompt, style, aspect_ratio: ratio, duration: parseInt(duration), mood, camera }),
        });

        const data = await resp.json();

        if (!data.success) {
            showError(data.errors || data.error || 'Đã xảy ra lỗi.');
            return;
        }

        if (data.fallback) {
            // Gemini script fallback
            showScriptResult(data, prompt);
        } else if (data.operation_name) {
            // Poll for Veo result
            pollVideoStatus(data.operation_name, prompt);
        }

    } catch (err) {
        showError('Lỗi kết nối: ' + err.message);
    }
}

function pollVideoStatus(operationName, prompt) {
    let attempts = 0;
    const maxAttempts = 60;

    pollingInterval = setInterval(async () => {
        attempts++;
        updateProgressStatus(`Đang xử lý... (${attempts * 5}s / ~${maxAttempts * 5}s tối đa)`);

        if (attempts >= maxAttempts) {
            clearInterval(pollingInterval);
            showError('Hết thời gian chờ. Veo đang bận, vui lòng thử lại.');
            return;
        }

        try {
            const resp = await fetch(`/api/video/status/${encodeURIComponent(operationName)}`);
            const data = await resp.json();

            if (data.done && data.video_uri) {
                clearInterval(pollingInterval);
                showVideoResult(data.video_uri, prompt);
            }
        } catch (err) {
            console.error('Polling error:', err);
        }
    }, 5000);
}

// ── UI ─────────────────────────────────────
function showProgress() {
    const panel = document.getElementById('resultPanel');
    panel.innerHTML = `
        <div class="progress-card">
            <div class="progress-animation">
                <svg class="progress-ring" width="80" height="80" viewBox="0 0 80 80">
                    <circle cx="40" cy="40" r="34" fill="none" stroke="var(--border)" stroke-width="4"/>
                    <circle cx="40" cy="40" r="34" fill="none" stroke="url(#grad)" stroke-width="4"
                        stroke-dasharray="130 80" stroke-linecap="round"/>
                    <defs>
                        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="var(--accent)"/>
                            <stop offset="100%" stop-color="var(--accent2)"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="progress-label">🎬 Đang tạo video...</div>
            <div class="progress-status" id="progressStatus">Gửi yêu cầu tới Google Veo...</div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill"></div>
            </div>
        </div>
    `;
}

function updateProgressStatus(msg) {
    const el = document.getElementById('progressStatus');
    if (el) el.textContent = msg;
}

function showVideoResult(videoUrl, prompt) {
    setGenerating(false);
    const panel = document.getElementById('resultPanel');
    panel.innerHTML = `
        <div class="result-card">
            <div class="result-card-header">
                <span class="result-card-title">✅ Video đã được tạo</span>
                <span class="tag tag-teal">Veo 2</span>
            </div>
            <div class="result-card-body">
                <video controls autoplay loop>
                    <source src="${escapeHtml(videoUrl)}" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
                <div style="margin-top:12px;display:flex;gap:8px;">
                    <a href="${escapeHtml(videoUrl)}" download class="btn btn-primary" style="font-size:13px;">
                        ⬇️ Tải về
                    </a>
                    <button class="btn btn-secondary" onclick="generateVideo()" style="font-size:13px;">
                        🔄 Tạo lại
                    </button>
                </div>
            </div>
        </div>
        <div class="result-card" style="border-color:var(--border);">
            <div class="result-card-header">
                <span class="result-card-title">📝 Prompt đã dùng</span>
            </div>
            <div class="result-card-body">
                <p style="font-size:13px;color:var(--muted);line-height:1.7;">${escapeHtml(prompt)}</p>
            </div>
        </div>
    `;
}

function showScriptResult(data, prompt) {
    setGenerating(false);
    const panel = document.getElementById('resultPanel');
    panel.innerHTML = `
        <div style="background:rgba(255,107,107,.08);border:1px solid rgba(255,107,107,.25);border-radius:var(--radius);padding:14px 16px;font-size:13px;line-height:1.7;">
            ⚠️ <strong>Veo API chưa khả dụng</strong> — ${escapeHtml(data.message || '')}<br>
            <a href="https://deepmind.google/technologies/veo/" target="_blank" style="color:var(--accent);">→ Đăng ký Veo API Allowlist</a>
        </div>

        <div class="result-card">
            <div class="result-card-header">
                <span class="result-card-title">🎬 Storyboard / Script</span>
                <span class="tag tag-purple">Gemini AI</span>
            </div>
            <div class="result-card-body">
                <div class="script-content">${formatMarkdown(data.script || '')}</div>
            </div>
        </div>

        <div class="result-card" style="border-color:var(--border);">
            <div class="result-card-header">
                <span class="result-card-title">📝 Prompt gốc</span>
            </div>
            <div class="result-card-body">
                <p style="font-size:13px;color:var(--muted);line-height:1.7;">${escapeHtml(prompt)}</p>
            </div>
        </div>

        <button class="btn btn-primary" onclick="generateVideo()" style="width:100%;justify-content:center;padding:12px;">
            🔄 Thử lại
        </button>
    `;
}

function showError(msg) {
    setGenerating(false);
    const panel = document.getElementById('resultPanel');
    panel.innerHTML = `
        <div style="background:rgba(255,107,107,.1);border:1px solid var(--accent3);border-radius:var(--radius);padding:20px;text-align:center;">
            <div style="font-size:32px;margin-bottom:12px;">⚠️</div>
            <div style="font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:8px;color:var(--accent3);">Đã xảy ra lỗi</div>
            <div style="font-size:13px;color:var(--muted);margin-bottom:16px;">${typeof msg === 'object' ? JSON.stringify(msg) : escapeHtml(String(msg))}</div>
            <button class="btn btn-secondary" onclick="generateVideo()">🔄 Thử lại</button>
        </div>
    `;
}

function setGenerating(loading) {
    const btn = document.getElementById('generateBtn');
    btn.disabled = loading;
    btn.innerHTML = loading
        ? `<div class="spinner"></div> Đang tạo video...`
        : `🎬 Tạo Video`;

    if (!loading && pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

function showNotification(msg, type = 'info') {
    const el = document.createElement('div');
    el.style.cssText = `
        position:fixed;top:20px;right:20px;z-index:9999;
        background:var(--card);border:1px solid ${type === 'error' ? 'var(--accent3)' : 'var(--accent)'};
        border-radius:10px;padding:12px 20px;font-size:14px;
        color:${type === 'error' ? 'var(--accent3)' : 'var(--text)'};
        box-shadow:0 8px 32px rgba(0,0,0,.4);
        animation:msg-in .25s ease;
    `;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

function formatMarkdown(text) {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/^### (.+)$/gm, '<h3>$1</h3>')
        .replace(/^## (.+)$/gm, '<h2>$1</h2>')
        .replace(/^# (.+)$/gm, '<h1>$1</h1>')
        .replace(/`([^`]+)`/g, '<code>$1</code>')
        .replace(/\n/g, '<br>');
}

function escapeHtml(text) {
    return String(text).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
