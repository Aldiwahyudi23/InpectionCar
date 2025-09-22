<?php

namespace App\Console\Commands;

use App\Models\DataInspection\Inspection;
use App\Models\DataInspection\InspectionImage;
use App\Services\InspectionmPdfGenerator;
use App\Services\InspectionPdfGenerator;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupInspectionData extends Command
{
    protected $signature = 'inspection:cleanup';
    protected $description = 'Hapus result & image 3 hari setelah inspection tertentu';

    public function handle()
    {
        $statuses = [
            'in_progress',
            'pending',
            'pending_review',
            'approved',
            'rejected',
            'revision',
            'cancelled'
        ];

        Inspection::with(['car', 'car.brand', 'car.model', 'car.type', 'category'])
            ->whereIn('status', $statuses)

            ->where('updated_at', '<=', Carbon::now()->subDays(3))

            // 1 jam yang lalu
            //->where('updated_at', '<=', Carbon::now()->subHours(1))

            // 2 jam yang lalu
            //->where('updated_at', '<=', Carbon::now()->subHours(2))

            // 3 jam yang lalu
            //->where('updated_at', '<=', Carbon::now()->subHours(3))

            // 30 menit yang lalu
            //->where('updated_at', '<=', Carbon::now()->subMinutes(30))

            // ->where('updated_at', '<=', Carbon::now()->subMinutes(2))


            ->chunkById(50, function ($inspections) {

                foreach ($inspections as $inspection) {

                    // Cek apakah ada result atau image
                    $hasResults = DB::table('inspection_results')
                        ->where('inspection_id', $inspection->id)
                        ->exists();

                    $hasImages = InspectionImage::where('inspection_id', $inspection->id)
                        ->whereHas('point', function($q){
                            $q->whereNotIn('name', ['Depan Kanan']);
                        })
                        ->exists();

                    // // Jika tidak ada data, lewati
                    // if (!$hasResults && !$hasImages) {
                    //     continue;
                    // }

                    // Jika status pending_review atau approved -> generate PDF dulu
                    if (in_array($inspection->status, ['pending_review', 'approved'])) {

                        $generator = new InspectionmPdfGenerator();
                        if (empty($inspection->file) || !file_exists(public_path($inspection->file))) {
                            $generator->generate($inspection);
                            
                        }

                        // Hapus results
                        if ($hasResults) {
                            DB::table('inspection_results')
                                ->where('inspection_id', $inspection->id)
                                ->delete();
                        }

                        // Hapus images kecuali Depan Kanan
                        if ($hasImages) {
                            $images = InspectionImage::where('inspection_id', $inspection->id)
                                ->whereHas('point', function($query) {
                                    $query->whereNotIn('name', ['Depan Kanan']);
                                })->get();

                            foreach ($images as $image) {
                                $path = public_path($image->image_path);
                                if (file_exists($path)) unlink($path);
                                $image->delete();
                            }
                        }

                        // Update status & log
                        $inspection->update(['status' => 'completed']);
                        $inspection->addLog(
                            'cleanup',
                            'Inspection results & images dihapus otomatis setelah 3 hari (kecuali Depan Kanan)'
                        );

                    } else {
                        // Status lain -> hapus semua data jika ada
                        if ($hasResults) {
                            DB::table('inspection_results')
                                ->where('inspection_id', $inspection->id)
                                ->delete();
                        }

                        if ($hasImages) {
                            $images = InspectionImage::where('inspection_id', $inspection->id)->get();
                            foreach ($images as $image) {
                                $path = public_path($image->image_path);
                                if (file_exists($path)) unlink($path);
                                $image->delete();
                            }
                        }

                        // Update status & log
                        $inspection->update(['status' => 'cancelled']);
                        $inspection->addLog(
                            'cleanup',
                            'Telah dibatalkan otomatis sudah 3 hari tidak ada action, semua data dihapus'
                        );
                    }
                }
            });

        $this->info("Cleanup selesai dijalankan");
    }
}
