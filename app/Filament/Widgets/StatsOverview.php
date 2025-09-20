<?php

namespace App\Filament\Widgets;

use App\Models\DataInspection\Inspection;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
protected int|string|array $columnSpan = [
    'sm' => 3,
    'md' => 4,
    'lg' => 6,
];
protected function getStats(): array
    {
        return [
            Stat::make('Total Inspeksi', Inspection::count())
                ->description('Semua data inspeksi')
                ->descriptionIcon('heroicon-m-clipboard-document-list'),

            Stat::make('Selesai', Inspection::where('status', 'completed')->count())
                ->description('Inspeksi selesai')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Dibuat', Inspection::where('status', 'draft')->count())
                ->description('Menunggu tindakan')
                ->descriptionIcon('heroicon-m-clock'),

            Stat::make('Disetujui', Inspection::where('status', 'approved')->count())
                ->description('Menunggu tindakan')
                ->descriptionIcon('heroicon-m-clipboard-document-list'),

            Stat::make('Sedang di review', Inspection::where('status', 'peding_review')->count())
                ->description('Menunggu tindakan')
                ->descriptionIcon('heroicon-m-clipboard-document-list'),
                
            Stat::make('Ditunda', Inspection::where('status', 'pending')->count())
                ->description('Menunggu tindakan')
                ->descriptionIcon('heroicon-m-clipboard-document-list'),

            Stat::make('Proses', Inspection::where('status', 'in_prosess')->count())
                ->description('Menunggu tindakan')
                ->descriptionIcon('heroicon-m-clipboard-document-list'),

            Stat::make('Revisi', Inspection::where('status', 'rivision')->count())
                ->description('Menunggu tindakan')
                ->descriptionIcon('heroicon-m-clipboard-document-list'),

            Stat::make('Dibatalkan', Inspection::where('status', 'cancelled')->count())
                ->description('Menunggu tindakan')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('danger'),

        ];
    }
}
