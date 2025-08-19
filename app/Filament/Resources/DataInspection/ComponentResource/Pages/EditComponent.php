<?php

namespace App\Filament\Resources\DataInspection\ComponentResource\Pages;

use App\Filament\Resources\DataInspection\ComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComponent extends EditRecord
{
    protected static string $resource = ComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
