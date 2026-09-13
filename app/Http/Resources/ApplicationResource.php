<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statusLabel = match ($this->status) {
            'accepted'  => 'تم التوافق / مقبول',
            'rejected'  => 'مرفوض',
            'completed' => 'مكتمل',
            default     => 'طلب تواصل قيد الانتظار',
        };

        return [
            'id'                   => $this->id,
            'company_id'           => $this->company_id,
            'company'              => new CompanyInfoResource($this->whenLoaded('company')),
            'candidate'            => new JobSeekerCardResource($this->whenLoaded('candidateProfile')),
            'status'               => $this->status,
            'status_label'         => $statusLabel,
            'notes'                => $this->notes,
            'created_at'           => $this->created_at?->toIso8601String(),
        ];
    }
}
