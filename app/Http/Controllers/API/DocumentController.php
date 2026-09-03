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
     */
    public function viewFile(Request $request, Document $document): BinaryFileResponse
    {
        $authUser = Auth::guard('sanctum')->user() ?? $request->user();

        // Allow access if owner, or if document is approved/belongs to an approved candidate
        $isOwner = $authUser && $document->user_id === $authUser->id;
        $isApprovedCandidateDoc = $document->is_approved || $document->user?->candidateProfile?->status === 'approved';

        if (!$isOwner && !$isApprovedCandidateDoc) {
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
