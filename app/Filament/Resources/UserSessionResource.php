<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserSessionResource\Pages;
use App\Filament\Resources\UserSessionResource\RelationManagers;
use App\Models\UserSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserSessionResource extends Resource
{
    protected static ?string $model = UserSession::class;
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?string $label = 'User Session';
    protected static ?string $pluralLabel = 'User Sessions';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('user.name')->label('User')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('ip_address')->label('IP'),
            Tables\Columns\TextColumn::make('platform')->label('Platform'),
            Tables\Columns\TextColumn::make('browser')->label('Browser'),
            Tables\Columns\TextColumn::make('last_activity')
                ->label('Last Active')
                ->since()
                ->sortable(),
            Tables\Columns\BadgeColumn::make('is_online')
                ->label('Status')
                ->colors([
                    'success' => fn ($state) => $state === true,
                    'danger' => fn ($state) => $state === false,
    ])
    ->formatStateUsing(fn ($state) => $state ? 'Online' : 'Offline'),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('user_id')
                ->label('User')
                ->relationship('user', 'name'),
        ])
        ->actions([
            Tables\Actions\Action::make('Deactivate')
                ->color('warning')
                ->icon('heroicon-o-user-minus')
                ->requiresConfirmation()
                ->action(function (\App\Models\UserSession $record) {
                    // Update status user jadi tidak aktif
                    $record->user->update([
                        'is_active' => false,
                    ]);
                }),
                // ->visible(fn ($record) => $record->user->is_active), // tampil hanya kalau user masih aktif

            Tables\Actions\Action::make('Logout')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (UserSession $record) {
                    \Illuminate\Support\Facades\Session::getHandler()->destroy($record->id);
                    $record->delete();
                }),

        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUserSessions::route('/'),
        ];
    }
}
