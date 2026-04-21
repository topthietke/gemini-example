<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gemini Studio') — AI Platform</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;700;800&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:       #0a0a0f;
            --surface:  #111118;
            --card:     #16161f;
            --border:   #222230;
            --border-h: #3a3a55;
            --text:     #e8e8f0;
            --muted:    #6b6b85;
            --accent:   #7c6aff;
            --accent2:  #00d4aa;
            --accent3:  #ff6b6b;
            --glow:     rgba(124, 106, 255, 0.15);
            --radius:   12px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'IBM Plex Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── SIDEBAR NAV ── */
        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 24px 0;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 0 20px 28px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 16px;
        }

        .sidebar-logo .logo-mark {
            font-family: 'Syne', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .nav-section {
            padding: 0 12px;
        }

        .nav-label {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            color: var(--muted);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 0 8px;
            margin-bottom: 6px;
            margin-top: 16px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all .18s;
            margin-bottom: 2px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--glow);
            color: var(--text);
        }

        .nav-link.active {
            color: var(--accent);
            border: 1px solid rgba(124, 106, 255, .2);
        }

        .nav-link .icon {
            width: 18px;
            text-align: center;
            font-size: 16px;
        }

        .nav-badge {
            margin-left: auto;
            font-family: 'Space Mono', monospace;
            font-size: 9px;
            background: var(--accent);
            color: white;
            padding: 2px 6px;
            border-radius: 20px;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px 20px;
            border-top: 1px solid var(--border);
        }

        .api-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--muted);
        }

        .api-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--accent2);
            box-shadow: 0 0 6px var(--accent2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 16px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title {
            font-family: 'Syne', sans-serif;
            font-size: 16px;
            font-weight: 700;
        }

        .model-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
            color: var(--text);
            cursor: pointer;
        }

        .model-selector select {
            background: none;
            border: none;
            color: var(--text);
            font-size: 13px;
            cursor: pointer;
            outline: none;
            font-family: 'IBM Plex Sans', sans-serif;
        }

        .page-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ── FLASH MESSAGES ── */
        .flash {
            margin: 16px 28px 0;
            padding: 12px 16px;
            border-radius: var(--radius);
            font-size: 14px;
        }

        .flash-error   { background: rgba(255,107,107,.12); border: 1px solid var(--accent3); color: var(--accent3); }
        .flash-success { background: rgba(0,212,170,.1);    border: 1px solid var(--accent2);  color: var(--accent2);  }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar       { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--surface); }
        ::-webkit-scrollbar-thumb { background: var(--border-h); border-radius: 3px; }

        /* ── UTILITY ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all .18s;
            font-family: 'IBM Plex Sans', sans-serif;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #5a4ae0);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(124,106,255,.35);
        }

        .btn-secondary {
            background: var(--card);
            border: 1px solid var(--border);
            color: var(--text);
        }

        .btn-secondary:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .btn-danger {
            background: rgba(255,107,107,.12);
            border: 1px solid var(--accent3);
            color: var(--accent3);
        }

        .btn:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none !important;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .tag {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 20px;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 400;
        }

        .tag-purple { background: rgba(124,106,255,.15); color: var(--accent); border: 1px solid rgba(124,106,255,.3); }
        .tag-teal   { background: rgba(0,212,170,.1);    color: var(--accent2);  border: 1px solid rgba(0,212,170,.25); }
        .tag-red    { background: rgba(255,107,107,.1);  color: var(--accent3);  border: 1px solid rgba(255,107,107,.25); }

        @media (max-width: 768px) {
            .sidebar { width: 60px; }
            .sidebar .nav-link span,
            .sidebar-logo .logo-text,
            .sidebar .nav-label,
            .sidebar-footer { display: none; }
            .main-content { margin-left: 60px; }
        }
    </style>

    @stack('styles')
</head>
<body>
<div class="app-shell">

    <!-- SIDEBAR -->
    <nav class="sidebar">
        <div class="sidebar-logo">
            <a href="/" class="logo-mark">
                <div class="logo-icon">✦</div>
                <span class="logo-text" style="font-family:'Syne',sans-serif;">Gemini<br><small style="font-weight:400;font-size:12px;color:var(--muted);">Studio</small></span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Công cụ</div>

            <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                <span class="icon">💬</span>
                <span>Gemini Chat</span>
            </a>

            <a href="{{ route('video.index') }}" class="nav-link {{ request()->routeIs('video.*') ? 'active' : '' }}">
                <span class="icon">🎬</span>
                <span>Text → Video</span>
                <span class="nav-badge">Veo</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Tài nguyên</div>

            <a href="https://ai.google.dev/gemini-api/docs" target="_blank" class="nav-link">
                <span class="icon">📖</span>
                <span>API Docs</span>
            </a>

            <a href="https://aistudio.google.com" target="_blank" class="nav-link">
                <span class="icon">🔬</span>
                <span>AI Studio</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="api-status">
                <div class="api-dot"></div>
                <span>Gemini API</span>
            </div>
        </div>
    </nav>

    <!-- MAIN -->
    <main class="main-content">
        <div class="topbar">
            <span class="topbar-title">@yield('topbar-title', 'Gemini Studio')</span>
            <div style="display:flex;align-items:center;gap:12px;">
                @yield('topbar-actions')
                <div class="tag tag-purple">Gemini 2.0 flash</div>
            </div>
        </div>

        <div class="page-body">
            @if(session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash flash-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>
