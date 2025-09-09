<?php

namespace App\Http\Controllers\Menu\Home;

use App\Http\Controllers\Controller;
use App\Models\DataInspection\Inspection;
use App\Models\Team\Region;
use App\Models\Team\RegionTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
    public function index(Request $request)
    {
        $user = $request->user();
        // cari semua user_id yang ada di region team user ini
        $userIds = RegionTeam::where('region_id', function ($q) use ($user) {
        $q->select('region_id')
          ->from('region_teams')
          ->where('user_id', $user->id)
          ->limit(1);
        })
        ->pluck('user_id');
        
        // Get filters from request
        $filters = $request->only(['status', 'dateRange', 'search', 'perPage']);
        
       // ambil inspections yang user_id nya ada di region tersebut
        $query = Inspection::with(['car', 'user'])
            ->whereIn('user_id', $userIds);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['dateRange'])) {
            switch ($filters['dateRange']) {
                case 'today':
                    $query->whereDate('inspection_date', today());
                    break;
                case 'week':
                    $query->whereBetween('inspection_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('inspection_date', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
            }
        }
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->whereHas('car', function($q2) use ($search) {
                    $q2->where('car_name', 'like', "%{$search}%")
                       ->orWhere('plate_number', 'like', "%{$search}%");
                })->orWhereHas('user', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }
        
        // Pagination
        $perPage = $filters['perPage'] ?? 10;
        $inspections = $query->orderBy('inspection_date', 'desc')
                            ->paginate($perPage)
                            ->withQueryString();
        
        // Get stats
        $stats = [
            'total' => Inspection::whereHas('user', function($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })->count(),
        
            'draft' => Inspection::whereHas('user', function($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })->where('status', 'draft')->count(),
            
            'in_progress' => Inspection::whereHas('user', function($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })->where('status', 'in_progress')->count(),

            'pending' => Inspection::whereHas('user', function($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })->where('status', 'pending')->count(),

            'pending_review' => Inspection::whereHas('user', function($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })->where('status', 'pending_review')->count(),

            'approved' => Inspection::whereHas('user', function($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })->where('status', 'approved')->count(),

            'rejected' => Inspection::whereHas('user', function($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })->where('status', 'rejected')->count(),
            
            'revision' => Inspection::whereHas('user', function($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })->where('status', 'revision')->count(),
            
            'cancelled' => Inspection::whereHas('user', function($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })->where('status', 'cancelled')->count(),
            
            'completed' => Inspection::whereHas('user', function($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })->where('status', 'completed')->count(),
        ];

        return Inertia::render('FrontEnd/Menu/Home/Coordinator/Index', [
            'inspections' => $inspections,
            'filters' => $filters,
            'stats' => $stats,
            'region' => [
                'id' => $user->region_id,
                'name' => $user->region->name ?? 'Unknown Region'
            ]
        ]);
    }

    /**
     * Show specific inspection
     */
    public function show(Request $request, Inspection $inspection)
    {

        // $id = Crypt::decrypt($inspection);
        // $inspection = Inspection::find($id);
       
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
            // 'items' => function($query) {
            //     $query->orderBy('category')->orderBy('name');
            // },
            // 'notes' => function($query) {
            //     $query->orderBy('created_at', 'desc')->with('user');
            // }
        ]);

         $inspection->load(['logs.user']); // load 

        return Inertia::render('FrontEnd/Menu/Home/Coordinator/Show', [
            'inspection' => $inspection,
            'region' => [
                'id' => $user->region_id,
                'name' => $user->region->name ?? 'Unknown Region'
            ]
        ]);
    }

    /**
     * Assign inspection to inspector
     */
    public function assign(Request $request, Inspection $inspection)
    {
        // Authorization check
        $user = $request->user();
        if ($inspection->inspector->region_id !== $user->region_id) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'inspector_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $inspection->update([
            'user_id' => $request->inspector_id,
            'status' => 'assigned'
        ]);

        return redirect()->back()->with('success', 'Inspection assigned successfully.');
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
