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
        $candidateProfile = $this->candidateProfile;
        $candidateUser    = $candidateProfile?->user;
        $professionModel  = $candidateProfile?->profession ?? $candidateProfile?->professions?->first();
        $professionTitle  = $professionModel?->title ?? $professionModel?->title_ar ?? $candidateProfile?->sub_specialization ?? 'غير محدد';

        $statusLabel = match ($this->status) {
            'accepted'  => 'تم التوافق / مقبول',
            'rejected'  => 'مرفوض',
            'completed' => 'مكتمل',
            default     => 'طلب تواصل قيد الانتظار',
        };

        return [
            'id'                   => $this->id,
            'name'                 => $candidateUser?->name ?? 'غير محدد',
            'profession'           => $professionTitle,
            'request_date'         => $this->created_at?->format('Y-m-d H:i:s') ?? $this->created_at?->toIso8601String(),
            'created_at'           => $this->created_at?->toIso8601String(),
            'status'               => $this->status,
            'status_label'         => $statusLabel,
            'notes'                => $this->notes,
            'candidate'            => new JobSeekerCardResource($this->whenLoaded('candidateProfile')),
        ];
    }
}
