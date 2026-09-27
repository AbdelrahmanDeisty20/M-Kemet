<div class="flex items-center gap-3">
    <img src="{{ asset('images/logo.png') }}" alt="M-Kemet Logo" class="h-10 w-auto object-contain drop-shadow-sm" />
    <span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">
        {{ app()->getLocale() === 'ar' ? 'منصة أم كميت | M-Kemet' : 'M-Kemet Platform' }}
    </span>
</div>
