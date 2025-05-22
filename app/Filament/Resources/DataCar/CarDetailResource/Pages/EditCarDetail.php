<?php

namespace App\Filament\Resources\DataCar\CarDetailResource\Pages;

use App\Filament\Resources\DataCar\CarDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarDetail extends EditRecord
{
    protected static string $resource = CarDetailResource::class;

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
