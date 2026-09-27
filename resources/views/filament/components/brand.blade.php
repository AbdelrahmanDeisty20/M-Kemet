<div class="flex items-center gap-2.5 overflow-hidden py-1">
    <img src="{{ asset('images/logo.png') }}" alt="M-Kemet Logo" class="h-8 w-8 max-h-8 max-w-8 object-contain shrink-0" />
    <span class="text-base font-bold tracking-tight text-slate-900 dark:text-white truncate">
        {{ app()->getLocale() === 'ar' ? 'منصة أم كميت | M-Kemet' : 'M-Kemet Platform' }}
    </span>
</div>
