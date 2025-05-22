<?php

namespace App\Filament\Resources\DataCar\CarDetailResource\Pages;

use App\Filament\Resources\DataCar\CarDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCarDetail extends ViewRecord
{
    protected static string $resource = CarDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
