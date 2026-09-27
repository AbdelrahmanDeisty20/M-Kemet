<div style="display: flex; align-items: center; gap: 10px; max-height: 38px; overflow: hidden; width: 100%;">
    <img src="{{ asset('images/logo.png') }}" alt="M-Kemet Logo" style="height: 32px; width: 32px; max-height: 32px; max-width: 32px; object-fit: contain; flex-shrink: 0;" />
    <span style="font-size: 15px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" class="text-slate-900 dark:text-white">
        {{ app()->getLocale() === 'ar' ? 'منصة أم كميت | M-Kemet' : 'M-Kemet Platform' }}
    </span>
</div>
