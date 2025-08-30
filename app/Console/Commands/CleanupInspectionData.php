<?php

namespace App\Console\Commands;

use App\Models\DataInspection\Inspection;
use App\Models\DataInspection\InspectionImage;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupInspectionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string.
     */
     protected $signature = 'inspection:cleanup';
    protected $description = 'Hapus result & image 3 hari setelah inspection approved';

//     public function handle()
// {
//     Inspection::where('status', 'approved')
//         ->where('updated_at', '<=', Carbon::now()->subDays(3))
//         ->chunkById(100, function ($inspections) {
//             foreach ($inspections as $inspection) {
//                 // Hapus result
//                 $inspection->results()->delete();

//                 // Hapus images (event model InspectionImage akan otomatis hapus file fisiknya)
//                 $inspection->images()->delete();

//                 // Update status inspection
//                 $inspection->update(['status' => 'completed']);
//             }
//         });

//     $this->info("Cleanup selesai dijalankan");
// }

public function handle()
{
    Inspection::where('status', 'approved')
        // ->where('updated_at', '<=', Carbon::now()->subDays(3))
        ->where('updated_at', '<=', Carbon::now()->subMinutes(1))
        ->chunkById(50, function ($inspections) {
            foreach ($inspections as $inspection) {
                // 1. Hapus results via query builder (langsung, cepat)
                DB::table('inspection_results')
                    ->where('inspection_id', $inspection->id)
                    ->delete();

                // 2. Hapus images via model (supaya event deleting jalan → hapus file storage)
                $images = InspectionImage::where('inspection_id', $inspection->id)->get();
                foreach ($images as $image) {
                    $image->delete(); // trigger event di model
                }

                // 3. Update status jadi completed
                $inspection->update(['status' => 'completed']);
                $inspection->addLog(
    'cleanup',
    'Inspection results & images dihapus otomatis setelah 3 hari approved'
);

            }
        });

    $this->info("Cleanup selesai dijalankan");
}

}
