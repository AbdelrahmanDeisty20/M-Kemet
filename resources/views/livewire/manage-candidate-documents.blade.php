<div class="space-y-6">
    <!-- بيانات صاحب المستندات Header Card -->
    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-lg text-slate-100">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-blue-600/20 text-blue-400 rounded-xl border border-blue-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $user->name ?? $user->phone ?? $user->email }}</h2>
                    <p class="text-sm text-slate-400 font-mono">{{ $user->email }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                    📱 {{ $user->phone ?? 'لا يوجد هاتف' }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                    📂 إجمالي المستندات: {{ $user->documents->count() }}
                </span>
                @if($user->video)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                        🎥 يوجد فيديو تعريفي
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                        🚫 لا يوجد فيديو تعريفي
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- قسم الفيديو التعريفي (Intro Video Section) -->
    @if($user->video)
        <div class="p-6 rounded-2xl bg-slate-900 border border-purple-900/40 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-purple-600/20 text-purple-400 rounded-lg">🎥</span>
                    <h3 class="text-lg font-bold text-white">الفيديو التعريفي (Intro Video)</h3>
                </div>

                <!-- Video Status Badge -->
                <div>
                    @if($user->video->status === 'approved')
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">
                            ✓ معتمد
                        </span>
                    @elseif($user->video->status === 'rejected')
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/40">
                            ✕ مرفوض
                        </span>
                    @else
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/40">
                            ⏳ قيد المراجعة
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                <!-- Video Player Container -->
                <div class="space-y-3">
                    <div class="overflow-hidden rounded-xl bg-black border border-slate-800 shadow-inner">
                        <video controls preload="metadata" class="w-full max-h-[300px] object-contain rounded-xl">
                            <source src="{{ $user->video->admin_stream_url }}" type="video/mp4">
                            متصفحك لا يدعم تشغيل الفيديو.
                        </video>
                    </div>

                    <!-- Action Links for Video -->
                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        <a href="{{ $user->video->admin_stream_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg shadow-sm transition">
                            👁️ مشاهدة بالشاشة الكاملة
                        </a>
                        <a href="{{ $user->video->admin_download_url }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold border border-slate-700 rounded-lg shadow-sm transition">
                            ⬇️ تحميل الفيديو
                        </a>
                    </div>
                </div>

                <!-- Video Controls & Rejection Details -->
                <div class="space-y-4 bg-slate-950/60 p-4 rounded-xl border border-slate-800">
                    <div class="grid grid-cols-2 gap-3 text-xs text-slate-300">
                        <div class="bg-slate-900 p-2.5 rounded-lg border border-slate-800">
                            <span class="text-slate-500 block">مدة الفيديو:</span>
                            <span class="font-bold text-slate-200">{{ $user->video->duration_seconds ? $user->video->duration_seconds . ' ثانية' : 'غير محدد' }}</span>
                        </div>
                        <div class="bg-slate-900 p-2.5 rounded-lg border border-slate-800">
                            <span class="text-slate-500 block">حجم الملف:</span>
                            <span class="font-bold text-slate-200">{{ $user->video->file_size_mb ? $user->video->file_size_mb . ' MB' : 'غير محدد' }}</span>
                        </div>
                    </div>

                    @if($user->video->rejection_reason)
                        <div class="p-3 bg-red-950/40 border border-red-900/50 rounded-lg text-xs text-red-300">
                            <span class="font-bold block text-red-400 mb-1">سبب الرفض المسجل:</span>
                            <p>{{ $user->video->rejection_reason }}</p>
                        </div>
                    @endif

                    <!-- Video Approval Action Buttons -->
                    <div class="pt-2 border-t border-slate-800 space-y-3">
                        <label class="text-xs font-bold text-slate-300 block">تغيير حالة الاعتماد للفيديو:</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="approveVideo" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition">
                                ✓ قبول / اعتماد الفيديو
                            </button>
                            <button type="button" wire:click="$toggle('editingVideoReason')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-bold text-xs rounded-xl shadow-md transition">
                                ✕ رفض الفيديو
                            </button>
                            <button type="button" wire:click="pendingVideo" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs rounded-xl shadow-md transition">
                                ⏳ قيد المراجعة
                            </button>
                        </div>

                        <!-- Video Rejection Prompt Input -->
                        @if($editingVideoReason || $user->video->status === 'rejected')
                            <div class="mt-3 p-3 bg-slate-900 border border-slate-800 rounded-xl space-y-2">
                                <label class="text-xs text-slate-400 block font-medium">حدد سبب رفض الفيديو:</label>
                                <div class="flex gap-2">
                                    <input type="text" wire:model.defer="videoRejectionReason" placeholder="أدخل سبب سبب عدم قبول الفيديو..." class="w-full text-xs px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-red-500" />
                                    <button type="button" wire:click="rejectVideo" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-xs font-bold rounded-lg shadow whitespace-nowrap">
                                        حفظ الرفض
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- قسم المستندات المرفوعة التفصيلية (Uploaded Documents Grid) -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <span class="p-2 bg-blue-600/20 text-blue-400 rounded-lg">📑</span>
                <h3 class="text-lg font-bold text-white">قائمة المستندات المرفوعة ({{ $user->documents->count() }})</h3>
            </div>
        </div>

        @if($user->documents->isEmpty())
            <div class="text-center py-12 bg-slate-950/50 rounded-xl border border-dashed border-slate-800 text-slate-400">
                <p class="text-sm">لم يتم رفع أي مستندات لهذا المرشح حتى الآن.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($user->documents as $doc)
                    <div class="rounded-xl bg-slate-950 border border-slate-800 p-4 flex flex-col justify-between space-y-4 shadow-md transition hover:border-slate-700">
                        <!-- Document Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">
                                    @if($doc->document_type === 'personal_photo') 📷
                                    @elseif($doc->document_type === 'national_id') 🪪
                                    @elseif($doc->document_type === 'passport') 🛂
                                    @elseif($doc->document_type === 'cv') 📄
                                    @else 📁 @endif
                                </span>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-100">{{ $doc->document_type_name }}</h4>
                                    <span class="text-[11px] text-slate-500">{{ $doc->created_at?->format('Y-m-d H:i') }}</span>
                                </div>
                            </div>

                            <!-- Individual Document Status Badge -->
                            <div>
                                @if($doc->is_approved)
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        ✓ مقبول
                                    </span>
                                @elseif($doc->rejection_reason)
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-500/20 text-red-400 border border-red-500/30">
                                        ✕ مرفوض
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        ⏳ قيد المراجعة
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Document Media Content Preview -->
                        <div class="space-y-3">
                            @if($doc->is_image)
                                <!-- Direct Image View -->
                                <div class="bg-slate-900 p-2 rounded-lg border border-slate-800 flex flex-col items-center justify-center min-h-[160px]">
                                    <img src="{{ $doc->admin_file_url }}" alt="{{ $doc->document_type_name }}" class="max-h-[220px] w-auto object-contain rounded-md shadow-sm transition hover:scale-105" />
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs pt-1">
                                    <a href="{{ $doc->admin_file_url }}" target="_blank" class="inline-flex items-center gap-1 text-blue-400 hover:text-blue-300 font-medium">
                                        👁️ فتح الصورة بالشاشة الكاملة
                                    </a>
                                    <a href="{{ $doc->admin_download_url }}" class="inline-flex items-center gap-1 text-emerald-400 hover:text-emerald-300 font-medium">
                                        ⬇️ تحميل الصورة
                                    </a>
                                </div>
                            @elseif($doc->is_pdf)
                                <!-- PDF / CV View & Embedded Preview -->
                                <div class="bg-slate-900 p-3 rounded-lg border border-slate-800 space-y-3">
                                    <div class="flex items-center gap-3 p-2 bg-slate-950 rounded-lg border border-slate-800">
                                        <div class="p-2 bg-red-500/20 text-red-400 rounded-lg">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                                            </svg>
                                        </div>
                                        <div class="truncate">
                                            <span class="text-xs font-bold text-slate-200 block truncate">{{ basename($doc->file_path) }}</span>
                                            <span class="text-[10px] text-slate-400">مستند PDF / سيرة ذاتية</span>
                                        </div>
                                    </div>

                                    <!-- Embedded PDF iFrame Preview -->
                                    <div class="overflow-hidden rounded-lg border border-slate-800 bg-slate-950">
                                        <iframe src="{{ $doc->admin_file_url }}" class="w-full h-44 rounded-lg" loading="lazy"></iframe>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs pt-1">
                                    <a href="{{ $doc->admin_file_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg transition">
                                        👁️ فتح / عرض الملف (PDF)
                                    </a>
                                    <a href="{{ $doc->admin_download_url }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-emerald-400 font-semibold border border-slate-700 rounded-lg transition">
                                        ⬇️ تحميل الملف
                                    </a>
                                </div>
                            @else
                                <!-- Generic File -->
                                <div class="bg-slate-900 p-4 rounded-lg border border-slate-800 flex items-center justify-between text-xs">
                                    <span class="text-slate-300 font-mono truncate">{{ basename($doc->file_path) }}</span>
                                    <div class="flex gap-2">
                                        <a href="{{ $doc->admin_file_url }}" target="_blank" class="text-blue-400 hover:underline">👁️ فتح</a>
                                        <a href="{{ $doc->admin_download_url }}" class="text-emerald-400 hover:underline">⬇️ تحميل</a>
                                    </div>
                                </div>
                            @endif

                            @if($doc->rejection_reason)
                                <div class="p-2.5 bg-red-950/40 border border-red-900/50 rounded-lg text-xs text-red-300">
                                    <span class="font-bold block text-red-400 mb-0.5">سبب الرفض:</span>
                                    <p>{{ $doc->rejection_reason }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Card Footer: Approval Control Buttons -->
                        <div class="pt-3 border-t border-slate-800/80 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 block">حالة اعتماد هذا المستند:</label>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" wire:click="approveDocument({{ $doc->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                    ✓ قبول
                                </button>
                                <button type="button" wire:click="toggleEditReason({{ $doc->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                    ✕ رفض
                                </button>
                            </div>

                            <!-- Inline Rejection Form -->
                            @if(($editingReason[$doc->id] ?? false) || (!$doc->is_approved && $doc->rejection_reason))
                                <div class="mt-2 p-2 bg-slate-900 border border-slate-800 rounded-lg space-y-1.5">
                                    <input type="text" wire:model.defer="rejectionReasons.{{ $doc->id }}" placeholder="أدخل سبب الرفض هنا..." class="w-full text-xs px-2.5 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-red-500" />
                                    <button type="button" wire:click="rejectDocument({{ $doc->id }})" class="w-full py-1 bg-red-600 hover:bg-red-500 text-white text-xs font-bold rounded-lg shadow transition">
                                        حفظ سبب الرفض
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
