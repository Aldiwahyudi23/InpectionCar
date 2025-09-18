<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Finance\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

       protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 Forms\Components\Select::make('inspection_id')
                    ->relationship('inspection', 'plate_number')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Kendaraan (Plat Nomor)'),
                
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->prefix('Rp')
                    ->label('Nominal Pembayaran'),
                
                Forms\Components\Select::make('status')
                    ->options(Transaction::getStatusOptions())
                    ->required()
                    ->label('Status Pembayaran'),
                
                Forms\Components\Select::make('payment_method')
                    ->options(Transaction::getPaymentMethodOptions())
                    ->label('Metode Pembayaran'),
                
                Forms\Components\DateTimePicker::make('payment_date')
                    ->label('Tanggal Pembayaran'),
                
                Forms\Components\DateTimePicker::make('due_date')
                    ->label('Batas Waktu Pembayaran'),
                
                Forms\Components\TextInput::make('transaction_reference')
                    ->maxLength(255)
                    ->label('Referensi Transaksi'),
                
                Forms\Components\TextInput::make('invoice_number')
                    ->maxLength(255)
                    ->disabled()
                    ->label('Nomor Invoice'),
                
                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->label('Catatan'),
                
                Forms\Components\FileUpload::make('payment_proof')
                    ->directory('payment-proofs')
                    ->label('Bukti Pembayaran'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                  Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->label('No. Invoice'),
                
                Tables\Columns\TextColumn::make('inspection.plate_number')
                    ->searchable()
                    ->sortable()
                    ->label('Plat Nomor'),
                
                Tables\Columns\TextColumn::make('inspection.customer.name')
                    ->searchable()
                    ->label('Customer'),
                
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable()
                    ->label('Nominal'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => Transaction::STATUS_PENDING,
                        'success' => Transaction::STATUS_PAID,
                        'danger' => Transaction::STATUS_FAILED,
                        'secondary' => Transaction::STATUS_REFUNDED,
                        'gray' => Transaction::STATUS_EXPIRED,
                    ])
                    ->label('Status'),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->enum(Transaction::getPaymentMethodOptions())
                    ->label('Metode'),
                
                Tables\Columns\TextColumn::make('payment_date')
                    ->dateTime()
                    ->sortable()
                    ->label('Tgl. Pembayaran'),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime()
                    ->sortable()
                    ->label('Batas Waktu'),
            ])
            ->filters([
                 Tables\Filters\SelectFilter::make('status')
                    ->options(Transaction::getStatusOptions())
                    ->label('Status Pembayaran'),
                
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(Transaction::getPaymentMethodOptions())
                    ->label('Metode Pembayaran'),
                
                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query) => $query->where('status', Transaction::STATUS_PENDING)
                        ->where('due_date', '<', now()))
                    ->label('Terlambat Bayar'),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                     Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['inspection.customer']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
            // 'view' => Pages\ViewTransaction::route('/{record}'),
        ];
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['invoice_number', 'transaction_reference', 'inspection.plate_number', 'inspection.customer.name'];
    }
}
