https://aistudio.google.com

# 🤖 Gemini Chat — Laravel + Google Gemini API

Ứng dụng chat AI được xây dựng bằng Laravel, kết nối trực tiếp với Google Gemini API.

![Preview](preview.png)

---

## ✨ Tính năng

- 💬 Chat real-time với Google Gemini AI
- 🔄 Lưu lịch sử hội thoại trong phiên (multi-turn conversation)
- 🎛️ Tuỳ chỉnh model, nhiệt độ (temperature), system prompt
- ⚡ Kiểm tra kết nối API
- 📊 Hiển thị số token đã dùng
- 📱 Responsive — hỗ trợ mobile
- 🌙 Dark mode mặc định
- 🔐 CSRF protection

---

## 📋 Yêu cầu

- PHP >= 8.2
- Composer
- Laravel 11
- Google Gemini API Key ([lấy tại đây](https://aistudio.google.com/app/apikey))

---

## 🚀 Cài đặt

### 1. Clone hoặc tải dự án

```bash
git clone <your-repo-url> gemini-chat
cd gemini-chat
```

### 2. Cài dependencies

```bash
composer install
```

### 3. Cấu hình môi trường

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Thêm Gemini API Key vào `.env`

```env
GEMINI_API_KEY=your_actual_api_key_here
GEMINI_MODEL=gemini-2.0-flash
GEMINI_TEMPERATURE=0.7
GEMINI_MAX_TOKENS=2048
```

> 💡 **Lấy API Key miễn phí tại:** https://aistudio.google.com/app/apikey

### 5. Chạy ứng dụng

```bash
php artisan serve
```

Mở trình duyệt tại: **http://localhost:8000**

---

## 📁 Cấu trúc dự án

```
gemini-laravel/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── ChatController.php      # Xử lý request chat
│   └── Services/
│       └── GeminiService.php           # Tích hợp Gemini API
├── config/
│   └── gemini.php                      # Cấu hình Gemini
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php           # Layout chính
│       └── chat/
│           └── index.blade.php         # Giao diện chat
├── routes/
│   └── web.php                         # Định tuyến
├── public/
│   ├── css/app.css                     # Stylesheet
│   └── js/app.js                       # Frontend logic
└── .env.example                        # Mẫu cấu hình
```

---

## 🔌 API Endpoints

| Method | URL | Mô tả |
|--------|-----|-------|
| GET | `/` | Trang chat chính |
| POST | `/chat/send` | Gửi tin nhắn |
| GET | `/chat/test-connection` | Kiểm tra kết nối API |

---

## ⚙️ Cấu hình nâng cao

Chỉnh sửa file `config/gemini.php` hoặc các biến trong `.env`:

```env
# Model mặc định
GEMINI_MODEL=gemini-2.0-flash        # Nhanh, tiết kiệm
# GEMINI_MODEL=gemini-1.5-pro        # Mạnh hơn, chậm hơn
# GEMINI_MODEL=gemini-1.5-flash      # Cân bằng

# Độ sáng tạo (0.0 = chính xác, 1.0 = sáng tạo)
GEMINI_TEMPERATURE=0.7

# Giới hạn token đầu ra
GEMINI_MAX_TOKENS=2048
```

---

## 📦 Models Gemini hỗ trợ

| Model | Đặc điểm |
|-------|---------|
| `gemini-2.0-flash` | Nhanh, hiệu quả — **khuyến nghị** |
| `gemini-1.5-pro` | Mạnh mẽ, context dài |
| `gemini-1.5-flash` | Cân bằng tốc độ/chất lượng |

---

## 🛠️ Mở rộng

### Thêm streaming response

Chỉnh sửa `GeminiService::generateContent()` để dùng endpoint `:streamGenerateContent`.

### Lưu lịch sử vào database

1. Tạo migration cho bảng `conversations` và `messages`
2. Cập nhật `ChatController` để lưu/load history
3. Thêm authentication nếu cần

### Thêm upload ảnh (Multimodal)

Gemini hỗ trợ gửi ảnh — cập nhật `parts` trong request để thêm `inlineData`.

---

## 📄 License

MIT License — Tự do sử dụng và chỉnh sửa.
