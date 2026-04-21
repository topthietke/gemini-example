<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiService
{
    protected Client $client;
    protected string $apiKey;
    protected string $baseUrl;
    protected string $chatModel;
    protected string $visionModel;
    protected string $uploadUrl;

    public function __construct()
    {
        $this->apiKey   = config('gemini.api_key');
        $this->baseUrl  = config('gemini.base_url');
        $this->uploadUrl = config('gemini.upload_url', 'https://generativelanguage.googleapis.com/upload/v1beta');
        $this->chatModel   = config('gemini.chat_model');
        $this->visionModel = config('gemini.vision_model');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 120,
            'verify'   => $this->getCACertPath(),
            'headers'  => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ],
        ]);
    }

    /**
     * Send a text message to Gemini
     */
    public function chat(string $message, array $history = []): array
    {
        try {            
            $contents = $this->buildContents($history, $message);
            $apiKey = env('GEMINI_API_KEY');            
            $url = "/v1beta/models/{$this->chatModel}:generateContent?key={$apiKey}";
            
            
            $response = $this->client->post($url, [
                'json' => [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature'     => 0.9,
                        'topK'            => 40,
                        'topP'            => 0.95,
                        'maxOutputTokens' => 8192,
                    ],
                    'safetySettings' => $this->getSafetySettings(),
                ],
            ]);
            
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->parseResponse($data);

        } catch (RequestException $e) {
            dd($e->getMessage());
            Log::error('Gemini Chat Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error'   => $this->parseError($e),
            ];
        }
    }

    /**
     * Send message with file (image/document/video/audio)
     */
    public function chatWithFile(string $message, UploadedFile $file, array $history = []): array
    {
        try {
            $mimeType = $file->getMimeType();
            $fileData = base64_encode(file_get_contents($file->getRealPath()));

            $contents = $this->buildContents($history, $message, [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data'      => $fileData,
                ],
            ]);

            $response = $this->client->post("/models/{$this->visionModel}:generateContent", [
                'json' => [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature'     => 0.9,
                        'maxOutputTokens' => 8192,
                    ],
                    'safetySettings' => $this->getSafetySettings(),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $this->parseResponse($data);

        } catch (RequestException $e) {
            Log::error('Gemini File Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error'   => $this->parseError($e),
            ];
        }
    }

    /**
     * Upload file to Gemini File API (for larger files)
     */
    public function uploadFileToGemini(UploadedFile $file): array
    {
        try {
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();
            $fileName = $file->getClientOriginalName();

            // Step 1: Initiate resumable upload
            $initResponse = $this->client->post("{$this->uploadUrl}/files", [
                'headers' => [
                    'X-Goog-Upload-Protocol' => 'resumable',
                    'X-Goog-Upload-Command'  => 'start',
                    'X-Goog-Upload-Header-Content-Length' => $fileSize,
                    'X-Goog-Upload-Header-Content-Type'   => $mimeType,
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $this->apiKey,
                ],
                'json' => [
                    'file' => ['display_name' => $fileName],
                ],
            ]);

            $uploadUrl = $initResponse->getHeaderLine('X-Goog-Upload-URL');

            // Step 2: Upload actual file content
            $uploadResponse = $this->client->post($uploadUrl, [
                'headers' => [
                    'Content-Length' => $fileSize,
                    'X-Goog-Upload-Offset'  => 0,
                    'X-Goog-Upload-Command' => 'upload, finalize',
                ],
                'body' => fopen($file->getRealPath(), 'r'),
            ]);

            $fileInfo = json_decode($uploadResponse->getBody()->getContents(), true);

            return [
                'success'   => true,
                'file_uri'  => $fileInfo['file']['uri'] ?? null,
                'file_name' => $fileInfo['file']['name'] ?? null,
                'mime_type' => $mimeType,
            ];

        } catch (RequestException $e) {
            Log::error('Gemini Upload Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error'   => $this->parseError($e),
            ];
        }
    }

    /**
     * Chat using uploaded file URI
     */
    public function chatWithFileUri(string $message, string $fileUri, string $mimeType, array $history = []): array
    {
        try {
            $contents = $this->buildContents($history, $message, [
                'file_data' => [
                    'mime_type' => $mimeType,
                    'file_uri'  => $fileUri,
                ],
            ]);

            $response = $this->client->post("/models/{$this->visionModel}:generateContent", [
                'json' => [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature'     => 0.9,
                        'maxOutputTokens' => 8192,
                    ],
                    'safetySettings' => $this->getSafetySettings(),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $this->parseResponse($data);

        } catch (RequestException $e) {
            return [
                'success' => false,
                'error'   => $this->parseError($e),
            ];
        }
    }

    /**
     * Stream chat response (Server-Sent Events)
     */
    public function streamChat(string $message, array $history = []): \Generator
    {
        $contents = $this->buildContents($history, $message);

        try {
            $response = $this->client->post("/models/{$this->chatModel}:streamGenerateContent", [
                'query' => ['alt' => 'sse'],
                'json'  => [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature'     => 0.9,
                        'maxOutputTokens' => 8192,
                    ],
                ],
                'stream' => true,
            ]);

            $body = $response->getBody();
            $buffer = '';

            while (!$body->eof()) {
                $chunk = $body->read(1024);
                $buffer .= $chunk;

                $lines = explode("\n", $buffer);
                $buffer = array_pop($lines);

                foreach ($lines as $line) {
                    if (str_starts_with($line, 'data: ')) {
                        $jsonStr = substr($line, 6);
                        if ($jsonStr === '[DONE]') break;

                        $data = json_decode($jsonStr, true);
                        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                            yield $data['candidates'][0]['content']['parts'][0]['text'];
                        }
                    }
                }
            }

        } catch (RequestException $e) {
            yield 'ERROR: ' . $this->parseError($e);
        }
    }

    /**
     * Get available models
     */
    public function listModels(): array
    {
        try {
            $response = $this->client->get('/models');
            $data = json_decode($response->getBody()->getContents(), true);
            return ['success' => true, 'models' => $data['models'] ?? []];
        } catch (RequestException $e) {
            return ['success' => false, 'error' => $this->parseError($e)];
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Resolve SSL CA cert — fixes cURL error 60 on Windows/XAMPP/Laragon
     */
    private function getCACertPath(): string|bool
    {
        // 1. Đường dẫn tuỳ chỉnh: GEMINI_SSL_CA_CERT=/path/to/cacert.pem
        if ($custom = config('gemini.ssl_ca_cert')) {
            return $custom;
        }
        // 2. File cacert.pem tự đặt trong storage/app/cacert.pem
        $storageCert = storage_path('app/cacert.pem');
        if (file_exists($storageCert)) {
            return $storageCert;
        }
        // 3. PHP đã cấu hình curl.cainfo (thường đúng trên Linux)
        $curlCa = ini_get('curl.cainfo');
        if ($curlCa && file_exists($curlCa)) {
            return $curlCa;
        }
        // 4. openssl.cafile
        $opensslCa = ini_get('openssl.cafile');
        if ($opensslCa && file_exists($opensslCa)) {
            return $opensslCa;
        }
        // 5. Tắt SSL verify — chỉ dùng local dev: GEMINI_SSL_VERIFY=false
        if (config('gemini.ssl_verify') === false) {
            return false;
        }
        return true;
    }

    private function buildContents(array $history, string $message, ?array $filePart = null): array
    {
        $contents = [];

        foreach ($history as $turn) {
            $contents[] = [
                'role'  => $turn['role'],
                'parts' => [['text' => $turn['content']]],
            ];
        }

        $userParts = [];
        if ($filePart) {
            $userParts[] = $filePart;
        }
        $userParts[] = ['text' => $message];

        $contents[] = [
            'role'  => 'user',
            'parts' => $userParts,
        ];

        return $contents;
    }

    private function parseResponse(array $data): array
    {
        if (isset($data['error'])) {
            return [
                'success' => false,
                'error'   => $data['error']['message'] ?? 'Unknown error',
            ];
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $finishReason = $data['candidates'][0]['finishReason'] ?? 'STOP';
        $usageMetadata = $data['usageMetadata'] ?? [];

        return [
            'success'      => true,
            'text'         => $text,
            'finish_reason' => $finishReason,
            'usage'        => [
                'prompt_tokens'     => $usageMetadata['promptTokenCount'] ?? 0,
                'completion_tokens' => $usageMetadata['candidatesTokenCount'] ?? 0,
                'total_tokens'      => $usageMetadata['totalTokenCount'] ?? 0,
            ],
        ];
    }

    private function parseError(RequestException $e): string
    {
        if ($e->hasResponse()) {
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            return $body['error']['message'] ?? $e->getMessage();
        }
        return $e->getMessage();
    }

    private function getSafetySettings(): array
    {
        return [
            ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
        ];
    }
}
