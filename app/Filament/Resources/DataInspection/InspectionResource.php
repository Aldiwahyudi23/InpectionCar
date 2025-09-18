<?php

namespace App\Filament\Resources\DataInspection;

use App\Filament\Resources\DataInspection\InspectionResource\Pages;
use App\Filament\Resources\DataInspection\InspectionResource\RelationManagers;
use App\InspectionStatus;
use App\Models\DataCar\CarDetail;
use App\Models\DataInspection\Inspection;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PhpParser\Node\Stmt\Label;

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

                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('plate_number')
                    ->label('No. Polisi')
                    ->required()
                    ->maxLength(9),   
                    
                Forms\Components\Select::make('car_id')
                    ->label('Mobil')
                    ->options(CarDetail::with(['brand', 'model', 'type'])
                        ->get()
                        ->mapWithKeys(fn ($car) => [
                            $car->id => "{$car->brand->name} {$car->model->name} {$car->type->name} ({$car->year})"
                        ]))
                    ->searchable()
                    ->nullable(),
              
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draf',
                        'in_progress' => 'Sedang Diproses',
                        'pending' => 'Ditunda',
                        'pending_review' => 'Menunggu Tinjauan',
                        'approved' => 'Disetujui', 
                        'rejected' => 'Ditolak',
                        'revision' => 'Perlu Revisi',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('draft')
                    ->required(),
                                    
                Forms\Components\DateTimePicker::make('inspection_date')
                    ->label('Tanggal Inspeksi')
                    ->required()
                    ->default(now()),

                Forms\Components\TextInput::make('car_name')
                    ->label('Nama Kendaraan'),

                Forms\Components\TextInput::make('file')
                    ->label('File'),
                
                 Forms\Components\RichEditor::make('notes')
                    ->label('Catatan')
                    ->toolbarButtons([
                        'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->fileAttachmentsDirectory('notes') // Folder untuk upload file
                    ->placeholder('Masukkan kesimpulan Komponen di sini...')
                    ->helperText('Kesimpulan tambahan tentang komponen. Format HTML akan dipertahankan.')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('settings')
                    ->label('json')
                    ->rows(3)
                    ->columnSpanFull()
                    

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 Tables\Columns\TextColumn::make('inspection_date')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                    
                 Tables\Columns\TextColumn::make('user.name')
                    ->label('Inspector')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                Tables\Columns\TextColumn::make('plate_number')
                    ->label('No. Polisi')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('car_id')
                    ->label('Mobil'),
                    // ->formatStateUsing(function (Inspection $record) {
                    //     if (!$record->car) return '-';
                    //     return "{$record->car->brand->name} {$record->car->model->name} ({$record->car->year})";
                    // })
                    // ->searchable(query: function (Builder $query, string $search): Builder {
                    //     return $query->whereHas('car', function($q) use ($search) {
                    //         $q->whereHas('brand', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    //           ->orWhereHas('model', fn($q) => $q->where('name', 'like', "%{$search}%"));
                    //     });
                    // }),
                
                Tables\Columns\TextColumn::make('car_name')
                 ->label('Name'),
                    
               Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'in_progress' => 'info',
                        'pending' => 'warning',
                        'pending_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'revision' => 'orange',
                        'completed' => 'green',
                        'cancelled' => 'dark',
                        default => 'gray',
                    }),
                    
                Tables\Columns\IconColumn::make('file')
                    ->label('File'),
                    
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->notes),

                Tables\Columns\TextColumn::make('settings')
                    ->label('settings'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                 Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draf',
                        'in_progress' => 'Sedang Diproses',
                        'pending' => 'Ditunda',
                        'pending_review' => 'Menunggu Tinjauan',
                        'approved' => 'Disetujui', 
                        'rejected' => 'Ditolak',
                        'revision' => 'Perlu Revisi',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name'),
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
