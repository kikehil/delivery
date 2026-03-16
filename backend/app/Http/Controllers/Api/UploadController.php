<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            \Log::info('Upload Attempt', [
                'has_file' => $request->hasFile('image'),
                'file_info' => $request->file('image') ? [
                    'name' => $request->file('image')->getClientOriginalName(),
                    'size' => $request->file('image')->getSize(),
                    'mime' => $request->file('image')->getMimeType(),
                ] : 'no file'
            ]);

            if (!$request->hasFile('image')) {
                return response()->json(['status' => 'error', 'message' => 'No se recibió archivo'], 400);
            }

            $request->validate([
                'image' => 'required|image|max:10240', // 10MB
                'type' => 'nullable|string'
            ]);

            $file = $request->file('image');
            $filename = Str::random(30) . '.' . $file->getClientOriginalExtension();
            $folder = $request->input('type', 'others');
            
            // Explicit path construction for Windows
            $subPath = 'uploads/' . $folder;
            $fullPath = $file->storeAs($subPath, $filename, 'public');
            
            $url = asset('storage/' . $fullPath);

            \Log::info('File stored successfully', ['path' => $fullPath, 'url' => $url]);

            return response()->json([
                'status' => 'success',
                'url' => $url
            ]);
        } catch (\Exception $e) {
            \Log::error('Upload Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
