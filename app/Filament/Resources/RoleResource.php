<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        // Ambil semua permission sekali saja dan urutkan
        $permissions = Permission::all()->sortBy('name');

        // Buat array untuk menyimpan permissions yang sudah dikelompokkan
        $groupedPermissions = [
            'Filament' => [],
            'Frontend' => [],
        ];

        // Kelompokkan permissions secara manual untuk memastikan keakuratan
        foreach ($permissions as $permission) {
            if (str_starts_with($permission->name, 'filament ')) {
                $groupedPermissions['Filament'][] = $permission;
            } else {
                $groupedPermissions['Frontend'][] = $permission;
            }
        }

        // Ubah array permissions ke dalam format opsi yang diperlukan
        $formattedGroups = collect($groupedPermissions)->map(function ($permissions, $category) {
            return [
                'category' => $category,
                'permissions' => collect($permissions)->pluck('name', 'id')
            ];
        });

        return $form
            ->schema([
                Section::make('Informasi Peran')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->label('Nama Peran')
                            ->placeholder('Masukkan nama peran (contoh: Admin, Inspektur)'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Jelaskan tujuan dari peran ini')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
               Section::make('Manajemen Izin')
                    ->description('Pilih izin untuk peran ini. Izin ditampilkan dalam satu daftar yang dapat dicari.')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('')
                            ->options($permissions->pluck('name', 'id'))
                            ->bulkToggleable()
                            ->searchable()
                            ->columns(4)
                            ->gridDirection('row')
                            ->relationship('permissions', 'name'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->description ?? 'No description'),
                
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->sortable()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => $state . ' permissions'),
                
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->sortable()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state . ' users'),
                
                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('permissions')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Filter by Permission'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil'),
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No roles found')
            ->emptyStateDescription('Create your first role to get started.')
            ->emptyStateIcon('heroicon-o-shield-exclamation')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Role')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\PermissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }
}