<?php

namespace App\Filament\Resources\DataInspection;

use App\Filament\Resources\DataInspection\InspectionResource\Pages;
use App\Filament\Resources\DataInspection\InspectionResource\RelationManagers;
use App\InspectionStatus;
use App\Models\DataCar\CarDetail;
use App\Models\DataInpection\Inspection;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

 protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Inspeksi';
    protected static ?string $modelLabel = 'Inspeksi';
    protected static ?string $navigationLabel = 'Data Inspeksi';

    public static function form(Form $form): Form
    {
        return $form
          ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Inspektor')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Forms\Components\Select::make('car_id')
                    ->label('Mobil')
                    ->options(CarDetail::with(['brand', 'model', 'type'])
                        ->get()
                        ->mapWithKeys(fn ($car) => [
                            $car->id => "{$car->brand->name} {$car->model->name} {$car->type->name} ({$car->year})"
                        ]))
                    ->searchable()
                    ->nullable(),
                    
                Forms\Components\DateTimePicker::make('inspection_date')
                    ->label('Tanggal Inspeksi')
                    ->required()
                    ->default(now()),
                    
                Forms\Components\Select::make('status')
                    ->options(InspectionStatus::class)
                    ->native(false)
                    ->required(),
                      Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull()
                    ->nullable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Inspektor')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('carDisplay')
                    ->label('Mobil')
                    ->formatStateUsing(function (Inspection $record) {
                        if (!$record->car) return '-';
                        return "{$record->car->brand->name} {$record->car->model->name} ({$record->car->year})";
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('car', function($q) use ($search) {
                            $q->whereHas('brand', fn($q) => $q->where('name', 'like', "%{$search}%"))
                              ->orWhereHas('model', fn($q) => $q->where('name', 'like', "%{$search}%"));
                        });
                    }),
                     Tables\Columns\TextColumn::make('inspection_date')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => InspectionStatus::from($state)->label()),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                 // Tambahkan filter jika diperlukan
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'selesai' => 'Selesai',
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'dibatalkan' => 'Dibatalkan'
                    ]),
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
            'index' => Pages\ListInspections::route('/'),
            'create' => Pages\CreateInspection::route('/create'),
            'view' => Pages\ViewInspection::route('/{record}'),
            'edit' => Pages\EditInspection::route('/{record}/edit'),
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
