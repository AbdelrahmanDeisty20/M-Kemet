<?php

namespace App\Observers;

use App\Models\Video;

class VideoObserver
{
    /**
     * Handle the Video "updated" event.
     * Disabled to prevent sending individual push notifications on single video edits.
     */
    public function updated(Video $video): void
    {
        // Notifications are handled via unified bulk actions to avoid user notification spam.
    }
}
