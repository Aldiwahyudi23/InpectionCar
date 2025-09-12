<?php

namespace App\Filament\Imports;

use App\Models\DataInspection\InspectionPoint;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class InspectionPointImport extends Importer
{
    protected static ?string $model = InspectionPoint::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nama Point')
                ->required()
                ->rules(['required', 'max:255']),
            
            ImportColumn::make('description')
                ->label('Deskripsi')
                ->required()
                ->rules(['required', 'max:255']),
            
            ImportColumn::make('notes')
                ->label('Catatan')
                ->rules(['nullable', 'string']),
            
            ImportColumn::make('is_active')
                ->label('Status Aktif')
                ->boolean()
                ->rules(['boolean']),
            
            ImportColumn::make('order')
                ->label('Urutan')
                ->numeric()
                ->rules(['integer', 'min:1']),
            
            ImportColumn::make('file_path')
                ->label('File Path')
                ->rules(['nullable', 'string']),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import inspection points selesai dan ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' gagal diimpor.';
        }

        return $body;
    }

   

    protected function afterCreate(InspectionPoint $record, array $data): void
    {
        // Logic setelah record dibuat (jika diperlukan)
    }

    // Di InspectionPointImport.php
protected function beforeCreate(array $data): array
{
    // Set component_id dari options
    if ($componentId = $this->options['component_id'] ?? null) {
        $data['component_id'] = $componentId;
    }

    // Cek duplikasi nama dalam komponen yang sama
    $exists = InspectionPoint::where('name', $data['name'])
        ->where('component_id', $data['component_id'])
        ->exists();
    
    if ($exists) {
        throw new \Exception("Inspection point '{$data['name']}' sudah ada dalam komponen ini.");
    }

    // Handle order jika tidak disediakan
    if (!isset($data['order']) || empty($data['order'])) {
        $maxOrder = InspectionPoint::where('component_id', $data['component_id'])
            ->max('order');
        $data['order'] = ($maxOrder ?? 0) + 1;
    }

    return $data;
}
}