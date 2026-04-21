<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TextToVideoService
{
    protected Client $client;
    protected string $apiKey;
    protected string $veoUrl;
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->apiKey    = config('gemini.video_api_key') ?: config('gemini.api_key');
        $this->veoUrl    = config('gemini.veo_api_url', 'https://generativelanguage.googleapis.com/v1beta');
        $this->geminiService = $geminiService;

        $this->client = new Client([
            'timeout' => 300,
            'headers' => [
                'Content-Type'   => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ],
        ]);
    }

    /**
     * Generate video using Google Veo 2 API
     * POST https://generativelanguage.googleapis.com/v1beta/models/veo-2.0-generate-001:predictLongRunning
     */
    public function generateVideo(array $params): array
    {
        $prompt      = $params['prompt'] ?? '';
        $aspectRatio = $params['aspect_ratio'] ?? '16:9';
        $duration    = (int) ($params['duration'] ?? 8);
        $style       = $params['style'] ?? 'cinematic';

        // Enhance prompt with style
        $enhancedPrompt = $this->enhancePrompt($prompt, $style, $params);

        try {
            // Veo 2 API endpoint
            $response = $this->client->post(
                "{$this->veoUrl}/models/veo-2.0-generate-001:predictLongRunning",
                [
                    'json' => [
                        'instances' => [
                            [
                                'prompt' => $enhancedPrompt,
                            ],
                        ],
                        'parameters' => [
                            'aspectRatio'    => $aspectRatio,
                            'durationSeconds' => $duration,
                            'enhancePrompt'  => true,
                            'numberOfVideos' => 1,
                        ],
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            // Returns an operation name for polling
            if (isset($data['name'])) {
                return [
                    'success'        => true,
                    'operation_name' => $data['name'],
                    'status'         => 'processing',
                    'message'        => 'Video đang được tạo, vui lòng chờ...',
                ];
            }

            return [
                'success' => false,
                'error'   => 'Không nhận được operation name từ API',
            ];

        } catch (RequestException $e) {
            $errorMsg = $this->parseError($e);

            // Fallback: use Gemini to generate video script + return mock for demo
            Log::warning('Veo API not available, using fallback', ['error' => $errorMsg]);
            return $this->generateVideoFallback($prompt, $style, $aspectRatio, $duration);
        }
    }

    /**
     * Poll operation status
     */
    public function checkOperationStatus(string $operationName): array
    {
        try {
            $response = $this->client->get(
                "{$this->veoUrl}/{$operationName}"
            );

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['done'] ?? false) {
                $videoUri = $data['response']['videos'][0]['video']['uri'] ?? null;
                $gcsUri   = $data['response']['videos'][0]['video']['gcsUri'] ?? null;

                return [
                    'success'  => true,
                    'done'     => true,
                    'video_uri' => $videoUri,
                    'gcs_uri'  => $gcsUri,
                ];
            }

            return [
                'success' => true,
                'done'    => false,
                'status'  => 'processing',
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'error'   => $this->parseError($e),
            ];
        }
    }

    /**
     * Fallback: Use Gemini to generate a detailed video script/storyboard
     * when Veo API is not accessible (no allowlist / quota)
     */
    protected function generateVideoFallback(string $prompt, string $style, string $aspectRatio, int $duration): array
    {
        try {
            $scriptPrompt = "Bạn là một đạo diễn video chuyên nghiệp. Tạo một storyboard chi tiết cho video với prompt sau:\n\n"
                . "**Prompt:** {$prompt}\n"
                . "**Style:** {$style}\n"
                . "**Tỉ lệ:** {$aspectRatio}\n"
                . "**Thời lượng:** {$duration} giây\n\n"
                . "Hãy tạo storyboard với:\n"
                . "1. Mô tả từng cảnh quay (scene)\n"
                . "2. Camera movement\n"
                . "3. Lighting & color palette\n"
                . "4. Audio/music suggestion\n"
                . "5. Transition effects\n\n"
                . "Trả lời bằng tiếng Việt, format markdown đẹp.";

            $geminiResponse = $this->geminiService->chat($scriptPrompt);
            $script = 'Không thể tạo script.';
            if ($geminiResponse['success']) {
                $script = $geminiResponse['text'];
            }

            return [
                'success'  => true,
                'fallback' => true,
                'status'   => 'script_generated',
                'message'  => 'Veo API chưa khả dụng cho tài khoản này. Đã tạo storyboard thay thế.',
                'script'   => $script,
                'note'     => 'Để dùng tính năng tạo video thật, bạn cần đăng ký Google Veo API allowlist tại: https://deepmind.google/technologies/veo/',
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'error'   => 'Lỗi kết nối API: ' . $this->parseError($e),
            ];
        }
    }

    /**
    {
        $styleMap = [
            'cinematic'    => 'cinematic, 4K, professional filmmaking, dramatic lighting, shallow depth of field',
            'anime'        => 'anime style, vibrant colors, Japanese animation, detailed artwork',
            'realistic'    => 'photorealistic, ultra-detailed, natural lighting, 8K resolution',
            'cartoon'      => 'cartoon style, bright colors, flat design, fun and playful',
            'documentary'  => 'documentary style, handheld camera, natural lighting, authentic',
            'commercial'   => 'commercial advertisement, professional, clean, high production value',
            'artistic'     => 'artistic, painterly, impressionistic, creative visual storytelling',
        ];

        $styleKeywords = $styleMap[$style] ?? $styleMap['cinematic'];
        $mood = $params['mood'] ?? '';
        $camera = $params['camera'] ?? 'medium shot';

        return "{$prompt}. Style: {$styleKeywords}. Camera: {$camera}." . ($mood ? " Mood: {$mood}." : '');
    }

    protected function parseError(RequestException $e): string
    {
        if ($e->hasResponse()) {
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            return $body['error']['message'] ?? $e->getMessage();
        }
        return $e->getMessage();
    }
}
