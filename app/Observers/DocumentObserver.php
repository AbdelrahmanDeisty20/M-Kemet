<?php

namespace App\Observers;

use App\Models\Document;

class DocumentObserver
{
    /**
     * Handle the Document "updated" event.
     * Disabled to prevent sending individual push notifications on single document edits.
     */
    public function updated(Document $document): void
    {
        // Notifications are handled via unified bulk actions to avoid user notification spam.
    }
}
