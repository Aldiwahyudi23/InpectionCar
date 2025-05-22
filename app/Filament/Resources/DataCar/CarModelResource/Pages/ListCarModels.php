<?php

namespace App\Filament\Resources\DataCar\CarModelResource\Pages;

use App\Filament\Resources\DataCar\CarModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarModels extends ListRecords
{
    protected static string $resource = CarModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
