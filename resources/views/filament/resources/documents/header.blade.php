<div class="fi-header flex flex-col gap-y-4 pb-4">
    <!-- Breadcrumbs & Title Section (Full Width Top) -->
    <div class="space-y-1">
        <x-filament-panels::breadcrumbs :breadcrumbs="$breadcrumbs" />
        
        <div class="flex items-center gap-3 pt-1">
            <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                {{ $title }}
            </h1>
        </div>
    </div>

    <!-- Actions Section (Full Width Bottom, Flex Wrapped) -->
    <div class="flex flex-wrap items-center gap-3 pt-2">
        <x-filament-actions::actions :actions="$actions" />
    </div>
</div>
