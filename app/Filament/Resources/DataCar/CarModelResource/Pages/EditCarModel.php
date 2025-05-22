<?php

namespace App\Filament\Resources\DataCar\CarModelResource\Pages;

use App\Filament\Resources\DataCar\CarModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarModel extends EditRecord
{
    protected static string $resource = CarModelResource::class;

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
