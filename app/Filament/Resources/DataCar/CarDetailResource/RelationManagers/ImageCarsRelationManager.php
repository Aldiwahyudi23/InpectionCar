<?php

namespace App\Filament\Resources\DataCar\CarDetailResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImageCarsRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                 Forms\Components\FileUpload::make('file_path')
                    ->label('File Gambar')
                    ->image()
                    ->directory('car-images') // simpan di storage/app/public/car-images
                    ->required(),

                Forms\Components\Textarea::make('note')
                    ->label('Catatan')
                    ->maxLength(500),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                 Tables\Columns\TextColumn::make('name')->label('Nama'),
               Tables\Columns\ImageColumn::make('file_path')
                ->label('Gambar')
                ->disk('car_images') // Gunakan disk custom
                ->width(50)
                ->height(50),
                Tables\Columns\TextColumn::make('note')->label('Catatan')->limit(30),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
