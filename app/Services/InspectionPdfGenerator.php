<?php

namespace App\Services;

use App\Models\DataInspection\Inspection;
use App\Models\DataInspection\InspectionImage;
use App\Models\DataInspection\MenuPoint;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class InspectionPdfGenerator
{
    /**
     * Generate PDF untuk inspection dan simpan ke storage
     *
     * @param Inspection $inspection
     * @return string|null Path file PDF yang disimpan
     */
    public function generate(Inspection $inspection)
    {
        // Ambil data menu points & images
        $menu_points = MenuPoint::with([
            'app_menu',
            'inspection_point',
            'inspection_point.component',
            'inspection_point.results' => function ($q) use ($inspection) {
                $q->where('inspection_id', $inspection->id);
            },
            'inspection_point.images' => function ($q) use ($inspection) {
                $q->where('inspection_id', $inspection->id)
                  ->orderBy('created_at', 'asc');
            }
        ])
        ->whereHas('app_menu', function ($query) use ($inspection) {
            $query->where('category_id', $inspection->category_id);
        })
        ->get();

        // Ambil cover image (Depan Kanan), jika tidak ada pakai image pertama
        $coverImage = InspectionImage::where('inspection_id', $inspection->id)
            ->whereHas('point', function ($q) {
                $q->where('name', 'Depan Kanan');
            })
            ->first();

        if (!$coverImage) {
            $coverImage = InspectionImage::where('inspection_id', $inspection->id)->first();
        }

        // Generate PDF
        $pdf = Pdf::loadView('inspection.report.report1', [
            'inspection' => $inspection,
            'menu_points' => $menu_points,
            'coverImage' => $coverImage,
        ])->setPaper('a4', 'portrait');

        // Tentukan nama & folder file
        $filename = 'inspection_report_' . $inspection->plate_number . '_' . time() . '.pdf';
        $directory = 'inspection-reports/' . date('Y/m');

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory, 0755, true);
        }

        $filePath = $directory . '/' . $filename;

        // Simpan ke storage
        Storage::disk('public')->put($filePath, $pdf->output());

        // Update inspection file path & approved_at jika belum ada
        $inspection->update([
            'file' => $filePath,
            'approved_at' => $inspection->approved_at ?? now(),
        ]);

        // Tambahkan log
        $inspection->addLog('pdf_generated', 'PDF inspection berhasil di-generate otomatis');

        return $filePath;
    }
}
