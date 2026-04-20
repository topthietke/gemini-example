<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function __construct(protected GeminiService $gemini) {}

    /**
     * Show main chat page.
     */
    public function index()
    {
        $models = [];
        try {
            $models = $this->gemini->listModels();
        } catch (\Exception $e) {
            // silently fail – user can still chat with default model
        }

        return view('chat.index', compact('models'));
    }

    /**
     * Handle chat message (AJAX).
     */
    public function send(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'prompt'         => 'required|string|min:1|max:10000',
            'history'        => 'nullable|array',
            'history.*.role'    => 'required|in:user,model',
            'history.*.content' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            $result = $this->gemini->generateContent(
                $request->input('prompt'),
                $request->input('history', [])
            );
            return response()->json([
                'success' => true,
                'text'    => $result['text'],
                'meta'    => [
                    'model'         => $result['model'],
                    'input_tokens'  => $result['input_tokens'],
                    'output_tokens' => $result['output_tokens'],
                ],
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging if needed: \Log::error($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // app/Http/Controllers/ChatController.php


    /**
     * Test API key connection.
     */
    public function testConnection() {        
        try {
            $models = $this->gemini->listModels();                
            return response()->json([
                'success' => true,
                'message' => 'Kết nối thành công!',
                'models'  => count($models),
                'data'    => $models
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
