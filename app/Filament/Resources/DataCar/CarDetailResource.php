<?php

namespace App\Filament\Resources\DataCar;

use App\Filament\Resources\DataCar\CarDetailResource\Pages;
use App\Filament\Resources\DataCar\CarDetailResource\RelationManagers;
use App\Filament\Resources\DataCar\CarDetailResource\RelationManagers\ImageCarsRelationManager;
use App\Models\DataCar\Brand;
use App\Models\DataCar\CarDetail;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log; // Import the Log class

class CarDetailResource extends Resource
{
    protected static ?string $model = CarDetail::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Car Management';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Detail Mobil';
    protected static ?string $navigationLabel = 'Detail Mobil';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('brand_id')
                    ->label('Merek')
                    ->options(Brand::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('car_model_id', null))
                    ->required()
                    ->createOptionForm(function (Form $form) { // Tambahkan closure ini
                        return $form
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Merek')
                                    ->required(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ]);
                    })
                    ->createOptionUsing(function (array $data): Brand { // Tambahkan closure ini
                        return Brand::create($data);
                    }),

                Forms\Components\Select::make('car_model_id')
                    ->label('Model')
                    ->options(function (callable $get) {
                        $brandId = $get('brand_id');
                        return $brandId ? CarModel::where('brand_id', $brandId)
                            ->where('is_active', true)
                            ->pluck('name', 'id') : [];
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('car_type_id', null))
                    ->required()
                    ->createOptionForm(function (Form $form) {  // Tambahkan closure ini
                        return $form
                            ->schema([
                                Forms\Components\Select::make('brand_id')
                                    ->label('Merek')
                                    ->options(Brand::where('is_active', true)->pluck('name', 'id'))
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Model')
                                    ->required(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ]);
                    })
                    ->createOptionUsing(function (array $data): CarModel { // Tambahkan closure ini
                        return CarModel::create($data);
                    }),

                Forms\Components\Select::make('car_type_id')
                    ->label('Tipe')
                    ->options(function (callable $get) {
                        $modelId = $get('car_model_id');
                        return $modelId ? CarType::where('car_model_id', $modelId)
                            ->where('is_active', true)
                            ->pluck('name', 'id') : [];
                    })
                    ->searchable()
                    ->required()
                    ->createOptionForm(function (Form $form) { // Tambahkan closure ini
                        return $form
                            ->schema([
                                Forms\Components\Select::make('car_model_id')
                                    ->label('Model')
                                    ->options(CarModel::where('is_active', true)->pluck('name', 'id'))
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Tipe')
                                    ->required(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ]);
                    })
                    ->createOptionUsing(function (array $data): CarType { // Tambahkan closure ini
                        return CarType::create($data);
                    }),

                Forms\Components\TextInput::make('year')
                    ->label('Tahun')
                    ->numeric()
                    ->minValue(1900)
                    ->maxValue(now()->year)
                    ->required(),

                Forms\Components\TextInput::make('cc')
                    ->label('Kapasitas Mesin (cc)')
                    ->numeric()
                    ->minValue(500)
                    ->maxValue(10000),

                Forms\Components\Select::make('transmission')
                    ->label('Transmisi')
                    ->native(false)   // pakai JS renderer (Choices.js) supaya HTML di dalam option dirender
                    ->allowHtml()     // IZINKAN HTML di option label
                    ->options([
                        'MT'   => "Manual Transmission (MT)<br><small><em>Transmisi manual dengan pedal kopling, perpindahan gigi manual</em></small>",
                        'AT'   => "Automatic Transmission (AT)<br><small><em>Transmisi otomatis konvensional dengan torque converter</em></small>",
                        'CVT'  => "Continuously Variable Transmission (CVT)<br><small><em>Transmisi otomatis dengan puli & sabuk, perpindahan halus</em></small>",
                        'e-CVT'=> "Electronic CVT (e-CVT)<br><small><em>Khusus hybrid/EV, menggabungkan motor listrik & mesin bensin</em></small>",
                        'DCT'  => "Dual Clutch Transmission (DCT)<br><small><em>Transmisi kopling ganda, perpindahan gigi cepat & efisien</em></small>",
                        'AMT'  => "Automated Manual Transmission (AMT)<br><small><em>Manual yang dikendalikan otomatis (semi otomatis)</em></small>",
                        'IVT'  => "Intelligent Variable Transmission (IVT)<br><small><em>Varian CVT dengan kontrol elektronik, lebih efisien</em></small>",
                        'SSG'  => "Seamless Shift Gearbox (SSG)<br><small><em>Transmisi kopling ganda performa tinggi (sport car)</em></small>",
                        'AGS'  => "Auto Gear Shift (AGS)<br><small><em>Versi AMT Suzuki, hemat & sederhana</em></small>",
                        'DHT'  => "Dedicated Hybrid Transmission (DHT)<br><small><em>Khusus hybrid, mengatur mesin bensin & motor listrik</em></small>",
                    ])
                    ->helperText('Pilih tipe transmisi sesuai kendaraan')
                    ->default('AT')
                    ->required(),
                Forms\Components\Select::make('fuel_type')
                    ->label('Bahan Bakar')
                    ->options([
                        'Bensin' => 'Bensin',
                        'Solar' => 'Solar',
                        'Listrik' => 'Listrik',
                        'Hybrid' => 'Hybrid'
                    ])
                    ->required(),

                Forms\Components\TextInput::make('production_period')
                    ->label('Periode Produksi')
                    ->required()
                    ->helperText('Format: Tahun Mulai - Tahun Akhir (e.g., 2010-2020)')
                    ->formatStateUsing(function ($state) {
                        //  Memastikan format yang benar saat menampilkan di form untuk edit
                        if (!$state) return null;
                        $years = explode('-', $state);
                        if (count($years) == 2) {
                            return trim($years[0]) . ' - ' . trim($years[1]);
                        }
                        return $state;
                    })
                    ->dehydrateStateUsing(function ($state) {
                         // Memastikan format yang benar saat menyimpan
                        if (!$state) return null;
                        $years = explode('-', $state);
                        if (count($years) == 2) {
                            return trim($years[0]) . '-' . trim($years[1]);
                        }
                        return $state;
                    })
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('engine_code')
                    ->label('Engine Code')
                    ->placeholder('Masukkan kode mesin')
                    ->maxLength(255)
                    ->nullable(),

                Forms\Components\TextInput::make('segment')
                    ->label('Segment')
                    ->placeholder('Masukkan segment')
                    ->maxLength(255)
                    ->nullable(),

               Forms\Components\RichEditor::make('description')
                    ->label('Deskripsi')
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
                    ->fileAttachmentsDirectory('descriptions') // Folder untuk upload file
                    ->placeholder('Masukkan deskripsi mobil di sini...')
                    ->helperText('Deskripsi tambahan tentang mobil. Format HTML akan dipertahankan.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Merek')
                    ->sortable(),

                Tables\Columns\TextColumn::make('model.name')
                    ->label('Model')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type.name')
                    ->label('Tipe'),

                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cc')
                    ->label('CC')
                    ->suffix(' cc'),

                Tables\Columns\TextColumn::make('transmission')
                    ->label('Transmisi'),

                Tables\Columns\TextColumn::make('fuel_type')
                    ->label('Bahan Bakar'),

                Tables\Columns\TextColumn::make('production_period')
                    ->label('Periode Produksi'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Filter Merek'),
                Tables\Filters\SelectFilter::make('fuel_type')
                    ->options([
                        'Bensin' => 'Bensin',
                        'Solar' => 'Solar',
                        'Listrik' => 'Listrik',
                        'Hybrid' => 'Hybrid'
                    ])
                    ->label('Bahan Bakar')
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
             ImageCarsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarDetails::route('/'),
            'create' => Pages\CreateCarDetail::route('/create'),
            'view' => Pages\ViewCarDetail::route('/{record}'),
            'edit' => Pages\EditCarDetail::route('/{record}/edit'),
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

