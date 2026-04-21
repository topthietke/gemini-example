# 🤖 Gemini Studio — Laravel AI Platform

Ứng dụng Laravel tích hợp **Google Gemini AI** với các tính năng:
- 💬 Chat AI với Gemini (text, đa lượt)
- 📎 Upload & phân tích file (ảnh, PDF, video, audio)
- 🎬 Tạo video từ văn bản (Google Veo 2)

---

## 📋 Yêu cầu

| Thành phần | Phiên bản |
|---|---|
| PHP | ≥ 8.1 |
| Composer | ≥ 2.0 |
| Laravel | 10.x |
| Google Gemini API Key | Required |

---

## 🚀 Cài đặt nhanh

```bash
# 1. Clone hoặc giải nén project
cd gemini-studio

# 2. Chạy setup script
chmod +x setup.sh
./setup.sh

# 3. Thêm Gemini API Key vào .env
nano .env
# → GEMINI_API_KEY=AIza...

# 4. Khởi động
php artisan serve
```

Mở trình duyệt: **http://localhost:8000**

---

## ⚙️ Cấu hình `.env`

```env
# ============================
# BẮT BUỘC
# ============================
GEMINI_API_KEY=AIzaSy...         # Lấy tại https://aistudio.google.com/app/apikey

# ============================
# TÙY CHỌN
# ============================
GEMINI_CHAT_MODEL=gemini-1.5-pro  # hoặc gemini-1.5-flash (nhanh hơn, rẻ hơn)
GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta

# Google Veo (Text-to-Video) - Cần đăng ký allowlist riêng
GOOGLE_VIDEO_API_KEY=AIzaSy...
```

---

## 🗺️ Cấu trúc Project

```
gemini-studio/
├── app/
│   ├── Http/Controllers/
│   │   ├── ChatController.php       # Chat + File upload
│   │   └── VideoController.php      # Text-to-Video
│   └── Services/
│       ├── GeminiService.php        # Gemini API wrapper
│       └── TextToVideoService.php   # Veo API + fallback
├── config/
│   └── gemini.php                   # Cấu hình API
├── resources/views/
│   ├── layouts/app.blade.php        # Master layout
│   ├── chat/index.blade.php         # Giao diện Chat
│   └── video/index.blade.php        # Giao diện Video
├── routes/
│   └── web.php                      # Định tuyến
├── .env.example
└── setup.sh
```

---

## 🔌 API Endpoints

### Chat
| Method | Endpoint | Mô tả |
|---|---|---|
| `GET` | `/chat` | Giao diện chat |
| `POST` | `/api/chat/message` | Gửi tin nhắn text |
| `POST` | `/api/chat/upload` | Gửi tin nhắn kèm file |
| `POST` | `/api/chat/stream` | Chat streaming (SSE) |
| `GET` | `/api/models` | Danh sách models |

### Video
| Method | Endpoint | Mô tả |
|---|---|---|
| `GET` | `/video` | Giao diện tạo video |
| `POST` | `/api/video/generate` | Tạo video từ text |
| `GET` | `/api/video/status/{op}` | Kiểm tra trạng thái |

---

## 📖 Ví dụ API

### Gửi tin nhắn
```bash
curl -X POST http://localhost:8000/api/chat/message \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: {token}" \
  -d '{"message": "Xin chào Gemini!", "history": []}'
```

### Upload file
```bash
curl -X POST http://localhost:8000/api/chat/upload \
  -F "file=@/path/to/image.jpg" \
  -F "message=Mô tả ảnh này"
```

### Tạo video
```bash
curl -X POST http://localhost:8000/api/video/generate \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "Một con sóc đang nhảy trên cánh rừng mùa thu",
    "style": "cinematic",
    "aspect_ratio": "16:9",
    "duration": 8
  }'
```

---

## 🎬 Về Google Veo (Text-to-Video)

**Google Veo 2** là mô hình tạo video AI tiên tiến nhất của Google. Hiện tại API đang trong giai đoạn **private preview** và yêu cầu đăng ký allowlist.

Khi Veo API chưa khả dụng, hệ thống sẽ tự động **fallback sang Gemini** để tạo storyboard/script chi tiết.

→ **Đăng ký Veo API**: https://deepmind.google/technologies/veo/

---

## 📁 File hỗ trợ

| Loại | Định dạng | Ghi chú |
|---|---|---|
| Ảnh | JPG, PNG, GIF, WebP | Tối đa 20MB |
| Tài liệu | PDF, TXT, DOCX | |
| Video | MP4, MOV | Dùng Gemini File API |
| Audio | MP3, WAV, OGG | Dùng Gemini File API |

File > 5MB sẽ tự động dùng **Gemini File API** (upload resumable).

---

## 🔑 Lấy API Key

1. Truy cập: https://aistudio.google.com/app/apikey
2. Đăng nhập Google Account
3. Tạo API Key mới
4. Paste vào `.env`: `GEMINI_API_KEY=your_key`

**Gemini 1.5 Pro** có free tier hào phóng — phù hợp để thử nghiệm.

---

## 🛠️ Phát triển thêm

Một số hướng mở rộng:
- [ ] Authentication (Laravel Sanctum)
- [ ] Lưu lịch sử chat vào database
- [ ] Rate limiting
- [ ] Queue jobs cho video generation
- [ ] Multi-user support
- [ ] Export chat history

---

Made with ❤️ by Tutn | Powered by Google Gemini
