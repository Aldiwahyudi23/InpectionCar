<?php

namespace App\Filament\Resources\DataInspection\AppMenuResource\Pages;

use App\Filament\Resources\DataInspection\AppMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAppMenu extends ViewRecord
{
    protected static string $resource = AppMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
