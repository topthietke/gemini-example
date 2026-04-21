<?php

namespace App\Http\Controllers;

use App\Services\TextToVideoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    protected TextToVideoService $videoService;

    public function __construct(TextToVideoService $videoService)
    {
        $this->videoService = $videoService;
    }

    /**
     * GET /video - Video generation page
     */
    public function index()
    {
        return view('video.index');
    }

    /**
     * POST /api/video/generate - Generate video from text
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt'       => 'required|string|min:10|max:2000',
            'aspect_ratio' => 'nullable|in:16:9,9:16,1:1,4:3',
            'duration'     => 'nullable|integer|min:4|max:60',
            'style'        => 'nullable|in:cinematic,anime,realistic,cartoon,documentary,commercial,artistic',
            'mood'         => 'nullable|string|max:100',
            'camera'       => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $result = $this->videoService->generateVideo($request->all());

        return response()->json($result);
    }

    /**
     * GET /api/video/status/{operationName} - Check video generation status
     */
    public function checkStatus(Request $request, string $operationName): JsonResponse
    {
        $result = $this->videoService->checkOperationStatus($operationName);
        return response()->json($result);
    }
}
