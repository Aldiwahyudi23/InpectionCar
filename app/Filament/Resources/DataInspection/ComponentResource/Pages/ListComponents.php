<?php

namespace App\Filament\Resources\DataInspection\ComponentResource\Pages;

use App\Filament\Resources\DataInspection\ComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComponents extends ListRecords
{
    protected static string $resource = ComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
