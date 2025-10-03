<?php

namespace App\Filament\Resources\DataInspection\AppMenuResource\Pages;

use App\Filament\Resources\DataInspection\AppMenuResource;
use App\Models\DataInspection\AppMenu;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAppMenu extends ViewRecord
{
    protected static string $resource = AppMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            // TAMBAHKAN: Tombol beralih ke menu lain dalam category yang sama
            Actions\ActionGroup::make(
                $this->getOtherMenusActions()
            )
            ->label('Beralih ke Menu Lain')
            ->icon('heroicon-o-arrows-right-left')
            ->color('info')
            ->button()
            ->visible(fn () => $this->hasOtherMenus()),
        ];
    }

    // Method untuk mendapatkan actions menu lain
    private function getOtherMenusActions(): array
    {
        $currentMenu = $this->record;
        
        return AppMenu::where('category_id', $currentMenu->category_id)
            ->where('id', '!=', $currentMenu->id)
            ->get()
            ->map(function (AppMenu $menu) {
                return Actions\Action::make("switch_to_{$menu->id}")
                    ->label($menu->name)
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('gray')
                    ->url(AppMenuResource::getUrl('view', ['record' => $menu->id]))
                    ->openUrlInNewTab(false);
            })
            ->toArray();
    }

    // Method untuk cek apakah ada menu lain
    private function hasOtherMenus(): bool
    {
        return AppMenu::where('category_id', $this->record->category_id)
            ->where('id', '!=', $this->record->id)
            ->exists();
    }


}
