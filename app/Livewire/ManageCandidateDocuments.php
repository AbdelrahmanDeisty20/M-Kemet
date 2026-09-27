<?php

namespace App\Livewire;

use App\Models\Document;
use App\Models\User;
use App\Models\Video;
use Filament\Notifications\Notification;
use Livewire\Component;

class ManageCandidateDocuments extends Component
{
    public User $user;

    public array $rejectionReasons = [];
    public array $editingReason = [];
    public ?string $videoRejectionReason = null;
    public bool $editingVideoReason = false;

    public function mount(User $user)
    {
        $this->user = $user->load(['documents', 'video']);

        foreach ($this->user->documents as $doc) {
            $this->rejectionReasons[$doc->id] = $doc->rejection_reason ?? '';
            $this->editingReason[$doc->id] = false;
        }

        if ($this->user->video) {
            $this->videoRejectionReason = $this->user->video->rejection_reason ?? '';
        }
    }

    public function approveDocument($documentId)
    {
        $document = Document::findOrFail($documentId);
        $document->update([
            'is_approved'      => true,
            'rejection_reason' => null,
        ]);

        $this->rejectionReasons[$documentId] = '';
        $this->editingReason[$documentId] = false;
        $this->user->load('documents');

        Notification::make()
            ->title('تم قبول الاعتماد بنجاح')
            ->body('تم تحديث حالة المستند (' . $document->document_type_name . ') إلى مقبول.')
            ->success()
            ->send();
    }

    public function rejectDocument($documentId)
    {
        $document = Document::findOrFail($documentId);
        $reason = trim($this->rejectionReasons[$documentId] ?? '');

        if (empty($reason)) {
            $reason = 'المستند مرفوض / غير واضح';
            $this->rejectionReasons[$documentId] = $reason;
        }

        $document->update([
            'is_approved'      => false,
            'rejection_reason' => $reason,
        ]);

        $this->user->load('documents');

        Notification::make()
            ->title('تم رفض المستند')
            ->body('تم تسجيل سبب الرفض للمستند (' . $document->document_type_name . ').')
            ->warning()
            ->send();
    }

    public function toggleEditReason($documentId)
    {
        $this->editingReason[$documentId] = !($this->editingReason[$documentId] ?? false);
    }

    public function approveVideo()
    {
        if (!$this->user->video) return;

        $this->user->video->update([
            'status'           => 'approved',
            'rejection_reason' => null,
        ]);

        $this->videoRejectionReason = '';
        $this->editingVideoReason = false;
        $this->user->load('video');

        Notification::make()
            ->title('تم اعتماد الفيديو التعريفي')
            ->body('تمت موافقة الإدارة على الفيديو التعريفي للمرشح.')
            ->success()
            ->send();
    }

    public function rejectVideo()
    {
        if (!$this->user->video) return;

        $reason = trim($this->videoRejectionReason ?? '');
        if (empty($reason)) {
            $reason = 'محتوى الفيديو غير مناسب أو غير واضح';
            $this->videoRejectionReason = $reason;
        }

        $this->user->video->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $this->user->load('video');

        Notification::make()
            ->title('تم رفض الفيديو التعريفي')
            ->body('تم تسجيل حالة الرفض للفيديو التعريفي.')
            ->warning()
            ->send();
    }

    public function pendingVideo()
    {
        if (!$this->user->video) return;

        $this->user->video->update([
            'status'           => 'pending',
            'rejection_reason' => null,
        ]);

        $this->user->load('video');

        Notification::make()
            ->title('تم إعادة الفيديو إلى قيد المراجعة')
            ->info()
            ->send();
    }

    public function deleteDocument($documentId)
    {
        $document = Document::findOrFail($documentId);
        $typeLabel = $document->document_type_name;

        \App\Filament\Resources\Documents\DocumentResource::deleteSingleDocument($document);

        unset($this->rejectionReasons[$documentId]);
        unset($this->editingReason[$documentId]);

        $this->user->load('documents');

        Notification::make()
            ->title('تم حذف المستند بنجاح')
            ->body('تم حذف مستند (' . $typeLabel . ') وملفه بدون حذف الحساب.')
            ->success()
            ->send();
    }

    public function deleteVideo()
    {
        if (!$this->user->video) return;

        \App\Filament\Resources\Documents\DocumentResource::deleteSingleVideo($this->user->video);

        $this->videoRejectionReason = null;
        $this->editingVideoReason = false;

        $this->user->load('video');

        Notification::make()
            ->title('تم حذف الفيديو بنجاح')
            ->body('تم حذف الفيديو التعريفي للمرشح دون حذف الحساب.')
            ->success()
            ->send();
    }

    public function deleteAllDocuments()
    {
        \App\Filament\Resources\Documents\DocumentResource::deleteUserDocuments($this->user);

        $this->rejectionReasons = [];
        $this->editingReason = [];
        $this->videoRejectionReason = null;
        $this->editingVideoReason = false;

        $this->user->load(['documents', 'video']);

        Notification::make()
            ->title('تم حذف جميع الوثائق بنجاح')
            ->body('تم حذف كافة المستندات والفيديو لهذا المرشح مع بقاء حسابه فعالاً.')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.manage-candidate-documents');
    }
}
