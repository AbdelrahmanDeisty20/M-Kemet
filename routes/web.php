<?php

use App\Models\Document;
use App\Models\Video;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/documents/{document}/file', function (Document $document) {
        $disk = $document->disk ?? 'private';
        if (!Storage::disk($disk)->exists($document->file_path)) {
            if (Storage::disk('public')->exists($document->file_path)) {
                $disk = 'public';
            } else {
                abort(404, 'الملف غير موجود على السيرفر');
            }
        }

        $path = Storage::disk($disk)->path($document->file_path);
        $mimeType = Storage::disk($disk)->mimeType($document->file_path) ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    })->name('admin.documents.file');

    Route::get('/documents/{document}/download', function (Document $document) {
        $disk = $document->disk ?? 'private';
        if (!Storage::disk($disk)->exists($document->file_path)) {
            if (Storage::disk('public')->exists($document->file_path)) {
                $disk = 'public';
            } else {
                abort(404, 'الملف غير موجود على السيرفر');
            }
        }

        $path = Storage::disk($disk)->path($document->file_path);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $filename = $document->document_type . '_' . $document->user_id . '.' . $ext;

        return response()->download($path, $filename);
    })->name('admin.documents.download');

    Route::get('/videos/{video}/stream', function (Video $video) {
        $disk = 'public';
        if (!Storage::disk($disk)->exists($video->video_path)) {
            abort(404, 'الفيديو غير موجود على السيرفر');
        }

        $path = Storage::disk($disk)->path($video->video_path);
        $mimeType = Storage::disk($disk)->mimeType($video->video_path) ?: 'video/mp4';

        return response()->file($path, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    })->name('admin.videos.stream');

    Route::get('/videos/{video}/download', function (Video $video) {
        $disk = 'public';
        if (!Storage::disk($disk)->exists($video->video_path)) {
            abort(404, 'الفيديو غير موجود على السيرفر');
        }

        $path = Storage::disk($disk)->path($video->video_path);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $filename = 'intro_video_user_' . $video->user_id . '.' . $ext;

        return response()->download($path, $filename);
    })->name('admin.videos.download');
});

