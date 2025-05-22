<?php

namespace App\Filament\Resources\DataCar;

use App\Filament\Resources\DataCar\CarTypeResource\Pages;
use App\Filament\Resources\DataCar\CarTypeResource\RelationManagers;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CarTypeResource extends Resource
{
    protected static ?string $model = CarType::class;

   protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Car Management';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Tipe Mobil';
    protected static ?string $navigationLabel = 'Tipe Mobil';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 Forms\Components\Select::make('car_model_id')
                    ->label('Model Mobil')
                    ->options(CarModel::query()->where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->native(false)
                    ->columnSpanFull(),
                    
                Forms\Components\TextInput::make('name')
                    ->label('Nama Tipe')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->inline(false)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 Tables\Columns\TextColumn::make('model.name')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Tipe')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                 Tables\Filters\SelectFilter::make('car_model_id')
                    ->label('Filter Model')
                    ->relationship('model', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Aktif',
                        false => 'Non-Aktif'
                    ])
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarTypes::route('/'),
            'create' => Pages\CreateCarType::route('/create'),
            'view' => Pages\ViewCarType::route('/{record}'),
            'edit' => Pages\EditCarType::route('/{record}/edit'),
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
