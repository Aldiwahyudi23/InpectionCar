<?php

namespace App\Filament\Resources\DataInspection;

use App\Filament\Resources\DataInspection\CategorieResource\InspectionPoinResource\RelationManagers\RelanshipRelationManager;
use App\Filament\Resources\DataInspection\CategorieResource\Pages;
use App\Filament\Resources\DataInspection\CategorieResource\RelationManagers;
use App\Filament\Resources\DataInspection\CategorieResource\RelationManagers\InspectionPointRelationManager;
use App\Models\DataInpection\Categorie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategorieResource extends Resource
{
    protected static ?string $model = Categorie::class;
 protected static ?string $navigationIcon = 'heroicon-o-tag'; // Ikon yang sesuai

    protected static ?string $navigationGroup = 'Inspeksi'; // Grup navigasi

    protected static ?string $modelLabel = 'Kategori Inspeksi';
    protected static ?string $pluralModelLabel = 'Kategori Inspeksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required(),
                Forms\Components\TextInput::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            InspectionPointRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategorie::route('/create'),
            'view' => Pages\ViewCategorie::route('/{record}'),
            'edit' => Pages\EditCategorie::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
