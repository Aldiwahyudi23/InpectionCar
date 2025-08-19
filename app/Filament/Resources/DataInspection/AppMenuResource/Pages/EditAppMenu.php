<?php

namespace App\Filament\Resources\DataInspection\AppMenuResource\Pages;

use App\Filament\Resources\DataInspection\AppMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppMenu extends EditRecord
{
    protected static string $resource = AppMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
