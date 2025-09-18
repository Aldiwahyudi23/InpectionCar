<?php

namespace App\Filament\Resources\DataCar\BrandResource\Pages;

use App\Filament\Resources\DataCar\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
