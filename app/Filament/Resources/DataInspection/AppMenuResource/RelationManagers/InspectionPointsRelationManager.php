<?php

namespace App\Filament\Resources\DataInspection\AppMenuResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InspectionPointsRelationManager extends RelationManager
{
    protected static string $relationship = 'points';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('component_id')
                    ->label('Component')
                    ->relationship('component', 'name') // relasi point, ambil kolom 'name' untuk ditampilkan
                    ->searchable()
                    ->preload()
                    ->required(),


                Forms\Components\Select::make('input_type')
                    ->options([
                        'damage' => 'Kerusakan',
                        'text' => 'Text',
                        'number' => 'Number',
                        'account' => 'Account',
                        'date' => 'Date',
                        'image' => 'Image',
                        'imageTOradio' => 'Image to Radio',
                        'radio' => 'Radio Botton',
                        'checkbox' => 'Checkbox',
                        'textarea' => 'Textarea',
                        'select' => 'Select',
                       
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('settings', null)),
                    
                Forms\Components\Toggle::make('is_active')
                                    ->default(true)
                                    ->inline(false), // Label di atas toggle

Forms\Components\Fieldset::make('Settings Configuration')
    ->schema(function (callable $get) {
        $inputType = $get('input_type');
        $schema = [];

        // Common settings for all input types
        $commonSettings = [
            Forms\Components\Toggle::make('settings.is_required')
                ->default(true)
                ->columnSpanFull(),
        ];

        // Settings for number input
        if ($inputType === 'number') {
            $schema[] = Forms\Components\Fieldset::make('Number Configuration')
                ->schema([
                    Forms\Components\TextInput::make('settings.min')
                        ->numeric()
                        ->default(0)
                        ->label('Minimum Value'),
                    Forms\Components\TextInput::make('settings.max')
                        ->numeric()
                        ->default(100)
                        ->label('Maximum Value'),
                    Forms\Components\TextInput::make('settings.step')
                        ->numeric()
                        ->default(9)
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
                        ->default(0)
                        ->label('Minimum Length'),
                    Forms\Components\TextInput::make('settings.max_length')
                        ->numeric()
                        ->default(500)
                        ->label('Maximum Length'),
                    Forms\Components\TextInput::make('settings.placeholder')
                        ->default('Masukkan teks di sini')
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
                        ->default(0)
                        ->label('Minimum Length'),
                    Forms\Components\TextInput::make('settings.max_length')
                        ->numeric()
                        ->default(500)
                        ->label('Maximum Length'),
                    Forms\Components\TextInput::make('settings.pattern')
                        ->default('^[a-zA-Z0-9\s]+$') // Contoh regex untuk alfanumerik
                        ->label('Regex Pattern'),
                    Forms\Components\TextInput::make('settings.placeholder')
                        ->default('Masukkan teks di sini')
                        ->label('Placeholder Text'),
                ])
                ->columns(2);
        }

        // Settings for account input (currency format)
        elseif ($inputType === 'account') {
            $schema[] = Forms\Components\Section::make('Account/Currency Settings')
                ->schema([
                    // Basic Currency Configuration
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('settings.currency_symbol')
                                ->default('Rp')
                                ->label('Currency Symbol')
                                ->placeholder('Rp, $, €, etc.'),
                            
                            Forms\Components\Select::make('settings.thousands_separator')
                                ->options([
                                    ',' => 'Comma (,)',
                                    '.' => 'Dot (.)',
                                    ' ' => 'Space ( )',
                                    '' => 'None',
                                ])
                                ->default(',')
                                ->label('Thousands Separator'),
                        ]),
                    
                    // Input Validation
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('settings.min_value')
                                ->numeric()
                                ->label('Minimum Value')
                                ->default(0)
                                ->placeholder('0'),
                            
                            Forms\Components\TextInput::make('settings.max_value')
                                ->numeric()
                                ->label('Maximum Value')
                                ->default(100000000)
                                ->placeholder('100000000'),
                        ]),
                ])
                ->columns(1);
        }

        // Settings for image input
        elseif ($inputType === 'image') {
            $schema[] = Forms\Components\Section::make('Image Configuration')
                ->schema([
                    Forms\Components\Grid::make(3)
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
                                ->default(['jpg', 'png'])
                                ->placeholder('jpg, png, etc.')
                                ->label('Allowed File Types'),
                        ]),

                    Forms\Components\Section::make('Camera Settings')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Toggle::make('settings.enable_flash')
                                        ->default(true)
                                        ->label('Flash'),

                                    Forms\Components\Toggle::make('settings.enable_camera_switch')
                                        ->default(true)
                                        ->label('Kamera Switch'),
                                ]),

                            Forms\Components\Select::make('settings.camera_aspect_ratio')
                                ->options([
                                    '4:3' => '4:3',
                                    '3:4' => '3:4',
                                    '16:9' => '16:9',
                                    '1:1' => '1:1',
                                ])
                                ->default('4:3')
                                ->label('Camera Aspect Ratio')
                                ->columnSpanFull(),
                        ])
                        ->collapsible(),
                ])
                ->columns(1);
        }

        // Settings for image input
        elseif ($inputType === 'imageTOradio') {
            $schema[] = 
            Forms\Components\Section::make('Image Configuration')
                ->schema([
                    Forms\Components\Grid::make(2)
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
                                ->default(['jpg', 'png'])
                                ->placeholder('jpg, png, etc.')
                                ->label('Allowed File Types'),
                        ]),

                    Forms\Components\Section::make('Camera Settings')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Toggle::make('settings.enable_flash')
                                        ->default(true)
                                        ->label('Flash'),

                                    Forms\Components\Toggle::make('settings.enable_camera_switch')
                                        ->default(true)
                                        ->label('Kamera Switch'),
                                ]),

                            Forms\Components\Select::make('settings.camera_aspect_ratio')
                                ->options([
                                    '4:3' => '4:3',
                                    '3:4' => '3:4',
                                    '16:9' => '16:9',
                                    '1:1' => '1:1',
                                ])
                                ->default('4:3')
                                ->label('Camera Aspect Ratio')
                                ->columnSpanFull(),
                        ])
                        ->collapsible(),
                ])
                ->columns(1);
            $schema[] =
                Forms\Components\Repeater::make('settings.radios')
                ->schema([
                      // Value dan Label
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('value')
                                ->required()
                                ->label('Option Value'),
                            Forms\Components\TextInput::make('label')
                                ->required()
                                ->label('Display Label'),
                        ]),
                    
                    // Textarea Settings dalam Panel
                    Forms\Components\Section::make('Textarea Settings')
                        ->schema([
                            Forms\Components\Toggle::make('settings.show_textarea')
                                ->label('Show Textarea for this value')
                                ->default(false)
                                ->reactive(),
                            
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('settings.min_length')
                                        ->numeric()
                                        ->default(0)
                                        ->label('Min Length')
                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                    Forms\Components\TextInput::make('settings.max_length')
                                        ->numeric()
                                        ->default(500)
                                        ->label('Max Length')
                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                    Forms\Components\TextInput::make('settings.placeholder')
                                        ->default('Masukkan teks di sini')
                                        ->label('Placeholder')
                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                ])
                        ])
                        ->collapsible()
                        ->collapsed(fn (callable $get) => !$get('settings.show_textarea')),
                    
                    // Damage Settings dalam Panel
                    Forms\Components\Section::make('Damage Options')
                        ->schema([
                            Forms\Components\Toggle::make('settings.enable_damage')
                                ->label('Enable Damage Options for this value')
                                ->default(false)
                                ->reactive(),
                            
                            Forms\Components\Repeater::make('settings.damage_options')
                                ->schema([
                                    Forms\Components\TextInput::make('value')
                                        ->required()
                                        ->label('Damage Value'),
                                    Forms\Components\TextInput::make('label')
                                        ->required()
                                        ->label('Damage Label'),
                                ])
                                ->defaultItems(1)
                                ->columns(2)
                                ->visible(fn (callable $get) => $get('settings.enable_damage'))
                                ->label('Damage Options')
                                ->columnSpanFull(),
                        ])
                        ->collapsible()
                        ->collapsed(fn (callable $get) => !$get('settings.enable_damage')),
                ])
                ->columnSpanFull()
                ->columns(1);
        }


        // Settings for select/checkbox/option inputs
        elseif (in_array($inputType, ['select', 'checkbox', 'radio'])) {
            $schema[] = Forms\Components\Repeater::make('settings.radios')
                ->schema([
                    // Value dan Label
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('value')
                                ->required()
                                ->label('Option Value'),
                            Forms\Components\TextInput::make('label')
                                ->required()
                                ->label('Display Label'),
                        ]),
                    
                    // Textarea Settings dalam Panel
                    Forms\Components\Section::make('Textarea Settings')
                        ->schema([
                            Forms\Components\Toggle::make('settings.show_textarea')
                                ->label('Show Textarea for this value')
                                ->default(false)
                                ->reactive(),
                            
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('settings.min_length')
                                        ->numeric()
                                        ->default(0)
                                        ->label('Min Length')
                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                    Forms\Components\TextInput::make('settings.max_length')
                                        ->numeric()
                                        ->default(500)
                                        ->label('Max Length')
                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                    Forms\Components\TextInput::make('settings.placeholder')
                                        ->default('Masukkan teks di sini')
                                        ->label('Placeholder')
                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                ])
                        ])
                        ->collapsible()
                        ->collapsed(fn (callable $get) => !$get('settings.show_textarea')),
                    
                    // Image Upload Settings dalam Panel
                    Forms\Components\Section::make('Image Upload Settings')
                        ->schema([
                            Forms\Components\Toggle::make('settings.show_image_upload')
                                ->label('Show Image Upload for this value')
                                ->default(false)
                                ->reactive(),
                            
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('settings.max_files')
                                        ->numeric()
                                        ->default(1)
                                        ->label('Max Files')
                                        ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                    Forms\Components\TextInput::make('settings.max_size')
                                        ->numeric()
                                        ->default(2048)
                                        ->label('Max Size (KB)')
                                        ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                    Forms\Components\TagsInput::make('settings.allowed_types')
                                        ->placeholder('jpg, png, etc.')
                                        ->default(['jpg', 'png'])
                                        ->label('Allowed File Types')
                                        ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                ]),
                            
                            // Camera Settings dalam Panel
                            Forms\Components\Section::make('Camera Settings')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Toggle::make('settings.enable_flash')
                                                ->default(true)
                                                ->label('Flash')
                                                ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                            Forms\Components\Toggle::make('settings.enable_camera_switch')
                                                ->default(true)
                                                ->label('Kamera Switch')
                                                ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                        ]),
                                    
                                    Forms\Components\Select::make('settings.camera_aspect_ratio')
                                        ->options([
                                            '4:3' => '4:3',
                                            '3:4' => '3:4',
                                            '16:9' => '16:9',
                                            '1:1' => '1:1',
                                        ])
                                        ->default('4:3')
                                        ->label('Camera Aspect Ratio')
                                        ->visible(fn (callable $get) => $get('settings.show_image_upload'))
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn (callable $get) => $get('settings.show_image_upload'))
                                ->collapsible()
                        ])
                        ->collapsible()
                        ->collapsed(fn (callable $get) => !$get('settings.show_image_upload')),
                    
                    // Damage Settings dalam Panel
                    Forms\Components\Section::make('Damage Options')
                        ->schema([
                            Forms\Components\Toggle::make('settings.enable_damage')
                                ->label('Enable Damage Options for this value')
                                ->default(false)
                                ->reactive(),
                            
                            Forms\Components\Repeater::make('settings.damage_options')
                                ->schema([
                                    Forms\Components\TextInput::make('value')
                                        ->required()
                                        ->label('Damage Value'),
                                    Forms\Components\TextInput::make('label')
                                        ->required()
                                        ->label('Damage Label'),
                                ])
                                ->defaultItems(1)
                                ->columns(2)
                                ->visible(fn (callable $get) => $get('settings.enable_damage'))
                                ->label('Damage Options')
                                ->columnSpanFull(),
                        ])
                        ->collapsible()
                        ->collapsed(fn (callable $get) => !$get('settings.enable_damage')),
                ])
                ->defaultItems(2)
                ->columnSpanFull()
                ->columns(1);

            // For checkbox (multiple selection)
            if ($inputType === 'checkbox') {
                $schema[] = Forms\Components\Toggle::make('settings.multiple')
                    ->default(true)
                    ->label('Allow Multiple Selection');
                    
                // Global Damage Settings dalam Panel
                $schema[] = Forms\Components\Section::make('Global Damage Options')
                    ->schema([
                        Forms\Components\Toggle::make('settings.enable_damage_global')
                            ->label('Enable Global Damage Options')
                            ->default(false)
                            ->reactive(),
                        
                        Forms\Components\Repeater::make('settings.damage_options_global')
                            ->schema([
                                Forms\Components\TextInput::make('value')
                                    ->required()
                                    ->label('Damage Value'),
                                Forms\Components\TextInput::make('label')
                                    ->required()
                                    ->label('Damage Label'),
                            ])
                            ->defaultItems(1)
                            ->columns(2)
                            ->visible(fn (callable $get) => $get('settings.enable_damage_global'))
                            ->label('Global Damage Options')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn (callable $get) => !$get('settings.enable_damage_global'));
            }
        }

        // Settings for date input
        elseif ($inputType === 'date') {
            $schema[] = Forms\Components\Fieldset::make('Date Configuration')
                ->schema([
                    Forms\Components\DatePicker::make('settings.min_date')
                        ->default(now()->subYear()) // Default to one year ago
                        ->label('Minimum Date'),
                    Forms\Components\DatePicker::make('settings.max_date')
                        ->default(now()->addYear()) // Default to one year in the future
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
                    
                 Tables\Columns\TextColumn::make('app_menu.name')
                    ->label('Nama Menu')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Point')
                    ->searchable(),

                Tables\Columns\TextColumn::make('input_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'option-Y/N-textarea' => 'warning',
                        'option-T/F-Gambar' => 'success',
                        default => 'primary',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
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

                Tables\Filters\SelectFilter::make('app_menu')
                    ->relationship('app_menu', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->default(true),

            ])
            ->headerActions([
                 Tables\Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $maxOrder = $this->getOwnerRecord()
                        ->points()
                        ->max('order');
                    $data['order'] = ($maxOrder ?? 0) + 1;
                    return $data;
                }),
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
             ->defaultSort('app_menu_id')
            ->defaultSort('order')
            ->reorderable('order');
            
    }

     public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

