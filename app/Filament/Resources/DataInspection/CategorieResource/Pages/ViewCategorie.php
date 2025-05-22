<?php

namespace App\Filament\Resources\DataInspection\CategorieResource\Pages;

use App\Filament\Resources\DataInspection\CategorieResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCategorie extends ViewRecord
{
    protected static string $resource = CategorieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
