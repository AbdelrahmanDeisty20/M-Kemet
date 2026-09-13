<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyRequestResource extends JsonResource
{
    /**
     * Transform the company request resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $candidateUser = $this->candidateProfile?->user;
        $profession    = $this->candidateProfile?->profession;

        $statusLabel = match ($this->status) {
            'accepted'  => 'تم التوافق / مقبول',
            'rejected'  => 'مرفوض',
            'completed' => 'مكتمل',
            default     => 'طلب تواصل قيد الانتظار',
        };

        return [
            'id'                   => $this->id,
            'name'                 => $candidateUser?->name ?? 'غير محدد',
            'profession'           => $profession?->title_ar ?? $this->candidateProfile?->sub_specialization ?? 'غير محدد',
            'request_date'         => $this->created_at?->format('Y-m-d H:i:s') ?? $this->created_at?->toIso8601String(),
            'created_at'           => $this->created_at?->toIso8601String(),
            'status'               => $this->status,
            'candidate'            => new CompanyRequestResource($this->whenLoaded('candidateProfile')),
            'status_label'         => $statusLabel,
            'notes'                => $this->notes,
        ];
    }
}
