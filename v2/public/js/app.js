/* ============================================
   GEMINI CHAT — Frontend Logic
   ============================================ */

(function () {
    "use strict";

    /* ---- State ---- */
    const state = {
        history: [], // [{role, content}]
        uploadedFiles: [], // [{name, type, base64, id}]
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
        uploadBtn: () => document.getElementById("uploadBtn"),
        fileInput: () => document.getElementById("fileInput"),
        filePreviews: () => document.getElementById("filePreviews"),
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
        const files = isUser ? state.uploadedFiles : [];
        const div = document.createElement("div");
        div.className = `msg ${isUser ? "user" : "ai"}`;

        let filesHtml = '';
        if (files.length > 0) {
            filesHtml += '<div class="msg-files">';
            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    filesHtml += `<img src="data:${file.type};base64,${file.base64}" alt="${file.name}" class="msg-file-preview">`;
                }
            });
            filesHtml += '</div>';
        }

        div.innerHTML = `
      <div class="msg-avatar">${isUser ? "👤" : "✦"}</div>
      <div class="msg-body">
        <div class="msg-role">${isUser ? "Bạn" : "Gemini"}</div>
        ${filesHtml}
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

    /* ---- File Handling ---- */
    function renderFilePreviews() {
        const container = el.filePreviews();
        container.innerHTML = '';
        state.uploadedFiles.forEach(fileData => {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';

            let media;
            if (fileData.type.startsWith('image/')) {
                media = document.createElement('img');
            } else if (fileData.type.startsWith('video/')) {
                media = document.createElement('video');
                media.muted = true;
            }
            media.src = `data:${fileData.type};base64,${fileData.base64}`;
            previewItem.appendChild(media);

            const removeBtn = document.createElement('button');
            removeBtn.className = 'remove-file-btn';
            removeBtn.innerHTML = '&times;';
            removeBtn.onclick = () => {
                state.uploadedFiles = state.uploadedFiles.filter(f => f.id !== fileData.id);
                renderFilePreviews();
            };
            previewItem.appendChild(removeBtn);
            container.appendChild(previewItem);
        });
    }

    function handleFileSelect(event) {
        for (const file of event.target.files) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const fileData = {
                    name: file.name,
                    type: file.type,
                    base64: e.target.result.split(',')[1], // Only base64 part
                    id: Date.now() + Math.random()
                };
                state.uploadedFiles.push(fileData);
                renderFilePreviews();
            };
            reader.readAsDataURL(file);
        }
        el.fileInput().value = ''; // Reset to allow re-selecting same file
    }

    /* ---- Send message ---- */
    async function sendMessage() {
        const prompt = el.promptInput().value.trim();
        if ((!prompt && state.uploadedFiles.length === 0) || state.loading) return;

        state.loading = true;
        el.sendBtn().disabled = true;
        el.promptInput().value = "";
        el.promptInput().style.height = "auto";

        // Append user message
        const filesToSend = [...state.uploadedFiles];
        appendMessage("user", prompt, filesToSend);
        showTyping();

        // Clear files from state and UI after appending
        state.uploadedFiles = [];
        renderFilePreviews();

        // Build history to send
        const historyToSend = state.history.slice(-20); // last 20 turns
        const model = document.getElementById('modelSelect').value;
        const temp = parseFloat(el.tempSlider().value);
        const system = el.systemPrompt().value.trim();
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
                    model,
                    temperature: temp,
                    system,
                    history: historyToSend,
                    files: filesToSend, // Send files data
                }),
            });

            const data = await resp.json();

            hideTyping();

            if (!resp.ok || data.error) {
                showError(data.error || "Lỗi không xác định");
                return;
            }

            // Store in history
            const userContent = [{ type: 'text', text: prompt }];
            filesToSend.forEach(file => {
                userContent.push({
                    type: 'inline_data', part: { inline_data: { mime_type: file.type, data: file.base64 } }
                });
            });
            state.history.push({ role: "user", parts: userContent });
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

        // File upload
        el.uploadBtn().addEventListener("click", () => el.fileInput().click());
        el.fileInput().addEventListener("change", handleFileSelect);


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
            state.uploadedFiles = [];
            renderFilePreviews();
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
