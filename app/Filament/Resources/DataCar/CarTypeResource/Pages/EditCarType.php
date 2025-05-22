<?php

namespace App\Filament\Resources\DataCar\CarTypeResource\Pages;

use App\Filament\Resources\DataCar\CarTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarType extends EditRecord
{
    protected static string $resource = CarTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
