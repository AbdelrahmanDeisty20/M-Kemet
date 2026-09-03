@php
    $doc = $getRecord();
    $disk = $doc->disk ?? 'private';
    $fileExists = \Illuminate\Support\Facades\Storage::disk($disk)->exists($doc->file_path) || \Illuminate\Support\Facades\Storage::disk('public')->exists($doc->file_path);
@endphp

<div style="background: rgba(15, 23, 42, 0.5); padding: 12px 16px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); margin-top: 6px;">
    @if(!$fileExists)
        <div style="padding: 10px 14px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; color: #f87171; font-size: 13px;">
            ⚠️ تنبيه: ملف المستند غير موجود على السيرفر (اسم الملف: <code style="direction: ltr; display: inline-block;">{{ basename($doc->file_path) }}</code>)
        </div>
    @elseif($doc->is_image)
        <div style="text-align: center; padding: 10px; background: #090d16; border-radius: 8px; margin-bottom: 10px;">
            <img src="{{ $doc->admin_file_url }}" alt="{{ $doc->document_type_name }}" style="max-height: 220px; max-width: 100%; object-fit: contain; border-radius: 6px; display: inline-block;" />
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="{{ $doc->admin_file_url }}" target="_blank" style="color: #60a5fa; text-decoration: underline; font-size: 13px; font-weight: 600;">
                👁️ فتح الصورة بالشاشة الكاملة
            </a>
            <a href="{{ $doc->admin_download_url }}" style="color: #34d399; text-decoration: underline; font-size: 13px; font-weight: 600;">
                ⬇️ تحميل الصورة
            </a>
        </div>
    @elseif($doc->is_pdf)
        <div style="padding: 12px; background: #090d16; border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">📄</span>
                <div>
                    <strong style="color: #f1f5f9; font-size: 13px; display: block;">{{ basename($doc->file_path) }}</strong>
                    <span style="color: #94a3b8; font-size: 11px;">مستند PDF / سيرة ذاتية</span>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ $doc->admin_file_url }}" target="_blank" style="padding: 6px 14px; background: #2563eb; color: #fff; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">
                👁️ فتح / عرض السيرة الذاتية (PDF)
            </a>
            <a href="{{ $doc->admin_download_url }}" style="padding: 6px 14px; background: #059669; color: #fff; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">
                ⬇️ تحميل الملف
            </a>
        </div>
    @else
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: #090d16; border-radius: 8px;">
            <span style="color: #e2e8f0; font-size: 13px;">📁 {{ basename($doc->file_path) }}</span>
            <div style="display: flex; gap: 10px;">
                <a href="{{ $doc->admin_file_url }}" target="_blank" style="color: #60a5fa; font-size: 13px;">👁️ فتح</a>
                <a href="{{ $doc->admin_download_url }}" style="color: #34d399; font-size: 13px;">⬇️ تحميل</a>
            </div>
        </div>
    @endif
</div>
