<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * GET /chat - Chat interface
     */
    public function index()
    {
        return view('chat.index');
    }

    /**
     * POST /api/chat/message - Send text message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:10000',
            'history' => 'nullable|array',
            'history.*.role'    => 'in:user,model',
            'history.*.content' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $history = $this->sanitizeHistory($request->input('history', []));
        $result  = $this->gemini->chat($request->message, $history);

        return response()->json($result);
    }

    /**
     * POST /api/chat/upload - Send message with file
     */
    public function sendWithFile(Request $request): JsonResponse
    {
        $maxSizeMb = config('gemini.max_file_size_mb', 20);

        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string|max:5000',
            'file'    => "required|file|max:{$maxSizeMb}000",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $file     = $request->file('file');
        $message  = $request->input('message', 'Hãy phân tích nội dung file này.');
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // For files > 5MB, use Gemini File API
        if ($fileSize > 5 * 1024 * 1024) {
            $uploadResult = $this->gemini->uploadFileToGemini($file);

            if (!$uploadResult['success']) {
                return response()->json($uploadResult, 500);
            }

            $result = $this->gemini->chatWithFileUri(
                $message,
                $uploadResult['file_uri'],
                $uploadResult['mime_type'],
                $this->sanitizeHistory($request->input('history', []))
            );
        } else {
            $result = $this->gemini->chatWithFile(
                $message,
                $file,
                $this->sanitizeHistory($request->input('history', []))
            );
        }

        // Save uploaded file locally for reference
        $savedPath = null;
        if ($result['success']) {
            $ext        = $file->getClientOriginalExtension();
            $savedName  = Str::uuid() . '.' . $ext;
            $savedPath  = $file->storeAs('uploads/chat', $savedName, 'public');
        }

        return response()->json(array_merge($result, [
            'file_info' => [
                'name'      => $file->getClientOriginalName(),
                'size'      => $fileSize,
                'mime_type' => $mimeType,
                'path'      => $savedPath ? Storage::url($savedPath) : null,
            ],
        ]));
    }

    /**
     * POST /api/chat/stream - Stream response (SSE)
     */
    public function streamMessage(Request $request)
    {
        $message = $request->input('message', '');
        $history = $this->sanitizeHistory($request->input('history', []));

        return response()->stream(function () use ($message, $history) {
            foreach ($this->gemini->streamChat($message, $history) as $chunk) {
                echo "data: " . json_encode(['text' => $chunk]) . "\n\n";
                ob_flush();
                flush();
            }
            echo "data: [DONE]\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * GET /api/gemini/models - List available models
     */
    public function listModels(): JsonResponse
    {
        $result = $this->gemini->listModels();
        return response()->json($result);
    }

    private function sanitizeHistory(array $history): array
    {
        return array_map(function ($turn) {
            return [
                'role'    => in_array($turn['role'] ?? '', ['user', 'model']) ? $turn['role'] : 'user',
                'content' => substr($turn['content'] ?? '', 0, 5000),
            ];
        }, array_slice($history, -20)); // Keep last 20 turns
    }
}
