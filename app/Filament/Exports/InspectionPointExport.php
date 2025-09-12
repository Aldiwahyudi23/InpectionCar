<?php

namespace App\Filament\Exports;

use App\Models\DataInspection\InspectionPoint;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class InspectionPointExport extends Exporter
{
    protected static ?string $model = InspectionPoint::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('component.name')
                ->label('Nama Komponen'),
            
            ExportColumn::make('name')
                ->label('Nama Inspection Point'),
            
            ExportColumn::make('description')
                ->label('Deskripsi'),
            
            ExportColumn::make('notes')
                ->label('Catatan')
                ->formatStateUsing(fn ($state) => $state ? strip_tags($state) : ''),
            
            ExportColumn::make('is_active')
                ->label('Status')
                ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Non-Aktif'),
            
            ExportColumn::make('order')
                ->label('Urutan'),
            
            ExportColumn::make('file_path')
                ->label('File Gambar'),
            
            ExportColumn::make('created_at')
                ->label('Dibuat')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : ''),
            
            ExportColumn::make('updated_at')
                ->label('Diupdate')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export inspection points selesai dan ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' diexpor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' gagal diexpor.';
        }

        return $body;
    }
}