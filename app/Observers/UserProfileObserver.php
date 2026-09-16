<?php

namespace App\Observers;

use App\Models\UserProfile;

class UserProfileObserver
{
    /**
     * Handle the UserProfile "updated" event.
     * Disabled to prevent sending individual push notifications on single profile edits.
     */
    public function updated(UserProfile $userProfile): void
    {
        // Notifications are handled via unified bulk actions to avoid user notification spam.
    }
}
