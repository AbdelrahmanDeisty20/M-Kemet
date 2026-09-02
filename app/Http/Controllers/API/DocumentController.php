<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    /**
     * Stream/View document file securely (PDFs, Images)
     * Only the document owner can access their private documents
     */
    public function viewFile(Request $request, Document $document): BinaryFileResponse
    {
        // Authorization: المستخدم يشوف مستنداته بس
        if ($document->user_id !== Auth::id()) {
            abort(403, __('messages.unauthorized'));
        }

        $disk = $document->disk ?? 'private';

        if (!Storage::disk($disk)->exists($document->file_path)) {
            abort(404, __('messages.notFound'));
        }

        $path     = Storage::disk($disk)->path($document->file_path);
        $mimeType = Storage::disk($disk)->mimeType($document->file_path);

        return response()->file($path, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }
}
