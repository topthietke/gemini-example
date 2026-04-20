/* ============================================
   GEMINI CHAT — Frontend Logic
   ============================================ */

(function () {
    "use strict";

    /* ---- State ---- */
    const state = {
        history: [], // [{role, content}]
        loading: false,
    };

    /* ---- Elements ---- */
    const el = {
        sendBtn: () => document.getElementById("sendBtn"),
        promptInput: () => document.getElementById("promptInput"),
        messagesList: () => document.getElementById("messagesList"),
        messagesWrap: () => document.getElementById("messagesWrap"),
        welcomeState: () => document.getElementById("welcomeState"),
        tempSlider: () => document.getElementById("tempSlider"),
        tempValue: () => document.getElementById("tempValue"),
        systemPrompt: () => document.getElementById("systemPrompt"),
        testConn: () => document.getElementById("testConn"),
        clearChat: () => document.getElementById("clearChat"),
        connStatus: () => document.getElementById("connStatus"),
        tokenMeta: () => document.getElementById("tokenMeta"),
        menuToggle: () => document.getElementById("menuToggle"),
        sidebar: () => document.querySelector(".sidebar"),
    };

    /* ---- Markdown-lite renderer ---- */
    function renderMarkdown(text) {
        return text
            .replace(
                /```(\w*)\n([\s\S]*?)```/g,
                (_, lang, code) =>
                    `<pre><code class="lang-${lang}">${escapeHtml(code.trim())}</code></pre>`,
            )
            .replace(/`([^`]+)`/g, (_, c) => `<code>${escapeHtml(c)}</code>`)
            .replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>")
            .replace(/\*(.+?)\*/g, "<em>$1</em>")
            .replace(/^### (.+)$/gm, "<h3>$1</h3>")
            .replace(/^## (.+)$/gm, "<h2>$1</h2>")
            .replace(/^# (.+)$/gm, "<h1>$1</h1>")
            .replace(/^\* (.+)$/gm, "<li>$1</li>")
            .replace(/(<li>.*<\/li>)/s, "<ul>$1</ul>")
            .replace(/\n\n/g, "</p><p>")
            .replace(/^(.+)$/gm, (m) => (m.startsWith("<") ? m : m))
            .replace(/\n/g, "<br>");
    }

    function escapeHtml(s) {
        return s
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");
    }

    /* ---- Append message ---- */
    function appendMessage(role, content) {
        const welcome = el.welcomeState();
        if (welcome) welcome.style.display = "none";

        const isUser = role === "user";
        const div = document.createElement("div");
        div.className = `msg ${isUser ? "user" : "ai"}`;
        div.innerHTML = `
      <div class="msg-avatar">${isUser ? "👤" : "✦"}</div>
      <div class="msg-body">
        <div class="msg-role">${isUser ? "Bạn" : "Gemini"}</div>
        <div class="msg-text">${isUser ? escapeHtml(content).replace(/\n/g, "<br>") : renderMarkdown(content)}</div>
      </div>`;
        el.messagesList().appendChild(div);
        scrollBottom();
        return div;
    }

    /* ---- Typing indicator ---- */
    function showTyping() {
        const div = document.createElement("div");
        div.className = "msg ai";
        div.id = "typingMsg";
        div.innerHTML = `
      <div class="msg-avatar">✦</div>
      <div class="msg-body">
        <div class="msg-role">Gemini</div>
        <div class="typing-indicator">
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
        </div>
      </div>`;
        el.messagesList().appendChild(div);
        scrollBottom();
    }

    function hideTyping() {
        const t = document.getElementById("typingMsg");
        if (t) t.remove();
    }

    function scrollBottom() {
        const wrap = el.messagesWrap();
        wrap.scrollTop = wrap.scrollHeight;
    }

    /* ---- Toast error ---- */
    function showError(msg) {
        const toast = document.createElement("div");
        toast.className = "error-toast";
        toast.textContent = "⚠ " + msg;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    /* ---- Send message ---- */
    async function sendMessage() {
        
        const prompt = el.promptInput().value.trim();
        if (!prompt || state.loading) return;

        state.loading = true;
        el.sendBtn().disabled = true;
        el.promptInput().value = "";
        el.promptInput().style.height = "auto";

        // Append user message
        appendMessage("user", prompt);
        showTyping();

        // Build history to send
        const historyToSend = state.history.slice(-20); // last 20 turns
        console.log(1111, window.CHAT_CONFIG.sendUrl);        
        try {
            const resp = await fetch(window.CHAT_CONFIG.sendUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": window.CHAT_CONFIG.csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    prompt,
                    history: historyToSend,
                }),
            });

            const data = await resp.json();

            hideTyping();

            if (!resp.ok || data.error) {
                showError(data.error || "Lỗi không xác định");
                return;
            }

            // Store in history
            state.history.push({ role: "user", content: prompt });
            state.history.push({ role: "model", content: data.text });

            // Append AI reply
            appendMessage("model", data.text);

            // Update token meta
            if (data.meta) {
                el.tokenMeta().innerHTML = `Tokens: <strong>${data.meta.input_tokens}</strong> in / <strong>${data.meta.output_tokens}</strong> out<br>
           Model: <strong>${data.meta.model}</strong>`;
            }
        } catch (err) {
            hideTyping();
            showError("Lỗi mạng: " + err.message);
        } finally {
            state.loading = false;
            el.sendBtn().disabled = false;
            el.promptInput().focus();
        }
    }

    /* ---- Test connection ---- */
    async function testConnection() {
        const status = el.connStatus();
        status.className = "conn-status testing";
        status.querySelector(".status-text").textContent = "Đang kiểm tra...";        
        try {
            const resp = await fetch(window.CHAT_CONFIG.testUrl, {
                headers: { Accept: "application/json" },
            });
                       
            const data = await resp.json();
            console.log(data);
            
            if (resp.ok && data.success) {
                status.className = "conn-status success";
                status.querySelector(".status-text").textContent =
                    `OK — ${data.models} models`;
            } else {
                throw new Error(data.error || "Failed");
            }
        } catch (err) {
            status.className = "conn-status error";
            status.querySelector(".status-text").textContent = "Lỗi kết nối";
            showError(err.message);
        }
    }

    /* ---- Auto-resize textarea ---- */
    function autoResize(ta) {
        ta.style.height = "auto";
        ta.style.height = Math.min(ta.scrollHeight, 160) + "px";
    }

    /* ---- Init ---- */
    function init() {
        // Send button
        el.sendBtn().addEventListener("click", sendMessage);

        // Enter to send
        el.promptInput().addEventListener("keydown", (e) => {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Auto-resize
        el.promptInput().addEventListener("input", (e) => autoResize(e.target));

        // Temp slider
        el.tempSlider().addEventListener("input", (e) => {
            el.tempValue().textContent = e.target.value;
        });

        // Test connection
        el.testConn().addEventListener("click", testConnection);

        // Clear chat
        el.clearChat().addEventListener("click", () => {
            state.history = [];
            el.messagesList().innerHTML = "";
            const welcome = el.welcomeState();
            if (welcome) welcome.style.display = "";
            el.tokenMeta().innerHTML = "";
        });

        // Example prompts
        document.querySelectorAll(".example-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                el.promptInput().value = btn.dataset.prompt;
                sendMessage();
            });
        });

        // Mobile sidebar toggle
        el.menuToggle().addEventListener("click", () => {
            el.sidebar().classList.toggle("open");
        });

        // Close sidebar on outside click (mobile)
        document.addEventListener("click", (e) => {
            if (
                window.innerWidth <= 768 &&
                !el.sidebar().contains(e.target) &&
                !el.menuToggle().contains(e.target)
            ) {
                el.sidebar().classList.remove("open");
            }
        });

        el.promptInput().focus();
    }

    document.addEventListener("DOMContentLoaded", init);
})();
