<?php

namespace App\Filament\Resources\DataCar\CarModelResource\Pages;

use App\Filament\Resources\DataCar\CarModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCarModel extends ViewRecord
{
    protected static string $resource = CarModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
