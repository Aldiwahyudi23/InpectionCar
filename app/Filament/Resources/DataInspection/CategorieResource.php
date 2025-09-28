<?php

namespace App\Filament\Resources\DataInspection;


use App\Filament\Resources\DataInspection\CategorieResource\Pages;
use App\Filament\Resources\DataInspection\CategoriesResource\RelationManagers\AppMenuRelationManager;
use App\Models\DataInspection\Categorie;
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

    protected static ?string $navigationGroup = 'Master Data'; // optional

    protected static ?string $modelLabel = 'Kategori Inspeksi';
    protected static ?string $pluralModelLabel = 'Kategori Inspeksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255)
                      // Tambahkan aturan validasi unique di sini
                    ->unique(ignoreRecord: true),

                Forms\Components\Hidden::make('order')
                    ->required()
                    ->default(0),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->inline(false), // Label di atas toggle

                Forms\Components\Select::make('settings.menu_model')
                    ->label('Model Menu')
                    ->options([
                        'horizontal' => 'Slid Kesamping',
                        'vertical'   => 'Tombol ko Menu',
                    ])
                    ->required()
                    ->reactive(), // biar bisa trigger perubahan ke input lain

                Forms\Components\Select::make('settings.position')
                    ->label('Posisi Menu')
                    ->options(function (callable $get) {
                        if ($get('settings.menu_model') === 'vertical') {
                            return [
                                'top-left'     => 'Atas Kiri',
                                'top-right'    => 'Atas Kanan',
                                'bottom-left'  => 'Bawah Kiri',
                                'bottom-right' => 'Bawah Kanan',
                            ];
                        }

                        // default horizontal
                        return [
                            'top'    => 'Atas',
                            'bottom' => 'Bawah',
                        ];
                    })
                    ->required()
                    ->reactive(),

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
                    ->label('Nama Kategori')
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
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AppMenuRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            // 'create' => Pages\CreateCategorie::route('/create'),
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
