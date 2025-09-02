<?php

namespace App\Filament\Resources\DataInspection;

use App\Filament\Resources\DataInspection\AppMenuResource\Pages;
use App\Filament\Resources\DataInspection\AppMenuResource\RelationManagers;
use App\Filament\Resources\DataInspection\AppMenuResource\RelationManagers\InspectionPointsRelationManager;
use App\Filament\Resources\DataInspection\AppMenuResource\RelationManagers\MenuPointRelationManager;
use App\Models\DataInspection\AppMenu;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppMenuResource extends Resource
{
    protected static ?string $model = AppMenu::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
             ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Menu')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('input_type')
                    ->label('Tipe Input')
                    ->options([
                        'menu' => 'Menu',
                        'damage' => 'Kerusakan',
                    ])
                    ->default('menu')
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->inline(false), // Label di atas toggle
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MenuPointRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppMenus::route('/'),
            'create' => Pages\CreateAppMenu::route('/create'),
            'view' => Pages\ViewAppMenu::route('/{record}'),
            'edit' => Pages\EditAppMenu::route('/{record}/edit'),
        ];
    }
}
