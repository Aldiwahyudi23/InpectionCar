<?php

namespace App\Filament\Resources\DataInspection\InspectionResource\Pages;

use App\Filament\Resources\DataInspection\InspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInspections extends ListRecords
{
    protected static string $resource = InspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
