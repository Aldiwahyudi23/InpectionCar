<?php

namespace App\Filament\Resources\DataInspection\AppMenuResource\Pages;

use App\Filament\Resources\DataInspection\AppMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppMenus extends ListRecords
{
    protected static string $resource = AppMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
