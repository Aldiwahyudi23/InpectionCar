<?php

namespace App\Http\Controllers\Menu\Home;

use App\Http\Controllers\Controller;
use App\Models\DataInspection\Inspection;
use App\Models\Finance\Transaction;
use App\Models\Team\Region;
use App\Models\Team\RegionTeam;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class CoordinatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Display inspections for coordinator's region
     */

    // public function index(Request $request)
    // {
    //     $user = $request->user();

    //     // Ambil region_id dari user login
    //     $regionId = RegionTeam::where('user_id', $user->id)->value('region_id');

    //     $region = Region::find($regionId);
    //     // Ambil semua user_id yang ada di region tersebut
    //     $userIds = RegionTeam::where('region_id', $regionId)->pluck('user_id');

    //     // Filters
    //     $filters = $request->only(['status', 'dateRange', 'search', 'perPage']);

    //     // Query utama inspections
    //     $query = Inspection::with(['car', 'user', 'customer', 'transaction'])
    //         ->whereIn('user_id', $userIds)
    //         ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
    //         ->when($filters['dateRange'] ?? null, function ($q, $dateRange) {
    //             return match ($dateRange) {
    //                 'today' => $q->whereDate('inspection_date', today()),
    //                 'week'  => $q->whereBetween('inspection_date', [now()->startOfWeek(), now()->endOfWeek()]),
    //                 'month' => $q->whereBetween('inspection_date', [now()->startOfMonth(), now()->endOfMonth()]),
    //                 default => $q,
    //             };
    //         })
    //         ->when($filters['search'] ?? null, function($q, $search) {
    //             $q->where(function($q2) use ($search) {
    //                 $q2->whereHas('car', fn($carQ) => 
    //                         $carQ->where('car_name', 'like', "%{$search}%")
    //                             ->orWhere('plate_number', 'like', "%{$search}%")
    //                     )
    //                     ->orWhereHas('user', fn($userQ) => 
    //                         $userQ->where('name', 'like', "%{$search}%")
    //                             ->orWhere('email', 'like', "%{$search}%")
    //                     );
    //             });
    //         });

    //     // Pagination
    //     $perPage = $filters['perPage'] ?? 10;
    //     $inspections = $query->orderBy('inspection_date', 'desc')
    //                         ->paginate($perPage)
    //                         ->withQueryString();

    //     // Encrypt semua ID inspection
    //     $encryptedIds = $inspections->mapWithKeys(function($task) {
    //         return [$task->id => Crypt::encrypt($task->id)];
    //     });
    //     // =========================
    //     // Stats (ikut filter dateRange + search)
    //     // =========================
    //     $statsQuery = Inspection::select('status', DB::raw('count(*) as total'))
    //         ->whereIn('user_id', $userIds)
    //         ->when($filters['dateRange'] ?? null, function ($q, $dateRange) {
    //             return match ($dateRange) {
    //                 'today' => $q->whereDate('inspection_date', today()),
    //                 'week'  => $q->whereBetween('inspection_date', [now()->startOfWeek(), now()->endOfWeek()]),
    //                 'month' => $q->whereBetween('inspection_date', [now()->startOfMonth(), now()->endOfMonth()]),
    //                 default => $q,
    //             };
    //         })
    //         ->when($filters['search'] ?? null, function($q, $search) {
    //             $q->where(function($q2) use ($search) {
    //                 $q2->whereHas('car', fn($carQ) => 
    //                         $carQ->where('car_name', 'like', "%{$search}%")
    //                             ->orWhere('plate_number', 'like', "%{$search}%")
    //                     )
    //                     ->orWhereHas('user', fn($userQ) => 
    //                         $userQ->where('name', 'like', "%{$search}%")
    //                             ->orWhere('email', 'like', "%{$search}%")
    //                     );
    //             });
    //         })
    //         ->groupBy('status')
    //         ->pluck('total', 'status');

    //     $stats = [
    //         'total'          => $statsQuery->sum(),
    //         'draft'          => $statsQuery['draft'] ?? 0,
    //         'in_progress'    => $statsQuery['in_progress'] ?? 0,
    //         'pending'        => $statsQuery['pending'] ?? 0,
    //         'pending_review' => $statsQuery['pending_review'] ?? 0,
    //         'approved'       => $statsQuery['approved'] ?? 0,
    //         'rejected'       => $statsQuery['rejected'] ?? 0,
    //         'revision'       => $statsQuery['revision'] ?? 0,
    //         'cancelled'      => $statsQuery['cancelled'] ?? 0,
    //         'completed'      => $statsQuery['completed'] ?? 0,
    //     ];

    //     return Inertia::render('FrontEnd/Menu/Home/Coordinator/Index', [
    //         'inspections' => $inspections,
    //         'encryptedIds' => $encryptedIds,
    //         'filters'     => $filters,
    //         'stats'       => $stats,
    //         'region'      => [
    //             'id'   => $regionId,
    //             'name' => $region->name ?? 'Unknown Region',
    //         ],
    //     ]);
    // }

        public function index(Request $request)
    {
        $user = $request->user();

        // Ambil region_id default dari user login
        $regionId = RegionTeam::where('user_id', $user->id)->value('region_id');
        $region   = Region::find($regionId);

        // [ADMIN ADD] Cek role admin
        $isAdmin = $user->hasRole('Admin');

        // Filters
        $filters = $request->only(['status', 'dateRange', 'search', 'perPage', 'region_id', 'user_id']);

        // [ADMIN ADD] Jika admin, ambil region dari filter atau default
        if ($isAdmin) {
            $selectedRegionId = $filters['region_id'] ?? null;
            $selectedRegion   = Region::find($selectedRegionId);

            // Ambil semua region untuk dropdown
            $allRegions = Region::all(['id', 'name']);

              // Ambil semua user_id sesuai region / semua kalau region kosong
            $userIds = RegionTeam::query()
                ->when($selectedRegionId, fn($q) => $q->where('region_id', $selectedRegionId))
                ->pluck('user_id');

            // Jika ada filter user_id, batasi ke user tersebut
            if (!empty($filters['user_id'])) {
                $userIds = collect([$filters['user_id']]);
            }
        } else {
            // Default: koordinator biasa
            $allRegions = collect([]);
            $selectedRegion   = $region;
            $userIds = RegionTeam::where('region_id', $regionId)->pluck('user_id');
        }

        // Query utama inspections
        $query = Inspection::with(['car', 'user', 'customer', 'transaction'])
            ->whereIn('user_id', $userIds)
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['dateRange'] ?? null, function ($q, $dateRange) {
                return match ($dateRange) {
                    'today' => $q->whereDate('inspection_date', today()),
                    'week'  => $q->whereBetween('inspection_date', [now()->startOfWeek(), now()->endOfWeek()]),
                    'month' => $q->whereBetween('inspection_date', [now()->startOfMonth(), now()->endOfMonth()]),
                    default => $q,
                };
            })
            ->when($filters['search'] ?? null, function($q, $search) {
                $q->where(function($q2) use ($search) {
                    $q2->whereHas('car', fn($carQ) => 
                            $carQ->where('car_name', 'like', "%{$search}%")
                                ->orWhere('plate_number', 'like', "%{$search}%")
                        )
                        ->orWhereHas('user', fn($userQ) => 
                            $userQ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                        );
                });
            });

        $perPage = $filters['perPage'] ?? 10;
        $inspections = $query->orderBy('inspection_date', 'desc')
                            ->paginate($perPage)
                            ->withQueryString();

        $encryptedIds = $inspections->mapWithKeys(function($task) {
            return [$task->id => Crypt::encrypt($task->id)];
        });

        // Stats
        $statsQuery = Inspection::select('status', DB::raw('count(*) as total'))
            ->whereIn('user_id', $userIds)
            ->when($filters['dateRange'] ?? null, function ($q, $dateRange) {
                return match ($dateRange) {
                    'today' => $q->whereDate('inspection_date', today()),
                    'week'  => $q->whereBetween('inspection_date', [now()->startOfWeek(), now()->endOfWeek()]),
                    'month' => $q->whereBetween('inspection_date', [now()->startOfMonth(), now()->endOfMonth()]),
                    default => $q,
                };
            })
            ->when($filters['search'] ?? null, function($q, $search) {
                $q->where(function($q2) use ($search) {
                    $q2->whereHas('car', fn($carQ) => 
                            $carQ->where('car_name', 'like', "%{$search}%")
                                ->orWhere('plate_number', 'like', "%{$search}%")
                                ->orWhere('status', 'like', "%{$search}%")
                        )
                        ->orWhereHas('user', fn($userQ) => 
                            $userQ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                        );
                });
            })
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total'          => $statsQuery->sum(),
            'draft'          => $statsQuery['draft'] ?? 0,
            'in_progress'    => $statsQuery['in_progress'] ?? 0,
            'pending'        => $statsQuery['pending'] ?? 0,
            'pending_review' => $statsQuery['pending_review'] ?? 0,
            'approved'       => $statsQuery['approved'] ?? 0,
            'rejected'       => $statsQuery['rejected'] ?? 0,
            'revision'       => $statsQuery['revision'] ?? 0,
            'cancelled'      => $statsQuery['cancelled'] ?? 0,
            'completed'      => $statsQuery['completed'] ?? 0,
        ];

        return Inertia::render('FrontEnd/Menu/Home/Coordinator/Index', [
            'inspections'  => $inspections,
            'encryptedIds' => $encryptedIds,
            'filters'      => $filters,
            'stats'        => $stats,
            'region'       => [
                'id'   => $selectedRegion->id ?? $regionId,
                'name' => $selectedRegion->name ?? 'Unknown Region',
            ],
            'allRegions'   => $allRegions, // [ADMIN ADD]
            'isAdmin'      => $isAdmin,    // [ADMIN ADD]
        ]);
    }


    /**
     * Show specific inspection
     */
    public function show(Request $request, $inspection)
    {

        $id = Crypt::decrypt($inspection);
        $inspection = Inspection::find($id);
       
        // Authorization check - ensure inspection belongs to coordinator's region
        $user = $request->user();
        $userIds = RegionTeam::pluck('user_id');
        
        if (!in_array($inspection->user_id, $userIds->toArray())) {
            abort(403, 'Unauthorized action.');
        }

        // Load all relationships
        $inspection->load([
            'car',
            'car.brand',
            'car.model',
            'car.type',
            'user',
            'customer',
            // 'items' => function($query) {
            //     $query->orderBy('category')->orderBy('name');
            // },
            // 'notes' => function($query) {
            //     $query->orderBy('created_at', 'desc')->with('user');
            // }
        ]);


     

            // 5. Ambil data user (inspektur) berdasarkan ID yang ditemukan
            $inspectors = User::whereIn('id', $userIds)
                               ->get(['id', 'name', 'email']);
        
        $transaction = Transaction::where('inspection_id',$inspection->id )->first();
        // dd($inspectors);
         $inspection->load(['logs.user']); // load 
        $encryptedIds = Crypt::encrypt($inspection->id);

        return Inertia::render('FrontEnd/Menu/Home/Coordinator/Show', [
            'inspection' => $inspection,
            'encryptedIds' => $encryptedIds,
            'inspectors' => $inspectors,
            'transaction' => $transaction,
            'region' => [
                'id' => $user->region_id,
                'name' => $user->region->name ?? 'Unknown Region'
            ]
        ]);
    }

    /**
     * Assign inspection to inspector
     */
 public function assign(Request $request, $inspection)
    {

            $id = Crypt::decrypt($inspection);
            $inspection = Inspection::with([
                'car',
                'car.brand',
                'car.model',
                'car.type',
                'category',
            ])->findOrFail($id);

         $scheduledAt = $request->input('scheduled_at_date') . ' ' . $request->input('scheduled_at_time');
        $request->merge(['scheduled_at' => $scheduledAt]);

        // Validate the request, including the new 'scheduled_at' field
        $validator = Validator::make($request->all(), [
            'inspector_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        // Find the user (inspector) to be assigned
        $inspector = User::find($request->inspector_id);
        
        // Update the inspection with the new inspector and scheduled time
        $inspection->update([
            'user_id'      => $request->inspector_id,
            // 'status'       => 'pending', // Set status to 'pending' after assignment
            'inspection_date' => $request['scheduled_at'], // Parse the date string into a Carbon instance
        ]);

        // Add a log entry for the assignment
        $inspection->addLog('assign', 'Menugaskan inspeksi ke ' . $inspector->name );

        return redirect()->back()->with('success', 'Inspeksi berhasil ditugaskan.');
    }

    /**
     * Update inspection status
     */
    public function updateStatus(Request $request, Inspection $inspection)
    {
        // Authorization check
        $user = $request->user();
        if ($inspection->inspector->region_id !== $user->region_id) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,completed,rejected'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $inspection->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

}
