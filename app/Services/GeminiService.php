<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected string $apiKey;
    protected string $model;

    /**
     * Models to try in order when quota is exceeded.
     */
    protected array $fallbackModels = [
        'gemini-2.0-flash',
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite'


    ];

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->model  = config('gemini.model', 'gemini-2.0-flash');
    }

    /**
     * Send a prompt and get a response (with auto-fallback on quota errors).
     */
    public function generateContent(string $prompt, array $history = []): array
    {
        $contents = [];

        foreach ($history as $msg) {
            $contents[] = [
                'role'  => $msg['role'],
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $prompt]],
        ];

        $body = [
            'contents'         => $contents,
            'generationConfig' => [
                'temperature'     => config('gemini.temperature', 0.7),
                'maxOutputTokens' => config('gemini.max_tokens', 2048),
                'topP'            => 0.95,
                'topK'            => 40,
            ],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ],
        ];

        // Try configured model first, then fallbacks
        $modelsToTry = array_unique(array_merge([$this->model], $this->fallbackModels));
        $lastError   = null;

        foreach ($modelsToTry as $model) {
            // Determine API version based on model name.
            
            if (str_contains($model, '2.0') || str_contains($model, '2.5')) {
                // $apiVersion = 'v1beta'; // Gemini 2.x dùng v1beta
                $apiVersion = 'v1'; // Gemini 2.x dùng v1beta
            } 
            $baseUrl = "https://generativelanguage.googleapis.com/{$apiVersion}";

            $response = Http::timeout(30)
                ->withoutVerifying()
                ->post("{$baseUrl}/models/{$model}:generateContent?key={$this->apiKey}", $body);

            // Success
            if ($response->successful()) {
                return [
                    'text'         => $response->json('candidates.0.content.parts.0.text', ''),
                    'model'        => $model,
                    'input_tokens' => $response->json('usageMetadata.promptTokenCount', 0),
                    'output_tokens'=> $response->json('usageMetadata.candidatesTokenCount', 0),
                ];
            }

            $errorCode    = $response->json('error.code', 0);
            $errorMessage = $response->json('error.message', 'Loi khong xac dinh');
            $isQuota      = $errorCode === 429
                || str_contains(strtolower($errorMessage), 'quota') // e.g., "userRateLimitExceeded"
                || str_contains(strtolower($errorMessage), 'resource_exhausted') // e.g., "resourceExhausted"
                || str_contains(strtolower($errorMessage), 'high demand'); // e.g., "This model is currently experiencing high demand."

            // Non-retryable error -> throw immediately
            if (! $isQuota) {
                throw new \Exception("Gemini API Error: {$errorMessage}");
            }

            // Extract retry-after seconds, wait briefly if <= 5s
            preg_match('/retry in ([\d.]+)s/i', $errorMessage, $m);
            $wait = isset($m[1]) ? (int) ceil((float) $m[1]) : 0;
            if ($wait > 0 && $wait <= 5) {
                sleep($wait);
            }

            $lastError = "Model [{$model}] vuot quota" . ($wait > 0 ? " (thu lai sau {$wait}s)" : '') . ".";
        }

        throw new \Exception(
            "Tat ca models deu vuot quota. {$lastError} " .
            "Vui long kiem tra billing tai https://ai.dev/rate-limit hoac tao API key moi tai https://aistudio.google.com/app/apikey"
        );
    }

    /**
     * List available Gemini models.
     */
    public function listModels(): array
    {
        $apiVersions = ['v1beta', 'v1'];
        $allModels = [];
        
        foreach ($apiVersions as $version) {
            $baseUrl = "https://generativelanguage.googleapis.com/{$version}";
            try {
                $response = Http::timeout(10)
                    ->withoutVerifying()
                    ->get("{$baseUrl}/models?key={$this->apiKey}");
    
                if ($response->successful()) {
                    $models = collect($response->json('models', []))
                        ->filter(fn($m) => str_contains($m['name'], 'gemini'))
                        ->mapWithKeys(fn($m) => [str_replace('models/', '', $m['name']) => [
                            'id'          => str_replace('models/', '', $m['name']),
                            'displayName' => $m['displayName'] ?? $m['name'],
                        ]])
                        ->all();
                    $allModels = array_merge($allModels, $models);
                }
            } catch (\Exception $e) {
                // Ignore connection errors and try next version
            }
        }
        
        return array_values($allModels);
    }
}