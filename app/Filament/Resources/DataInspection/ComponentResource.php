<?php

namespace App\Filament\Resources\DataInspection;

use App\Filament\Resources\DataInspection\ComponentResource\Pages;
use App\Filament\Resources\DataInspection\ComponentResource\RelationManagers\InspectionPointsRelationManager;
use App\Models\DataInspection\Component;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComponentResource extends Resource
{
    protected static ?string $model = Component::class;

  protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Master Data'; // optional
    protected static ?string $navigationLabel = 'Komponen'; // label di sidebar

    // protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
              ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->inline(false), // Label di atas toggle

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3),


                Forms\Components\Hidden::make('order')
                    ->required()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
           ->columns([
                Tables\Columns\TextColumn::make('row_number')
                    ->label('No')
                    ->formatStateUsing(fn ($record) => $record->row_number),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Komponen')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
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
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
           //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComponents::route('/'),
            'create' => Pages\CreateComponent::route('/create'),
            'view' => Pages\ViewComponent::route('/{record}'),
            'edit' => Pages\EditComponent::route('/{record}/edit'),
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
