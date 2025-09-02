<?php

namespace App\Filament\Resources\DataInspection\ComponentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class InspectionPointRelationManager extends RelationManager
{
    protected static string $relationship = 'inspection_point';

    public function form(Form $form): Form
    {  
        // Dapatkan ID dari model induk (Category)
        $ownerRecord = $this->getOwnerRecord();

        return $form
            ->schema([
                Card::make()->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('Point Name')
                        ->unique(ignoreRecord: true
                        // , modifyRuleUsing: function (Unique $rule) use ($ownerRecord) {
                        //     return $rule->where('component_id', $ownerRecord->id);
                        //     }
                        ),


                    Forms\Components\Textarea::make('description')
                        ->label('Description'),
                    Forms\Components\Toggle::make('is_active')
                        ->required()
                        ->label('Is Active')
                        ->default(true),
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('inspection_point')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                   // Menggunakan soft-deletes scope
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $maxOrder = $this->getOwnerRecord()
                        ->inspection_point()
                        ->max('order');
                    $data['order'] = ($maxOrder ?? 0) + 1;
                    return $data;
                }),
            ])
            ->actions([
               Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                     Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('app_menu_id')
            ->defaultSort('order')
            ->reorderable('order');
    }

         public static function getEloquentQuery()
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
