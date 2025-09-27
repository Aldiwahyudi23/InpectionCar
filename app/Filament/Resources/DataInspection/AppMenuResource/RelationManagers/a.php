<?php

namespace App\Filament\Resources\DataInspection\AppMenuResource\RelationManagers;

use App\Models\DataInspection\InspectionPoint;
use App\Models\DataInspection\MenuPoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class a extends RelationManager
{
    protected static string $relationship = 'menu_point';

    // TAMBAHKAN PROPERTIES INI DI DALAM RELATION MANAGER
public $selectedPoints = [];
public $selectAll = false;
public $searchQuery = '';

// METHOD UNTUK HANDLE SELECT ALL
public function updatedSelectAll($value)
{
    if ($value) {
        $ownerRecord = $this->getOwnerRecord();
        $usedInspectionPointIds = MenuPoint::where('app_menu_id', $ownerRecord->id)
            ->pluck('inspection_point_id');
        
        $this->selectedPoints = InspectionPoint::whereNotIn('id', $usedInspectionPointIds)
            ->pluck('id')
            ->toArray();
    } else {
        $this->selectedPoints = [];
    }
}

    public function form(Form $form): Form
    {

         // Mendapatkan instance model induk (AppMenu) dari relation manager
        $ownerRecord = $this->getOwnerRecord();

        return $form
            ->schema([
                Forms\Components\Select::make('inspection_point_id')
                    ->label('Inspection Point')
                    ->relationship('inspection_point', 'name', function (Builder $query) use ($ownerRecord) {
                        $categoryId = $ownerRecord->category_id;

                        $usedInspectionPointIds = MenuPoint::whereHas('app_menu', function ($query) use ($categoryId) {
                                $query->where('category_id', $categoryId);
                            })
                            ->pluck('inspection_point_id');

                        $query->whereNotIn('id', $usedInspectionPointIds);
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => InspectionPoint::find($value)?->name) // 👈 ini kunci
                    ->searchable()
                    ->preload()
                    ->required(),


                Forms\Components\Select::make('input_type')
                    ->label('Tipe Input')
                    ->options([
                        'damage' => 'Kerusakan',
                        'text' => 'Text',
                        'number' => 'Number',
                        'account' => 'Account',
                        'date' => 'Date',
                        'image' => 'Image',
                        'imageTOradio' => 'Image to Radio',
                        'radio' => 'Radio Button',
                        'checkbox' => 'Checkbox',
                        'textarea' => 'Textarea',
                        'select' => 'Select',
                    ])
                    ->required()
                    ->reactive()
                    ->default('text')
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('settings', null);
                        
                        // Set default values based on input type
                        $defaults = $this->getDefaultSettings($state);
                        foreach ($defaults as $key => $value) {
                            $set("settings.{$key}", $value);
                        }
                    }),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->inline(false),

                Forms\Components\Toggle::make('is_default')
                    ->label('Tampilkan Point')
                    ->default(true)
                    ->inline(false),

                Forms\Components\Fieldset::make('Konfigurasi Settings')
                    ->schema(function (callable $get) {
                        $inputType = $get('input_type');
                        $schema = [];

                        // Common settings for all input types
                        $commonSettings = [
                            Forms\Components\Toggle::make('settings.is_required')
                                ->label('Wajib Diisi')
                                ->default(true)
                                ->columnSpanFull(),
                        ];

                        // Settings for number input
                        if ($inputType === 'number') {
                            $schema[] = Forms\Components\Fieldset::make('Konfigurasi Number')
                                ->schema([
                                    Forms\Components\TextInput::make('settings.min')
                                        ->label('Nilai Minimum')
                                        ->numeric()
                                        ->default(0),
                                    Forms\Components\TextInput::make('settings.max')
                                        ->label('Nilai Maksimum')
                                        ->numeric()
                                        ->default(100),
                                    Forms\Components\TextInput::make('settings.step')
                                        ->label('Step Value')
                                        ->numeric()
                                        ->default(1),
                                ])
                                ->columns(3);
                        }

                        // Settings for textarea input
                        elseif ($inputType === 'textarea') {
                            $schema[] = Forms\Components\Fieldset::make('Konfigurasi Textarea')
                                ->schema([
                                    Forms\Components\TextInput::make('settings.min_length')
                                        ->label('Panjang Minimum')
                                        ->numeric()
                                        ->default(0),
                                    Forms\Components\TextInput::make('settings.max_length')
                                        ->label('Panjang Maksimum')
                                        ->numeric()
                                        ->default(500),
                                    Forms\Components\TextInput::make('settings.placeholder')
                                        ->label('Placeholder')
                                        ->default('Masukkan teks di sini'),
                                ])
                                ->columns(3);
                        }

                        // Settings for text input
                        elseif ($inputType === 'text') {
                            $schema[] = Forms\Components\Fieldset::make('Konfigurasi Text')
                                ->schema([
                                    Forms\Components\TextInput::make('settings.min_length')
                                        ->label('Panjang Minimum')
                                        ->numeric()
                                        ->default(0),
                                    Forms\Components\TextInput::make('settings.max_length')
                                        ->label('Panjang Maksimum')
                                        ->numeric()
                                        ->default(255),
                                    // Forms\Components\TextInput::make('settings.pattern')
                                    //     ->label('Pattern Regex')
                                    //     ->default('^[a-zA-Z0-9\s]+$'),
                                Forms\Components\Select::make('settings.text_transform')
                                        ->label('Format Text')
                                        ->options([
                                            'none' => 'Biasa',
                                            'uppercase' => 'Huruf Besar Semua',
                                            'lowercase' => 'Huruf Kecil Semua',
                                            'capitalize' => 'Awal Kata Besar',
                                        ])
                                        ->default('none')
                                        ->required(),


                                    Forms\Components\Toggle::make('settings.allow_space')
                                        ->label('Boleh Pakai Spasi')
                                        ->default(true),


                                    Forms\Components\TextInput::make('settings.placeholder')
                                        ->label('Placeholder')
                                        ->default('Masukkan teks di sini'),
                                ])
                                ->columns(2);
                        }

                        // Settings for account input (currency format)
                        elseif ($inputType === 'account') {
                            $schema[] = Forms\Components\Section::make('Pengaturan Account/Currency')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('settings.currency_symbol')
                                                ->label('Simbol Currency')
                                                ->default('Rp')
                                                ->placeholder('Rp, $, €, etc.'),
                                            
                                            Forms\Components\Select::make('settings.thousands_separator')
                                                ->label('Pemisah Ribuan')
                                                ->options([
                                                    ',' => 'Koma (,)',
                                                    '.' => 'Titik (.)',
                                                    ' ' => 'Spasi ( )',
                                                    '' => 'Tidak Ada',
                                                ])
                                                ->default(','),
                                        ]),
                                    
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('settings.min_value')
                                                ->label('Nilai Minimum')
                                                ->numeric()
                                                ->default(0),
                                            
                                            Forms\Components\TextInput::make('settings.max_value')
                                                ->label('Nilai Maksimum')
                                                ->numeric()
                                                ->default(100000000),
                                        ]),
                                ])
                                ->columns(1);
                        }

                        // Settings for image input
                        elseif ($inputType === 'image') {
                            $schema[] = Forms\Components\Section::make('Konfigurasi Image')
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\TextInput::make('settings.max_files')
                                                ->label('File Maksimum')
                                                ->numeric()
                                                ->default(1),

                                            Forms\Components\TextInput::make('settings.max_size')
                                                ->label('Ukuran Maks (KB)')
                                                ->numeric()
                                                ->default(2048),

                                            Forms\Components\TagsInput::make('settings.allowed_types')
                                                ->label('Tipe File Diizinkan')
                                                ->default(['jpg', 'png', 'jpeg'])
                                                ->placeholder('jpg, png, jpeg'),
                                        ]),

                                    Forms\Components\Section::make('Pengaturan Kamera')
                                        ->schema([
                                            Forms\Components\Grid::make(2)
                                                ->schema([
                                                    Forms\Components\Toggle::make('settings.enable_flash')
                                                        ->label('Flash')
                                                        ->default(true),

                                                    Forms\Components\Toggle::make('settings.enable_camera_switch')
                                                        ->label('Switch Kamera')
                                                        ->default(true),
                                                ]),

                                            Forms\Components\Select::make('settings.camera_aspect_ratio')
                                                ->label('Aspect Ratio Kamera')
                                                ->options([
                                                    '4:3' => '4:3',
                                                    '3:4' => '3:4',
                                                    '16:9' => '16:9',
                                                    '1:1' => '1:1',
                                                ])
                                                ->default('4:3')
                                                ->columnSpanFull(),
                                        ])
                                        ->collapsible(),
                                ])
                                ->columns(1);
                        }

                        // Settings for imageTOradio input
                        elseif ($inputType === 'imageTOradio') {
                            $schema[] = Forms\Components\Section::make('Konfigurasi Image')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('settings.max_files')
                                                ->label('File Maksimum')
                                                ->numeric()
                                                ->default(1),

                                            Forms\Components\TextInput::make('settings.max_size')
                                                ->label('Ukuran Maks (KB)')
                                                ->numeric()
                                                ->default(2048),

                                            Forms\Components\TagsInput::make('settings.allowed_types')
                                                ->label('Tipe File Diizinkan')
                                                ->default(['jpg', 'png', 'jpeg'])
                                                ->placeholder('jpg, png, jpeg'),
                                        ]),

                                    Forms\Components\Section::make('Pengaturan Kamera')
                                        ->schema([
                                            Forms\Components\Grid::make(2)
                                                ->schema([
                                                    Forms\Components\Toggle::make('settings.enable_flash')
                                                        ->label('Flash')
                                                        ->default(true),

                                                    Forms\Components\Toggle::make('settings.enable_camera_switch')
                                                        ->label('Switch Kamera')
                                                        ->default(true),
                                                ]),

                                            Forms\Components\Select::make('settings.camera_aspect_ratio')
                                                ->label('Aspect Ratio Kamera')
                                                ->options([
                                                    '4:3' => '4:3',
                                                    '3:4' => '3:4',
                                                    '16:9' => '16:9',
                                                    '1:1' => '1:1',
                                                ])
                                                ->default('4:3')
                                                ->columnSpanFull(),
                                        ])
                                        ->collapsible(),
                                ])
                                ->columns(1);
                            
                            $schema[] = Forms\Components\Repeater::make('settings.radios')
                                ->label('Opsi Radio')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('value')
                                                ->required()
                                                ->label('Nilai Opsi')
                                                ->default(fn ($state) => $state ?? 'opsi1'),
                                            Forms\Components\TextInput::make('label')
                                                ->required()
                                                ->label('Label Tampilan')
                                                ->default(fn ($state) => $state ?? 'Opsi 1'),
                                        ]),
                                    
                                    Forms\Components\Section::make('Pengaturan Textarea')
                                        ->schema([
                                            Forms\Components\Toggle::make('settings.show_textarea')
                                                ->label('Tampilkan Textarea')
                                                ->default(false)
                                                ->reactive(),
                                            
                                            Forms\Components\Grid::make(3)
                                                ->schema([
                                                    Forms\Components\TextInput::make('settings.min_length')
                                                        ->label('Panjang Min')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                    Forms\Components\TextInput::make('settings.max_length')
                                                        ->label('Panjang Maks')
                                                        ->numeric()
                                                        ->default(500)
                                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                    Forms\Components\TextInput::make('settings.placeholder')
                                                        ->label('Placeholder')
                                                        ->default('Masukkan teks di sini')
                                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                ])
                                        ])
                                        ->collapsible()
                                        ->collapsed(fn (callable $get) => !$get('settings.show_textarea')),
                                    
                                        // Ganti bagian damage options dengan ini:
                                        Forms\Components\Section::make('Opsi Kerusakan')
                                            ->schema([
                                                Forms\Components\Toggle::make('settings.enable_damage')
                                                    ->label('Aktifkan Opsi Kerusakan')
                                                    ->default(false)
                                                    ->reactive(),
                                                
                                                Forms\Components\Select::make('settings.damage_category')
                                                    ->label('Pilih Kategori Kerusakan')
                                                    ->options(function () {
                                                        $categories = $this->getDamageCategories();
                                                        return collect($categories)->mapWithKeys(fn($cat, $key) => [$key => $cat['label']])->toArray();
                                                    })
                                                    ->default('exterior')
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        // Otomatis isi damage_options ketika kategori berubah
                                                        $categories = $this->getDamageCategories();
                                                        $damageOptions = $categories[$state]['options'] ?? [];
                                                        // Tambahkan field is_selected untuk setiap option
                                                        $optionsWithSelection = array_map(function ($option) {
                                                            return $option + ['is_selected' => true];
                                                        }, $damageOptions);
                                                        $set('settings.damage_options', $optionsWithSelection);
                                                    })
                                                    ->visible(fn (callable $get) => $get('settings.enable_damage')),
                                                
                                                Forms\Components\Repeater::make('settings.damage_options')
                                                    ->label('Daftar Opsi Kerusakan')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('value')
                                                            ->label('Deskripsi Kerusakan (Detail)')
                                                            ->required()
                                                            ->columnSpan(2),
                                                        
                                                        Forms\Components\TextInput::make('label')
                                                            ->label('Label Singkat')
                                                            ->required(),
                                                    ])
                                                    ->defaultItems(0)
                                                    ->columns(4)
                                                    ->visible(fn (callable $get) => $get('settings.enable_damage'))
                                                    ->columnSpanFull()
                                                    ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                                        // Otomatis isi saat form load
                                                        $category = $get('../../damage_category');
                                                        if (empty($state) && $category) {
                                                            $categories = $this->getDamageCategories();
                                                            $damageOptions = $categories[$category]['options'] ?? [];
                                                            // Tambahkan field is_selected untuk setiap option
                                                            $optionsWithSelection = array_map(function ($option) {
                                                                return $option + ['is_selected' => true];
                                                            }, $damageOptions);
                                                            $set('settings.damage_options', $optionsWithSelection);
                                                        }
                                                    })
                                                    ->createItemButtonLabel('Tambah Manual')
                                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                                        // Hanya simpan yang dicentang is_selected = true
                                                        return array_filter($data, function ($item) {
                                                            return $item['is_selected'] === true;
                                                        });
                                                    })

                                            ])
                                            ->collapsible()
                                            ->collapsed(fn (callable $get) => !$get('settings.enable_damage')),

                                ])
                                ->defaultItems(2)
                                ->createItemButtonLabel('Tambah Opsi Radio') // Tombol tambah di bawah
                                ->columnSpanFull()
                                ->columns(1);
                        }

                        // Settings for select/checkbox/radio inputs
                        elseif (in_array($inputType, ['select', 'checkbox', 'radio'])) {
                            $schema[] = Forms\Components\Repeater::make('settings.radios')
                                ->label('Daftar Opsi')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('value')
                                                ->required()
                                                ->label('Nilai Opsi')
                                                ->default('opsi1'),
                                            Forms\Components\TextInput::make('label')
                                                ->required()
                                                ->label('Label Tampilan')
                                                ->default('Opsi 1'),
                                        ]),
                                    
                                    Forms\Components\Section::make('Pengaturan Textarea')
                                        ->schema([
                                            Forms\Components\Toggle::make('settings.show_textarea')
                                                ->label('Tampilkan Textarea')
                                                ->default(false)
                                                ->reactive(),
                                            
                                            Forms\Components\Grid::make(3)
                                                ->schema([
                                                    Forms\Components\TextInput::make('settings.min_length')
                                                        ->label('Panjang Min')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                    Forms\Components\TextInput::make('settings.max_length')
                                                        ->label('Panjang Maks')
                                                        ->numeric()
                                                        ->default(500)
                                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                    Forms\Components\TextInput::make('settings.placeholder')
                                                        ->label('Placeholder')
                                                        ->default('Masukkan teks di sini')
                                                        ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                ])
                                        ])
                                        ->collapsible()
                                        ->collapsed(fn (callable $get) => !$get('settings.show_textarea')),
                                    
                                    Forms\Components\Section::make('Pengaturan Upload Gambar')
                                        ->schema([
                                            Forms\Components\Toggle::make('settings.show_image_upload')
                                                ->label('Tampilkan Upload Gambar')
                                                ->default(false)
                                                ->reactive(),
                                            
                                            Forms\Components\Grid::make(3)
                                                ->schema([
                                                    Forms\Components\TextInput::make('settings.max_files')
                                                        ->label('File Maksimum')
                                                        ->numeric()
                                                        ->default(1)
                                                        ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                                    Forms\Components\TextInput::make('settings.max_size')
                                                        ->label('Ukuran Maks (KB)')
                                                        ->numeric()
                                                        ->default(2048)
                                                        ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                                    Forms\Components\TagsInput::make('settings.allowed_types')
                                                        ->label('Tipe File Diizinkan')
                                                        ->default(['jpg', 'png', 'jpeg'])
                                                        ->placeholder('jpg, png, jpeg')
                                                        ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                                ]),
                                            
                                            Forms\Components\Section::make('Pengaturan Kamera')
                                                ->schema([
                                                    Forms\Components\Grid::make(2)
                                                        ->schema([
                                                            Forms\Components\Toggle::make('settings.enable_flash')
                                                                ->label('Flash')
                                                                ->default(true)
                                                                ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                                            Forms\Components\Toggle::make('settings.enable_camera_switch')
                                                                ->label('Switch Kamera')
                                                                ->default(true)
                                                                ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                                        ]),
                                                    
                                                    Forms\Components\Select::make('settings.camera_aspect_ratio')
                                                        ->label('Aspect Ratio Kamera')
                                                        ->options([
                                                            '4:3' => '4:3',
                                                            '3:4' => '3:4',
                                                            '16:9' => '16:9',
                                                            '1:1' => '1:1',
                                                        ])
                                                        ->default('4:3')
                                                        ->visible(fn (callable $get) => $get('settings.show_image_upload'))
                                                        ->columnSpanFull(),
                                                ])
                                                ->visible(fn (callable $get) => $get('settings.show_image_upload'))
                                                ->collapsible()
                                        ])
                                        ->collapsible()
                                        ->collapsed(fn (callable $get) => !$get('settings.show_image_upload')),
                                    
                                // Ganti bagian damage options dengan ini:
                                    Forms\Components\Section::make('Opsi Kerusakan')
                                        ->schema([
                                            Forms\Components\Toggle::make('settings.enable_damage')
                                                ->label('Aktifkan Opsi Kerusakan')
                                                ->default(false)
                                                ->reactive(),
                                            
                                            Forms\Components\Select::make('settings.damage_category')
                                                ->label('Pilih Kategori Kerusakan')
                                                ->options(function () {
                                                    $categories = $this->getDamageCategories();
                                                    return collect($categories)->mapWithKeys(fn($cat, $key) => [$key => $cat['label']])->toArray();
                                                })
                                                ->default('exterior')
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    // Otomatis isi damage_options ketika kategori berubah
                                                    $categories = $this->getDamageCategories();
                                                    $damageOptions = $categories[$state]['options'] ?? [];
                                                    // Tambahkan field is_selected untuk setiap option
                                                    $optionsWithSelection = array_map(function ($option) {
                                                        return $option + ['is_selected' => true];
                                                    }, $damageOptions);
                                                    $set('settings.damage_options', $optionsWithSelection);
                                                })
                                                ->visible(fn (callable $get) => $get('settings.enable_damage')),
                                            
                                            Forms\Components\Repeater::make('settings.damage_options')
                                                ->label('Daftar Opsi Kerusakan')
                                                ->schema([
                                                    Forms\Components\TextInput::make('value')
                                                        ->label('Deskripsi Kerusakan (Detail)')
                                                        ->required()
                                                        ->columnSpan(2),
                                                    
                                                    Forms\Components\TextInput::make('label')
                                                        ->label('Label Singkat')
                                                        ->required(),
                                                ])
                                                ->defaultItems(0)
                                                ->columns(4)
                                                ->visible(fn (callable $get) => $get('settings.enable_damage'))
                                                ->columnSpanFull()
                                                ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                                    // Otomatis isi saat form load
                                                    $category = $get('../../damage_category');
                                                    if (empty($state) && $category) {
                                                        $categories = $this->getDamageCategories();
                                                        $damageOptions = $categories[$category]['options'] ?? [];
                                                        // Tambahkan field is_selected untuk setiap option
                                                        $optionsWithSelection = array_map(function ($option) {
                                                            return $option + ['is_selected' => true];
                                                        }, $damageOptions);
                                                        $set('settings.damage_options', $optionsWithSelection);
                                                    }
                                                })
                                                ->createItemButtonLabel('Tambah Manual')
                                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                                ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                                    // Hanya simpan yang dicentang is_selected = true
                                                    return array_filter($data, function ($item) {
                                                        return $item['is_selected'] === true;
                                                    });
                                                })

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
                                    ->label('Izinkan Multiple Selection')
                                    ->default(true);
                                    
                                $schema[] = Forms\Components\Section::make('Opsi Kerusakan Global')
                                    ->schema([
                                        Forms\Components\Toggle::make('settings.enable_damage_global')
                                            ->label('Aktifkan Opsi Kerusakan Global')
                                            ->default(false)
                                            ->reactive(),
                                        
                                        Forms\Components\Repeater::make('settings.damage_options_global')
                                            ->label('Daftar Opsi Kerusakan Global')
                                            ->schema([
                                                Forms\Components\TextInput::make('value')
                                                    ->required()
                                                    ->label('Nilai Kerusakan')
                                                    ->default('rusak_global1'),
                                                Forms\Components\TextInput::make('label')
                                                    ->required()
                                                    ->label('Label Kerusakan')
                                                    ->default('Rusak Global 1'),
                                            ])
                                            ->defaultItems(1)
                                            ->columns(2)
                                            ->visible(fn (callable $get) => $get('settings.enable_damage_global'))
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(fn (callable $get) => !$get('settings.enable_damage_global'));
                            }
                        }

                        // Settings for date input
                        elseif ($inputType === 'date') {
                            $schema[] = Forms\Components\Fieldset::make('Konfigurasi Date')
                                ->schema([
                                    Forms\Components\DatePicker::make('settings.min_date')
                                        ->label('Tanggal Minimum')
                                        ->default(now()->subYear()),
                                    Forms\Components\DatePicker::make('settings.max_date')
                                        ->label('Tanggal Maksimum')
                                        ->default(now()->addYear()),
                                ])
                                ->columns(2);
                        }

                        return array_merge($commonSettings, $schema);
                    })
                    ->columnSpanFull()
            ]);
    }

    protected function getDamageCategories(): array
    {
        return [
            'exterior' => [
                'label' => 'Kerusakan Eksterior',
                'options' => [
                    ['value' => 'Sudah pernah dicat ulang', 'label' => 'Repaint'],
                    ['value' => 'Terdapat goresan pada permukaan cat', 'label' => 'Baret'],
                    ['value' => 'Permukaan penyok akibat benturan', 'label' => 'Penyok'],
                    ['value' => 'Cat mengelupas', 'label' => 'Cat Mengelupas'],
                    ['value' => 'Warna tidak sama dengan bagian lain', 'label' => 'Warna Beda'],
                    ['value' => 'Permukaan berlubang', 'label' => 'Lubang'],
                    ['value' => 'Sudah dempulan', 'label' => 'Dempul'],
                    ['value' => 'Piting/celah tidak persisi', 'label' => 'piting'],
                    ['value' => 'Cet meler', 'label' => 'Meler'],
                    ['value' => 'Cet bintik', 'label' => 'Bintik'],
                ]
            ],
            'interior' => [
                'label' => 'Kerusakan Interior', 
                'options' => [
                    ['value' => 'Jok sobek atau robek', 'label' => 'Jok Sobek'],
                    ['value' => 'Dashboard retak atau pecah', 'label' => 'Dashboard Retak'],
                    ['value' => 'Karpet kotor atau berlubang', 'label' => 'Karpet Rusak'],
                    ['value' => 'Plastik interior patah', 'label' => 'Plastik Patah'],
                    ['value' => 'Kenop atau tombol lepas', 'label' => 'Tombol Lepas'],
                ]
            ],
            'mesin' => [
                'label' => 'Kerusakan Mesin',
                'options' => [
                    ['value' => 'Suara terdengar Kasar', 'label' => 'Kasar'],
                    ['value' => 'Mesin Ngelitrik kurang Tenaga', 'label' => 'Ngelitrik'],
                    ['value' => 'Ada bunyi di bagian luar bearing/van-belt', 'label' => 'Bunyi'],
                    ['value' => 'Ada bagian yang terlihat basah oli Rembes', 'label' => 'Rembes'],
                    ['value' => 'Mesin sulit start', 'label' => 'Sulit Start'],
                    ['value' => 'Asap putih dari knalpot', 'label' => 'Asap Putih'],
                    ['value' => 'Mesin Goyang tidak stabil atau Pincang ', 'label' => 'Pincang'],
                    ['value' => 'Idle / Lamsam tidak stabil ', 'label' => 'Idle error'],
                    ['value' => 'Mesin mengalami panas berlebih', 'label' => 'Overheat'],
                    ['value' => 'Level air radiator kurang', 'label' => 'Air Radiator Kurang'],
                    ['value' => 'Bunyi ketukan dari klep', 'label' => 'Bunyi Klep'],
                    ['value' => 'Asap hitam dari knalpot', 'label' => 'Asap Hitam'],
                    
                    ['value' => 'Level oli mesin kurang', 'label' => 'Oli Kurang'],
                    ['value' => 'Kondisi dalam mesin sudah ngerak ', 'label' => 'Ngerak'],
                    ['value' => 'Mesin sudah berlumpur/Slug (Jorok jarang ganti oli/oli palsu)', 'label' => 'Slug'],
                    ['value' => 'Oli kental tidak biasanya', 'label' => 'Kekentalan'],
                ]
            ],
            'kelistrikan' => [
                'label' => 'Kerusakan Kelistrikan',
                'options' => [
                    ['value' => 'Lampu tidak menyala', 'label' => 'Lampu Mati'],
                    ['value' => 'Power window tidak berfungsi', 'label' => 'Power Window Rusak'],
                    ['value' => 'AC tidak dingin', 'label' => 'AC Tidak Dingin'],
                    ['value' => 'Audio tidak berfungsi', 'label' => 'Audio Rusak'],
                    ['value' => 'Kabel terbuka', 'label' => 'Kabel Terbuka'],
                ]
            ],
            'kaca' => [
                'label' => 'Kerusakan Kaca',
                'options' => [
                    ['value' => 'Retak pada kaca', 'label' => 'Kaca Retak'],
                    ['value' => 'Kaca film menggelembung atau rusak', 'label' => 'Kaca Film Rusak'],
                    ['value' => 'Kaca pecah', 'label' => 'Kaca Pecah'],
                    ['value' => 'Pintu kaca tidak bisa naik/turun', 'label' => 'Kaca Macet'],
                ]
            ],
            'ban_velg' => [
                'label' => 'Kerusakan Ban & Velg',
                'options' => [
                    ['value' => 'Velg tergores atau penyok', 'label' => 'Velg Rusak'],
                    ['value' => 'Ban botak', 'label' => 'Ban Botak'],
                    ['value' => 'Velg retak', 'label' => 'Velg Retak'],
                    ['value' => 'Velg bukan yang asli', 'label' => 'Gantian'],
                    ['value' => 'Ban tipis', 'label' => 'Tipis'],
                    ['value' => 'Tebal namun sudah Getas', 'label' => 'Tebal Getas'],
                ]
            ],
            'rangka_depan' => [
                'label' => 'Rangka Depan (Bulkhead)',
                'options' => [
                    ['value' => 'Ada bagian yang keriting bekas perbaikan', 'label' => 'Kriting'],
                    ['value' => 'Frame bengkok', 'label' => 'Bengkok'],
                    ['value' => 'Ada bagian yang penyok', 'label' => 'Penyok'],
                    ['value' => 'Bekas perbaikan las lasan', 'label' => 'Bekas Las'],
                    ['value' => 'Fame atau Komponen Gantian', 'label' => 'Gantian'],
                    ['value' => 'Sudah di cet ulang', 'label' => 'Repaint'],
                    ['value' => 'Sudah dempulan ', 'label' => 'Dempul'],
                    ['value' => 'Ada penyok/melengkung di bagian bawah bekas gasruk ', 'label' => 'Gasruk'],
                    ['value' => 'Tidak ada efek kebagian lain', 'label' => 'Tidak Efek'],
                ]
            ],
            'pilar' => [
                'label' => 'Pilar',
                'options' => [
                    ['value' => 'Ada bagian yang keriting bekas perbaikan', 'label' => 'Kriting'],
                    ['value' => 'Terdapat bekas perbaikan las lasan', 'label' => 'Bekas Las'],
                    ['value' => 'Permukaan berkarat (Pemakaian)', 'label' => 'karat pemakaian'],
                    ['value' => 'Ada bagian yang penyok', 'label' => 'Penyok'],
                    ['value' => 'Lidah Pilar sudah dempulan ', 'label' => 'Dempul'],
                    ['value' => 'Bagian luar dempulan', 'label' => 'Dempul Bagian Luar'],
                ]
            ],
            'kaki_kaki' => [
                'label' => 'Kaki-Kaki',
                'options' => [
                    ['value' => 'Bunyi tidak normal saat melewati jalan tidak rata', 'label' => 'Bunyi Tidak Normal'],
                    ['value' => 'Shock absorber bocor', 'label' => 'Shock Bocor'],
                    ['value' => 'Ball joint aus', 'label' => 'Ball Joint Aus'],
                    ['value' => 'Bearing roda berisik', 'label' => 'Bearing Berisik'],
                    ['value' => 'Suspensi tidak stabil', 'label' => 'Suspensi Goyang'],
                ]
            ],
            'sistem_pengereman' => [
                'label' => 'Sistem Pengereman',
                'options' => [
                    ['value' => 'Rem bunyi berdecit', 'label' => 'Rem Berdecit'],
                    ['value' => 'Rem blong atau kurang pakem', 'label' => 'Rem Blong'],
                    ['value' => 'Piringan rem berkarat atau tipis', 'label' => 'Piringan Berkarat'],
                    ['value' => 'Minyak rem bocor', 'label' => 'Minyak Rem Bocor'],
                ]
            ],
            'sistem_pembuangan' => [
                'label' => 'Sistem Pembuangan',
                'options' => [
                    ['value' => 'Knalpot berlubang', 'label' => 'Knalpot Bolong'],
                    ['value' => 'Suara knalpot bocor', 'label' => 'Suara Bocor'],
                    ['value' => 'Bracket knalpot patah', 'label' => 'Bracket Patah'],
                ]
            ],
            'sistem_pendingin' => [
                'label' => 'Sistem Pendingin',
                'options' => [
                    ['value' => 'Radiator bocor', 'label' => 'Radiator Bocor'],
                    ['value' => 'Air radiator berkurang cepat', 'label' => 'Air Kurang'],
                    ['value' => 'Kipas radiator tidak berfungsi', 'label' => 'Kipas Rusak'],
                ]
            ],
            'transmisi' => [
                'label' => 'Transmisi',
                'options' => [
                    ['value' => 'Transmisi manual sulit masuk gigi', 'label' => 'Gigi Sulit Masuk'],
                    ['value' => 'Transmisi otomatis hentakan kasar', 'label' => 'Hentakan Kasar'],
                    ['value' => 'Oli transmisi bocor', 'label' => 'Oli Transmisi Bocor'],
                ]
            ],
            'banjir' => [
                'label' => 'Banjir',
                'options' => [
                    ['value' => 'Ada karat oksidasi tidak menyeluruh', 'label' => 'Oksidasi'],
                    ['value' => 'Karat cukup tebal ', 'label' => 'Karat Tebal'],
                    ['value' => 'Flooring terlihat Lusuh', 'label' => 'Flooring Lusuh'],
                    ['value' => 'Tercium bau apek', 'label' => 'Bau'],
                    ['value' => 'Soket berjamur', 'label' => 'Jamuran'],
                ]
            ],
        ];
    }

// Tambahkan method ini di class yang sama
    protected function getDefaultSettings(string $inputType): array
    {
        $defaults = [
            'is_required' => true,
        ];
        // Ambil semua damage options dari kategori exterior sebagai default
        $exteriorDamageOptions = array_map(function ($option) {
            return $option ;
        }, $this->getDamageCategories()['exterior']['options']);

        switch ($inputType) {
            case 'number':
                $defaults['min'] = 0;
                $defaults['max'] = 100;
                $defaults['step'] = 1;
                break;
                
            case 'textarea':
                $defaults['min_length'] = 0;
                $defaults['max_length'] = 500;
                $defaults['placeholder'] = 'Masukkan teks di sini';
                break;
                
            case 'text':
                $defaults['min_length'] = 0;
                $defaults['max_length'] = 255;
                $defaults['text_transform'] = 'none';
                $defaults['allow_space'] = true;
                $defaults['placeholder'] = 'Masukkan teks di sini';
                break;
                
            case 'account':
                $defaults['currency_symbol'] = 'Rp';
                $defaults['thousands_separator'] = ',';
                $defaults['min_value'] = 0;
                $defaults['max_value'] = 100000000;
                break;
                
        case 'image':
                $defaults['max_files'] = 1;
                $defaults['max_size'] = 2048;
                $defaults['allowed_types'] = ['jpg', 'png', 'jpeg'];
                $defaults['enable_flash'] = true;
                $defaults['enable_camera_switch'] = true;
                $defaults['camera_aspect_ratio'] = '3:4';
                break;

            case 'imageTOradio':
                $defaults['max_files'] = 5;
                $defaults['max_size'] = 2048;
                $defaults['allowed_types'] = ['jpg', 'png', 'jpeg'];
                $defaults['enable_flash'] = true;
                $defaults['enable_camera_switch'] = true;
                $defaults['camera_aspect_ratio'] = '3:4';
                $defaults['radios'] = [
                    [
                        'value' => 'Normal',
                        'label' => 'Normal',
                        'settings' => [
                            'show_textarea' => false,
                                'min_length' => 0,
                                'max_length' => 500,
                                'placeholder' => 'Masukkan teks di sini',
                            'enable_damage' => false,
                            'damage_category' => 'exterior', // TAMBAH INI
                            'damage_options' => $exteriorDamageOptions // Langsung terisi semua
                        ]
                    ],
                    [
                        'value' => 'Tidak Normal', 
                        'label' => 'Tidak Normal',
                        'settings' => [
                            'show_textarea' => false,
                                'min_length' => 0,
                                'max_length' => 500,
                                'placeholder' => 'Masukkan teks di sini',
                            'enable_damage' => false,
                            'damage_category' => 'exterior', // TAMBAH INI
                            'damage_options' => $exteriorDamageOptions // Langsung terisi semua
                        
                        ]
                    ]
                ];
                break;
                
            case 'date':
                $defaults['min_date'] = now()->subYear()->format('Y-m-d');
                $defaults['max_date'] = now()->addYear()->format('Y-m-d');
                break;
                
            case 'select':
            case 'checkbox':
                    case 'radio':
                $defaults['radios'] = [
                    [
                        'value' => 'Normal',
                        'label' => 'Normal',
                        'settings' => [
                            'show_textarea' => false,
                                'min_length' => 0,
                                'max_length' => 500,
                                'placeholder' => 'Masukkan teks di sini',
                            'show_image_upload' => false,
                                'max_files' => 5,
                                'max_size' => 2048,
                                'allowed_types' => ['jpg', 'png', 'jpeg'],
                                'enable_flash' => true,
                                'enable_camera_switch' => true,
                                'camera_aspect_ratio' => '3:4',
                            'enable_damage' => false,
                            'damage_category' => 'exterior', // TAMBAH INI
                            'damage_options' => $exteriorDamageOptions // Langsung terisi semua
                        ]
                    ],
                    [
                        'value' => 'Tidak Normal', 
                        'label' => 'Tidak Normal',
                        'settings' => [
                            'show_textarea' => false,
                                'min_length' => 0,
                                'max_length' => 500,
                                'placeholder' => 'Masukkan teks di sini',
                            'show_image_upload' => false,
                                'max_files' => 5,
                                'max_size' => 2048,
                                'allowed_types' => ['jpg', 'png', 'jpeg'],
                                'enable_flash' => true,
                                'enable_camera_switch' => true,
                                'camera_aspect_ratio' => '3:4',
                            'enable_damage' => false,
                            'damage_category' => 'exterior', // TAMBAH INI
                            'damage_options' => $exteriorDamageOptions // Langsung terisi semua
                        ]
                    ]
                ];
            if ($inputType === 'checkbox') {
                    $defaults['multiple'] = true;
                    $defaults['enable_damage_global'] = true;
                    $defaults['damage_category_global'] = 'exterior'; // TAMBAH INI
                    $defaults['damage_options_global'] = []; // KOSONGKAN
                }
                break;
        }

        return $defaults;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('menu_point')
            ->columns([
                Tables\Columns\TextColumn::make('inspection_point.name')
                    ->label('Inspeksi Point')
                    ->sortable()
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

                Tables\Filters\SelectFilter::make('inspection_point_id')
                    ->relationship('inspection_point', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])


                ->headerActions([
                // ACTION UNTUK MULTIPLE CREATE
                Tables\Actions\Action::make('createMultiple')
                    ->label('Tambah Multiple Points')
                    ->icon('heroicon-o-plus')
                    ->form(function () {
                        $ownerRecord = $this->getOwnerRecord();
                        
                        return [
                            Forms\Components\Select::make('input_type')
                                ->label('Tipe Input')
                                ->options([
                                    'damage' => 'Kerusakan',
                                    'text' => 'Text',
                                    'number' => 'Number',
                                    'account' => 'Account',
                                    'date' => 'Date',
                                    'image' => 'Image',
                                    'imageTOradio' => 'Image to Radio',
                                    'radio' => 'Radio Button',
                                    'checkbox' => 'Checkbox',
                                    'textarea' => 'Textarea',
                                    'select' => 'Select',
                                ])
                                ->required()
                                ->reactive()
                                ->default('text')
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $set('settings', null);
                                    $defaults = $this->getDefaultSettings($state);
                                    foreach ($defaults as $key => $value) {
                                        $set("settings.{$key}", $value);
                                    }
                                }),
                            
                            Forms\Components\Toggle::make('is_active')
                                ->label('Status Aktif')
                                ->default(true),
                            
                            // CHECKBOX LIST UNTUK PILIH MULTIPLE POINTS
Forms\Components\Select::make('inspection_point_ids')
    ->label('Pilih Inspection Points')
    ->options(function () {
        $ownerRecord = $this->getOwnerRecord();
        
        // Dapatkan category_id dari app_menu
        $categoryId = $ownerRecord->category_id;
        
        // Cari semua inspection_point_id yang sudah digunakan di category ini
        $usedInspectionPointIds = MenuPoint::whereHas('app_menu', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->pluck('inspection_point_id');
        
        // Ambil inspection points yang belum digunakan di category ini
        $points = InspectionPoint::whereNotIn('id', $usedInspectionPointIds)
            ->with('component')
            ->get();
        
        // Group by component name dengan format optgroup
        $groupedOptions = [];
        foreach ($points->groupBy('component.name') as $componentName => $componentPoints) {
            $groupedOptions[$componentName] = $componentPoints->pluck('name', 'id')->toArray();
        }
        
        return $groupedOptions;
    })
    ->multiple()
    ->searchable()
    ->preload()
    ->required()
    ->maxItems(50)
    ->helperText('Gunakan Ctrl/Cmd + klik untuk memilih multiple items'),
                                          
                            // FIELDSET SETTINGS (SAMA SEPERTI SEBELUMNYA)
                            Forms\Components\Fieldset::make('Konfigurasi Settings')
                                ->schema(function (callable $get) {
                                    $inputType = $get('input_type');
                                    $schema = [];

                                    // Common settings for all input types
                                    $commonSettings = [
                                        Forms\Components\Toggle::make('settings.is_required')
                                            ->label('Wajib Diisi')
                                            ->default(true)
                                            ->columnSpanFull(),
                                    ];

                                    // Settings for number input
                                    if ($inputType === 'number') {
                                        $schema[] = Forms\Components\Fieldset::make('Konfigurasi Number')
                                            ->schema([
                                                Forms\Components\TextInput::make('settings.min')
                                                    ->label('Nilai Minimum')
                                                    ->numeric()
                                                    ->default(0),
                                                Forms\Components\TextInput::make('settings.max')
                                                    ->label('Nilai Maksimum')
                                                    ->numeric()
                                                    ->default(100),
                                                Forms\Components\TextInput::make('settings.step')
                                                    ->label('Step Value')
                                                    ->numeric()
                                                    ->default(1),
                                            ])
                                            ->columns(3);
                                    }

                                    // Settings for textarea input
                                    elseif ($inputType === 'textarea') {
                                        $schema[] = Forms\Components\Fieldset::make('Konfigurasi Textarea')
                                            ->schema([
                                                Forms\Components\TextInput::make('settings.min_length')
                                                    ->label('Panjang Minimum')
                                                    ->numeric()
                                                    ->default(0),
                                                Forms\Components\TextInput::make('settings.max_length')
                                                    ->label('Panjang Maksimum')
                                                    ->numeric()
                                                    ->default(500),
                                                Forms\Components\TextInput::make('settings.placeholder')
                                                    ->label('Placeholder')
                                                    ->default('Masukkan teks di sini'),
                                            ])
                                            ->columns(3);
                                    }

                                    // Settings for text input
                                    elseif ($inputType === 'text') {
                                        $schema[] = Forms\Components\Fieldset::make('Konfigurasi Text')
                                            ->schema([
                                                Forms\Components\TextInput::make('settings.min_length')
                                                    ->label('Panjang Minimum')
                                                    ->numeric()
                                                    ->default(0),
                                                Forms\Components\TextInput::make('settings.max_length')
                                                    ->label('Panjang Maksimum')
                                                    ->numeric()
                                                    ->default(255),
                                                // Forms\Components\TextInput::make('settings.pattern')
                                                //     ->label('Pattern Regex')
                                                //     ->default('^[a-zA-Z0-9\s]+$'),
                                            Forms\Components\Select::make('settings.text_transform')
                                                    ->label('Format Text')
                                                    ->options([
                                                        'none' => 'Biasa',
                                                        'uppercase' => 'Huruf Besar Semua',
                                                        'lowercase' => 'Huruf Kecil Semua',
                                                        'capitalize' => 'Awal Kata Besar',
                                                    ])
                                                    ->default('none')
                                                    ->required(),


                                                Forms\Components\Toggle::make('settings.allow_space')
                                                    ->label('Boleh Pakai Spasi')
                                                    ->default(true),


                                                Forms\Components\TextInput::make('settings.placeholder')
                                                    ->label('Placeholder')
                                                    ->default('Masukkan teks di sini'),
                                            ])
                                            ->columns(2);
                                    }

                                    // Settings for account input (currency format)
                                    elseif ($inputType === 'account') {
                                        $schema[] = Forms\Components\Section::make('Pengaturan Account/Currency')
                                            ->schema([
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('settings.currency_symbol')
                                                            ->label('Simbol Currency')
                                                            ->default('Rp')
                                                            ->placeholder('Rp, $, €, etc.'),
                                                        
                                                        Forms\Components\Select::make('settings.thousands_separator')
                                                            ->label('Pemisah Ribuan')
                                                            ->options([
                                                                ',' => 'Koma (,)',
                                                                '.' => 'Titik (.)',
                                                                ' ' => 'Spasi ( )',
                                                                '' => 'Tidak Ada',
                                                            ])
                                                            ->default(','),
                                                    ]),
                                                
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('settings.min_value')
                                                            ->label('Nilai Minimum')
                                                            ->numeric()
                                                            ->default(0),
                                                        
                                                        Forms\Components\TextInput::make('settings.max_value')
                                                            ->label('Nilai Maksimum')
                                                            ->numeric()
                                                            ->default(100000000),
                                                    ]),
                                            ])
                                            ->columns(1);
                                    }

                                    // Settings for image input
                                    elseif ($inputType === 'image') {
                                        $schema[] = Forms\Components\Section::make('Konfigurasi Image')
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('settings.max_files')
                                                            ->label('File Maksimum')
                                                            ->numeric()
                                                            ->default(1),

                                                        Forms\Components\TextInput::make('settings.max_size')
                                                            ->label('Ukuran Maks (KB)')
                                                            ->numeric()
                                                            ->default(2048),

                                                        Forms\Components\TagsInput::make('settings.allowed_types')
                                                            ->label('Tipe File Diizinkan')
                                                            ->default(['jpg', 'png', 'jpeg'])
                                                            ->placeholder('jpg, png, jpeg'),
                                                    ]),

                                                Forms\Components\Section::make('Pengaturan Kamera')
                                                    ->schema([
                                                        Forms\Components\Grid::make(2)
                                                            ->schema([
                                                                Forms\Components\Toggle::make('settings.enable_flash')
                                                                    ->label('Flash')
                                                                    ->default(true),

                                                                Forms\Components\Toggle::make('settings.enable_camera_switch')
                                                                    ->label('Switch Kamera')
                                                                    ->default(true),
                                                            ]),

                                                        Forms\Components\Select::make('settings.camera_aspect_ratio')
                                                            ->label('Aspect Ratio Kamera')
                                                            ->options([
                                                                '4:3' => '4:3',
                                                                '3:4' => '3:4',
                                                                '16:9' => '16:9',
                                                                '1:1' => '1:1',
                                                            ])
                                                            ->default('4:3')
                                                            ->columnSpanFull(),
                                                    ])
                                                    ->collapsible(),
                                            ])
                                            ->columns(1);
                                    }

                                    // Settings for imageTOradio input
                                    elseif ($inputType === 'imageTOradio') {
                                        $schema[] = Forms\Components\Section::make('Konfigurasi Image')
                                            ->schema([
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('settings.max_files')
                                                            ->label('File Maksimum')
                                                            ->numeric()
                                                            ->default(1),

                                                        Forms\Components\TextInput::make('settings.max_size')
                                                            ->label('Ukuran Maks (KB)')
                                                            ->numeric()
                                                            ->default(2048),

                                                        Forms\Components\TagsInput::make('settings.allowed_types')
                                                            ->label('Tipe File Diizinkan')
                                                            ->default(['jpg', 'png', 'jpeg'])
                                                            ->placeholder('jpg, png, jpeg'),
                                                    ]),

                                                Forms\Components\Section::make('Pengaturan Kamera')
                                                    ->schema([
                                                        Forms\Components\Grid::make(2)
                                                            ->schema([
                                                                Forms\Components\Toggle::make('settings.enable_flash')
                                                                    ->label('Flash')
                                                                    ->default(true),

                                                                Forms\Components\Toggle::make('settings.enable_camera_switch')
                                                                    ->label('Switch Kamera')
                                                                    ->default(true),
                                                            ]),

                                                        Forms\Components\Select::make('settings.camera_aspect_ratio')
                                                            ->label('Aspect Ratio Kamera')
                                                            ->options([
                                                                '4:3' => '4:3',
                                                                '3:4' => '3:4',
                                                                '16:9' => '16:9',
                                                                '1:1' => '1:1',
                                                            ])
                                                            ->default('4:3')
                                                            ->columnSpanFull(),
                                                    ])
                                                    ->collapsible(),
                                            ])
                                            ->columns(1);
                                        
                                        $schema[] = Forms\Components\Repeater::make('settings.radios')
                                            ->label('Opsi Radio')
                                            ->schema([
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('value')
                                                            ->required()
                                                            ->label('Nilai Opsi')
                                                            ->default(fn ($state) => $state ?? 'opsi1'),
                                                        Forms\Components\TextInput::make('label')
                                                            ->required()
                                                            ->label('Label Tampilan')
                                                            ->default(fn ($state) => $state ?? 'Opsi 1'),
                                                    ]),
                                                
                                                Forms\Components\Section::make('Pengaturan Textarea')
                                                    ->schema([
                                                        Forms\Components\Toggle::make('settings.show_textarea')
                                                            ->label('Tampilkan Textarea')
                                                            ->default(false)
                                                            ->reactive(),
                                                        
                                                        Forms\Components\Grid::make(3)
                                                            ->schema([
                                                                Forms\Components\TextInput::make('settings.min_length')
                                                                    ->label('Panjang Min')
                                                                    ->numeric()
                                                                    ->default(0)
                                                                    ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                                Forms\Components\TextInput::make('settings.max_length')
                                                                    ->label('Panjang Maks')
                                                                    ->numeric()
                                                                    ->default(500)
                                                                    ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                                Forms\Components\TextInput::make('settings.placeholder')
                                                                    ->label('Placeholder')
                                                                    ->default('Masukkan teks di sini')
                                                                    ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                            ])
                                                    ])
                                                    ->collapsible()
                                                    ->collapsed(fn (callable $get) => !$get('settings.show_textarea')),
                                                
                                                    // Ganti bagian damage options dengan ini:
                                                    Forms\Components\Section::make('Opsi Kerusakan')
                                                        ->schema([
                                                            Forms\Components\Toggle::make('settings.enable_damage')
                                                                ->label('Aktifkan Opsi Kerusakan')
                                                                ->default(false)
                                                                ->reactive(),
                                                            
                                                            Forms\Components\Select::make('settings.damage_category')
                                                                ->label('Pilih Kategori Kerusakan')
                                                                ->options(function () {
                                                                    $categories = $this->getDamageCategories();
                                                                    return collect($categories)->mapWithKeys(fn($cat, $key) => [$key => $cat['label']])->toArray();
                                                                })
                                                                ->default('exterior')
                                                                ->reactive()
                                                                ->afterStateUpdated(function ($state, callable $set) {
                                                                    // Otomatis isi damage_options ketika kategori berubah
                                                                    $categories = $this->getDamageCategories();
                                                                    $damageOptions = $categories[$state]['options'] ?? [];
                                                                    // Tambahkan field is_selected untuk setiap option
                                                                    $optionsWithSelection = array_map(function ($option) {
                                                                        return $option + ['is_selected' => true];
                                                                    }, $damageOptions);
                                                                    $set('settings.damage_options', $optionsWithSelection);
                                                                })
                                                                ->visible(fn (callable $get) => $get('settings.enable_damage')),
                                                            
                                                            Forms\Components\Repeater::make('settings.damage_options')
                                                                ->label('Daftar Opsi Kerusakan')
                                                                ->schema([
                                                                    Forms\Components\TextInput::make('value')
                                                                        ->label('Deskripsi Kerusakan (Detail)')
                                                                        ->required()
                                                                        ->columnSpan(2),
                                                                    
                                                                    Forms\Components\TextInput::make('label')
                                                                        ->label('Label Singkat')
                                                                        ->required(),
                                                                ])
                                                                ->defaultItems(0)
                                                                ->columns(4)
                                                                ->visible(fn (callable $get) => $get('settings.enable_damage'))
                                                                ->columnSpanFull()
                                                                ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                                                    // Otomatis isi saat form load
                                                                    $category = $get('../../damage_category');
                                                                    if (empty($state) && $category) {
                                                                        $categories = $this->getDamageCategories();
                                                                        $damageOptions = $categories[$category]['options'] ?? [];
                                                                        // Tambahkan field is_selected untuk setiap option
                                                                        $optionsWithSelection = array_map(function ($option) {
                                                                            return $option + ['is_selected' => true];
                                                                        }, $damageOptions);
                                                                        $set('settings.damage_options', $optionsWithSelection);
                                                                    }
                                                                })
                                                                ->createItemButtonLabel('Tambah Manual')
                                                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                                                ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                                                    // Hanya simpan yang dicentang is_selected = true
                                                                    return array_filter($data, function ($item) {
                                                                        return $item['is_selected'] === true;
                                                                    });
                                                                })

                                                        ])
                                                        ->collapsible()
                                                        ->collapsed(fn (callable $get) => !$get('settings.enable_damage')),

                                            ])
                                            ->defaultItems(2)
                                            ->createItemButtonLabel('Tambah Opsi Radio') // Tombol tambah di bawah
                                            ->columnSpanFull()
                                            ->columns(1);
                                    }

                                    // Settings for select/checkbox/radio inputs
                                    elseif (in_array($inputType, ['select', 'checkbox', 'radio'])) {
                                        $schema[] = Forms\Components\Repeater::make('settings.radios')
                                            ->label('Daftar Opsi')
                                            ->schema([
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('value')
                                                            ->required()
                                                            ->label('Nilai Opsi')
                                                            ->default('opsi1'),
                                                        Forms\Components\TextInput::make('label')
                                                            ->required()
                                                            ->label('Label Tampilan')
                                                            ->default('Opsi 1'),
                                                    ]),
                                                
                                                Forms\Components\Section::make('Pengaturan Textarea')
                                                    ->schema([
                                                        Forms\Components\Toggle::make('settings.show_textarea')
                                                            ->label('Tampilkan Textarea')
                                                            ->default(false)
                                                            ->reactive(),
                                                        
                                                        Forms\Components\Grid::make(3)
                                                            ->schema([
                                                                Forms\Components\TextInput::make('settings.min_length')
                                                                    ->label('Panjang Min')
                                                                    ->numeric()
                                                                    ->default(0)
                                                                    ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                                Forms\Components\TextInput::make('settings.max_length')
                                                                    ->label('Panjang Maks')
                                                                    ->numeric()
                                                                    ->default(500)
                                                                    ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                                Forms\Components\TextInput::make('settings.placeholder')
                                                                    ->label('Placeholder')
                                                                    ->default('Masukkan teks di sini')
                                                                    ->visible(fn (callable $get) => $get('settings.show_textarea')),
                                                            ])
                                                    ])
                                                    ->collapsible()
                                                    ->collapsed(fn (callable $get) => !$get('settings.show_textarea')),
                                                
                                                Forms\Components\Section::make('Pengaturan Upload Gambar')
                                                    ->schema([
                                                        Forms\Components\Toggle::make('settings.show_image_upload')
                                                            ->label('Tampilkan Upload Gambar')
                                                            ->default(false)
                                                            ->reactive(),
                                                        
                                                        Forms\Components\Grid::make(3)
                                                            ->schema([
                                                                Forms\Components\TextInput::make('settings.max_files')
                                                                    ->label('File Maksimum')
                                                                    ->numeric()
                                                                    ->default(1)
                                                                    ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                                                Forms\Components\TextInput::make('settings.max_size')
                                                                    ->label('Ukuran Maks (KB)')
                                                                    ->numeric()
                                                                    ->default(2048)
                                                                    ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                                                Forms\Components\TagsInput::make('settings.allowed_types')
                                                                    ->label('Tipe File Diizinkan')
                                                                    ->default(['jpg', 'png', 'jpeg'])
                                                                    ->placeholder('jpg, png, jpeg')
                                                                    ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                                            ]),
                                                        
                                                        Forms\Components\Section::make('Pengaturan Kamera')
                                                            ->schema([
                                                                Forms\Components\Grid::make(2)
                                                                    ->schema([
                                                                        Forms\Components\Toggle::make('settings.enable_flash')
                                                                            ->label('Flash')
                                                                            ->default(true)
                                                                            ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                                                        Forms\Components\Toggle::make('settings.enable_camera_switch')
                                                                            ->label('Switch Kamera')
                                                                            ->default(true)
                                                                            ->visible(fn (callable $get) => $get('settings.show_image_upload')),
                                                                    ]),
                                                                
                                                                Forms\Components\Select::make('settings.camera_aspect_ratio')
                                                                    ->label('Aspect Ratio Kamera')
                                                                    ->options([
                                                                        '4:3' => '4:3',
                                                                        '3:4' => '3:4',
                                                                        '16:9' => '16:9',
                                                                        '1:1' => '1:1',
                                                                    ])
                                                                    ->default('4:3')
                                                                    ->visible(fn (callable $get) => $get('settings.show_image_upload'))
                                                                    ->columnSpanFull(),
                                                            ])
                                                            ->visible(fn (callable $get) => $get('settings.show_image_upload'))
                                                            ->collapsible()
                                                    ])
                                                    ->collapsible()
                                                    ->collapsed(fn (callable $get) => !$get('settings.show_image_upload')),
                                                
                                            // Ganti bagian damage options dengan ini:
                                                Forms\Components\Section::make('Opsi Kerusakan')
                                                    ->schema([
                                                        Forms\Components\Toggle::make('settings.enable_damage')
                                                            ->label('Aktifkan Opsi Kerusakan')
                                                            ->default(false)
                                                            ->reactive(),
                                                        
                                                        Forms\Components\Select::make('settings.damage_category')
                                                            ->label('Pilih Kategori Kerusakan')
                                                            ->options(function () {
                                                                $categories = $this->getDamageCategories();
                                                                return collect($categories)->mapWithKeys(fn($cat, $key) => [$key => $cat['label']])->toArray();
                                                            })
                                                            ->default('exterior')
                                                            ->reactive()
                                                            ->afterStateUpdated(function ($state, callable $set) {
                                                                // Otomatis isi damage_options ketika kategori berubah
                                                                $categories = $this->getDamageCategories();
                                                                $damageOptions = $categories[$state]['options'] ?? [];
                                                                // Tambahkan field is_selected untuk setiap option
                                                                $optionsWithSelection = array_map(function ($option) {
                                                                    return $option + ['is_selected' => true];
                                                                }, $damageOptions);
                                                                $set('settings.damage_options', $optionsWithSelection);
                                                            })
                                                            ->visible(fn (callable $get) => $get('settings.enable_damage')),
                                                        
                                                        Forms\Components\Repeater::make('settings.damage_options')
                                                            ->label('Daftar Opsi Kerusakan')
                                                            ->schema([
                                                                Forms\Components\TextInput::make('value')
                                                                    ->label('Deskripsi Kerusakan (Detail)')
                                                                    ->required()
                                                                    ->columnSpan(2),
                                                                
                                                                Forms\Components\TextInput::make('label')
                                                                    ->label('Label Singkat')
                                                                    ->required(),
                                                            ])
                                                            ->defaultItems(0)
                                                            ->columns(4)
                                                            ->visible(fn (callable $get) => $get('settings.enable_damage'))
                                                            ->columnSpanFull()
                                                            ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                                                // Otomatis isi saat form load
                                                                $category = $get('../../damage_category');
                                                                if (empty($state) && $category) {
                                                                    $categories = $this->getDamageCategories();
                                                                    $damageOptions = $categories[$category]['options'] ?? [];
                                                                    // Tambahkan field is_selected untuk setiap option
                                                                    $optionsWithSelection = array_map(function ($option) {
                                                                        return $option + ['is_selected' => true];
                                                                    }, $damageOptions);
                                                                    $set('settings.damage_options', $optionsWithSelection);
                                                                }
                                                            })
                                                            ->createItemButtonLabel('Tambah Manual')
                                                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                                                // Hanya simpan yang dicentang is_selected = true
                                                                return array_filter($data, function ($item) {
                                                                    return $item['is_selected'] === true;
                                                                });
                                                            })

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
                                                ->label('Izinkan Multiple Selection')
                                                ->default(true);
                                                
                                            $schema[] = Forms\Components\Section::make('Opsi Kerusakan Global')
                                                ->schema([
                                                    Forms\Components\Toggle::make('settings.enable_damage_global')
                                                        ->label('Aktifkan Opsi Kerusakan Global')
                                                        ->default(false)
                                                        ->reactive(),
                                                    
                                                    Forms\Components\Repeater::make('settings.damage_options_global')
                                                        ->label('Daftar Opsi Kerusakan Global')
                                                        ->schema([
                                                            Forms\Components\TextInput::make('value')
                                                                ->required()
                                                                ->label('Nilai Kerusakan')
                                                                ->default('rusak_global1'),
                                                            Forms\Components\TextInput::make('label')
                                                                ->required()
                                                                ->label('Label Kerusakan')
                                                                ->default('Rusak Global 1'),
                                                        ])
                                                        ->defaultItems(1)
                                                        ->columns(2)
                                                        ->visible(fn (callable $get) => $get('settings.enable_damage_global'))
                                                        ->columnSpanFull(),
                                                ])
                                                ->collapsible()
                                                ->collapsed(fn (callable $get) => !$get('settings.enable_damage_global'));
                                        }
                                    }

                                    // Settings for date input
                                    elseif ($inputType === 'date') {
                                        $schema[] = Forms\Components\Fieldset::make('Konfigurasi Date')
                                            ->schema([
                                                Forms\Components\DatePicker::make('settings.min_date')
                                                    ->label('Tanggal Minimum')
                                                    ->default(now()->subYear()),
                                                Forms\Components\DatePicker::make('settings.max_date')
                                                    ->label('Tanggal Maksimum')
                                                    ->default(now()->addYear()),
                                            ])
                                            ->columns(2);
                                    }

                                    return array_merge($commonSettings, $schema);
                                })
                                ->columnSpanFull()
                        ];
                    })
                    ->action(function (array $data): void {
                        $ownerRecord = $this->getOwnerRecord();
                        
                        $inspectionPointIds = $data['inspection_point_ids'];
                        $inputType = $data['input_type'];
                        $settings = $data['settings'] ?? [];
                        $isActive = $data['is_active'];
                        
                        $createdCount = 0;
                        foreach ($inspectionPointIds as $pointId) {
                            // Cek duplikasi
                            $exists = MenuPoint::where('inspection_point_id', $pointId)
                                ->where('app_menu_id', $ownerRecord->id)
                                ->exists();
                                
                            if (!$exists) {
                                MenuPoint::create([
                                    'inspection_point_id' => $pointId,
                                    'input_type' => $inputType,
                                    'settings' => $settings,
                                    'is_active' => $isActive,
                                    'app_menu_id' => $ownerRecord->id,
                                    'order' => MenuPoint::where('app_menu_id', $ownerRecord->id)->max('order') + 1,
                                ]);
                                $createdCount++;
                            }
                        }
                        
                        // NOTIFIKASI SUKSES
                        \Filament\Notifications\Notification::make()
                            ->title($createdCount . ' inspection points berhasil ditambahkan')
                            ->success()
                            ->send();
                    })
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
