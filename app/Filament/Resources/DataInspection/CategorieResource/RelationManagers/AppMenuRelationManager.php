<?php

namespace App\Filament\Resources\DataInspection\CategoriesResource\RelationManagers;

use App\Filament\Resources\DataInspection\AppMenuResource;
use App\Models\DataInspection\AppMenu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class AppMenuRelationManager extends RelationManager
{
    protected static string $relationship = 'AppMenu';

    public function form(Form $form): Form
    {

         // Dapatkan ID dari model induk (Category)
        $ownerRecord = $this->getOwnerRecord();

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Menu')
                    ->required()
                    ->maxLength(255)
                     ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) use ($ownerRecord) {
                    return $rule->where('category_id', $ownerRecord->id);
                    }),

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




    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
             ->columns([
               

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Menu')
                    ->sortable(),

                Tables\Columns\TextColumn::make('input_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'menu' => 'success',
                        'damage' => 'warning',
                        default => 'success',
                    }),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
              ->headerActions([
                 Tables\Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $maxOrder = $this->getOwnerRecord()
                        ->appMenu()
                        ->max('order');
                    $data['order'] = ($maxOrder ?? 0) + 1;
                    return $data;
                }),
            ])

             ->actions([
            Tables\Actions\ViewAction::make()
                ->url(fn (AppMenu $record): string => AppMenuResource::getUrl('view', ['record' => $record])),
                
            Tables\Actions\EditAction::make()
                ->url(fn (AppMenu $record): string => AppMenuResource::getUrl('edit', ['record' => $record])),
        ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
    

     public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
