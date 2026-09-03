@php
    $record = $getRecord();
    $video = $record instanceof \App\Models\Video ? $record : ($record?->video ?? null);
    $videoUrl = $video?->video_url ?? ($video ? route('admin.videos.stream', $video->id) : null);
@endphp

@if($video)
    <div style="background: rgba(15, 23, 42, 0.6); padding: 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); margin-top: 8px;">
        @if($videoUrl)
            <video controls preload="metadata" style="width: 100%; max-width: 640px; max-height: 360px; border-radius: 8px; background: #000; display: block; margin-bottom: 12px;">
                <source src="{{ $videoUrl }}" type="video/mp4">
                <source src="{{ $videoUrl }}" type="video/webm">
                متصفحك لا يدعم تشغيل الفيديو.
            </video>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="{{ $videoUrl }}" target="_blank" style="padding: 6px 14px; background: #2563eb; color: #fff; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">
                    👁️ مشاهدة / فتح المشغل بالحجم الكامل
                </a>
            </div>
        @else
            <div style="padding: 12px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; color: #f59e0b; font-size: 13px;">
                ⚠️ تنبيه: رابط الفيديو غير متوفر حالياً.
            </div>
        @endif
    </div>
@endif
