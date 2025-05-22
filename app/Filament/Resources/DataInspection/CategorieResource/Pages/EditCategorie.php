<?php

namespace App\Filament\Resources\DataInspection\CategorieResource\Pages;

use App\Filament\Resources\DataInspection\CategorieResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategorie extends EditRecord
{
    protected static string $resource = CategorieResource::class;

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
