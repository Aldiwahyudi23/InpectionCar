<?php

namespace App\Http\Controllers\Menu\Job;

use App\Http\Controllers\Controller;
use App\Models\DataInspection\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

class JobController extends Controller
{
   public function index()
{
    $tasks = Inspection::where('user_id', Auth::id())
        ->whereIn('status', ['draft', 'in_progress', 'pending_review', 'pending','revision'])
        ->with([
            'car',
            'car.brand',
            'car.model',
            'car.type',
            'category', // tambahkan relasi kategori
        ])
        ->orderBy('inspection_date', 'asc')
        ->get();

    // Encrypt semua ID inspection
    $encryptedIds = $tasks->mapWithKeys(function($task) {
        return [$task->id => Crypt::encrypt($task->id)];
    });

    return Inertia::render('FrontEnd/Menu/Tugas/Index', [
        'tasks' => $tasks,
        'encryptedIds' => $encryptedIds
    ]);
}

    public function historys()
    {
        $tasks = Inspection::where('user_id', Auth::id())
            ->whereNotIn('status', ['draft', 'in_progress', 'pending_review', 'pending','revision'
            ])
            ->with([
                'car',
                'car.brand',
                'car.model',
                'car.type',
                'category',
            ])
            ->orderBy('inspection_date', 'desc')
            ->get();

                // Encrypt semua ID inspection
         $encryptedIds = $tasks->mapWithKeys(function($task) {
        return [$task->id => Crypt::encrypt($task->id)];
    });

        return Inertia::render('FrontEnd/Menu/Tugas/History', [
            'tasks' => $tasks,
            'encryptedIds' => $encryptedIds
        ]);
    }

    public function history(Request $request)
{
    $query = Inspection::where('user_id', Auth::id())
        ->whereNotIn('status', ['draft', 'in_progress', 'pending_review', 'pending', 'revision'])
        ->with([
            'car',
            'car.brand',
            'car.model',
            'car.type',
            'category',
        ]);

    // Filter berdasarkan bulan dan tahun
    if ($request->has('month') && $request->has('year')) {
        $query->whereMonth('created_at', $request->month)
              ->whereYear('created_at', $request->year);
    } else {
        // Default ke bulan dan tahun saat ini
        $query->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year);
    }

    // Filter pencarian
    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('plate_number', 'like', "%{$search}%")
              ->orWhere('car_name', 'like', "%{$search}%");
        });
    }

    $tasks = $query->orderBy('created_at', 'desc')->get();

    // Get available months and years for filter dropdown
    $availableMonths = Inspection::where('user_id', Auth::id())
        ->selectRaw('MONTH(inspection_date) as month, YEAR(inspection_date) as year')
        ->distinct()
        ->get()
        ->groupBy('year')
        ->map(function($yearGroup) {
            return $yearGroup->pluck('month')->unique()->sort();
        });

    // Encrypt semua ID inspection
    $encryptedIds = $tasks->mapWithKeys(function($task) {
        return [$task->id => Crypt::encrypt($task->id)];
    });

    return Inertia::render('FrontEnd/Menu/Tugas/History', [
        'tasks' => $tasks,
        'encryptedIds' => $encryptedIds,
        'filters' => [
            'month' => $request->month ?? now()->month,
            'year' => $request->year ?? now()->year,
            'search' => $request->search ?? '',
        ],
        'availableMonths' => $availableMonths,
        'currentMonth' => now()->month,
        'currentYear' => now()->year,
    ]);
}

}
