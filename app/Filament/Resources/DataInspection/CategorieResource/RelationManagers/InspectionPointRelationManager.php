<?php

namespace App\Filament\Resources\DataInspection\CategorieResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InspectionPointRelationManager extends RelationManager
{
    protected static string $relationship = 'points';

    public function form(Form $form): Form
    {
        return $form
                 ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('input_type')
                    ->options([
                        'text' => 'Text',
                        'number' => 'Number',
                        'date' => 'Date',
                        'image' => 'Image',
                        'checkbox' => 'Checkbox',
                        'textarea' => 'Textarea',
                        'select' => 'Select',
                        'radio' => 'radio',
                       
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('settings', null)),


                Forms\Components\TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
 Forms\Components\Toggle::make('is_active')
                    ->default(true),

Forms\Components\Fieldset::make('Settings Configuration')
    ->schema(function (callable $get) {
        $inputType = $get('input_type');
        $schema = [];

        // Common settings for all input types
        $commonSettings = [
            Forms\Components\Toggle::make('settings.is_required')
                ->default(false)
                ->columnSpanFull(),
        ];

        // Settings for number input
        if ($inputType === 'number') {
            $schema[] = Forms\Components\Fieldset::make('Number Configuration')
                ->schema([
                    Forms\Components\TextInput::make('settings.min')
                        ->numeric()
                        ->label('Minimum Value'),
                    Forms\Components\TextInput::make('settings.max')
                        ->numeric()
                        ->label('Maximum Value'),
                    Forms\Components\TextInput::make('settings.step')
                        ->numeric()
                        ->label('Step Value'),
                ])
                ->columns(3);
        }

        // Settings for textarea input
        elseif ($inputType === 'textarea') {
            $schema[] = Forms\Components\Fieldset::make('Textarea Configuration')
                ->schema([
                    Forms\Components\TextInput::make('settings.min_length')
                        ->numeric()
                        ->label('Minimum Length'),
                    Forms\Components\TextInput::make('settings.max_length')
                        ->numeric()
                        ->label('Maximum Length'),
                    Forms\Components\TextInput::make('settings.placeholder')
                        ->label('Placeholder Text'),
                ])
                ->columns(3);
        }

        // Settings for text input
        elseif ($inputType === 'text') {
            $schema[] = Forms\Components\Fieldset::make('Text Configuration')
                ->schema([
                    Forms\Components\TextInput::make('settings.min_length')
                        ->numeric()
                        ->label('Minimum Length'),
                    Forms\Components\TextInput::make('settings.max_length')
                        ->numeric()
                        ->label('Maximum Length'),
                    Forms\Components\TextInput::make('settings.pattern')
                        ->label('Regex Pattern'),
                    Forms\Components\TextInput::make('settings.placeholder')
                        ->label('Placeholder Text'),
                ])
                ->columns(2);
        }

        // Settings for image input
        elseif ($inputType === 'image') {
            $schema[] = Forms\Components\Fieldset::make('Image Configuration')
                ->schema([
                    Forms\Components\TextInput::make('settings.max_files')
                        ->numeric()
                        ->default(1)
                        ->label('Maximum Files'),
                    Forms\Components\TextInput::make('settings.max_size')
                        ->numeric()
                        ->default(2048)
                        ->label('Max Size (KB)'),
                    Forms\Components\TagsInput::make('settings.allowed_types')
                        ->placeholder('jpg, png, etc.')
                        ->label('Allowed File Types'),
                ])
                ->columns(3);
        }

        // Settings for select/checkbox/option inputs
        elseif (in_array($inputType, ['select', 'checkbox', 'radio'])) {
                $schema[] = Forms\Components\Repeater::make('settings.radios')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->required()
                            ->label('Option Value'),
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->label('Display Label'),

                        // Pengaturan khusus untuk value ini
                        Forms\Components\Toggle::make('settings.show_textarea')
                            ->label('Show Textarea for this value')
                            ->default(false),
                        Forms\Components\Toggle::make('settings.show_image_upload')
                            ->label('Show Image Upload for this value')
                            ->default(false),

                        // Optional: bisa juga tambahkan pengaturan textarea/image di level ini
                       Forms\Components\TextInput::make('settings.textarea_min_length')
                            ->numeric()
                            ->label('Min Length')
                            ->visible(fn (callable $get) => $get('settings.show_textarea')),
                        Forms\Components\TextInput::make('settings.textarea_max_length')
                            ->numeric()
                            ->label('Max Length')
                            ->visible(fn (callable $get) => $get('settings.show_textarea')),
                        Forms\Components\TextInput::make('settings.textarea_placeholder')
                            ->label('Placeholder')
                            ->visible(fn (callable $get) => $get('settings.show_textarea')),

                         Forms\Components\TextInput::make('settings.image_max_files')
                            ->numeric()
                            ->default(1)
                            ->label('Max Files')
                            ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                        Forms\Components\TextInput::make('settings.image_max_size')
                            ->numeric()
                            ->default(2048)
                            ->label('Max Size (KB)')
                            ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                        Forms\Components\TagsInput::make('settings.image_allowed_types')
                            ->placeholder('jpg, png, etc.')
                            ->label('Allowed Types')
                            ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                    ])
                    ->defaultItems(2)
                    ->columnSpanFull()
                    ->columns(2);

            // For checkbox (multiple selection)
            if ($inputType === 'checkbox') {
                $schema[] = Forms\Components\Toggle::make('settings.multiple')
                    ->default(true)
                    ->label('Allow Multiple Selection');
            }

        }

        // Settings for date input
        elseif ($inputType === 'date') {
            $schema[] = Forms\Components\Fieldset::make('Date Configuration')
                ->schema([
                    Forms\Components\DatePicker::make('settings.min_date')
                        ->label('Minimum Date'),
                    Forms\Components\DatePicker::make('settings.max_date')
                        ->label('Maximum Date'),
                ])
                ->columns(2);
        }

        return array_merge($commonSettings, $schema);
    })
    ->columnSpanFull()
                
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Manager')
            ->columns([
                 Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('input_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'option-Y/N-textarea' => 'warning',
                        'option-T/F-Gambar' => 'success',
                        default => 'primary',
                    }),


                Tables\Columns\TextColumn::make('order')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
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

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->default(true),

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            ])
             ->defaultSort('category_id')
            ->defaultSort('order');
            
    }

     public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
