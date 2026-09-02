<?php

namespace App\Filament\Resources\Qualifications\Pages;

use App\Filament\Resources\Qualifications\QualificationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQualification extends ViewRecord
{
    protected static string $resource = QualificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
