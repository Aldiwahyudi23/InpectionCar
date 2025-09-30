<?php

namespace App\Http\Controllers\Menu\Home;

use App\Http\Controllers\Controller;
use App\Models\DataInspection\Component;
use App\Models\DataInspection\Inspection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         // Hitung jumlah inspeksi bulan ini yang approved untuk user yang login
        $monthlyApprovedInspections = Inspection::where('user_id', Auth::user()->id)
            ->whereIn('status', ['approved', 'completed'])
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Ambil 3 inspeksi terakhir untuk user yang login
        $recentInspections = Inspection::with(['car'])
            ->where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($inspection) {
                return [
                    'id' => $inspection->id,
                    'vehicle_name' => $inspection->car_name ?? 'N/A',
                    'license_plate' => $inspection->plate_number ?? 'N/A',
                    'status' => $inspection->status,
                    'created_at' => $inspection->created_at->diffForHumans(),
                    'created_at_raw' => $inspection->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('FrontEnd/Menu/Home/Dashboard', [
            'monthlyApprovedCount' => $monthlyApprovedInspections,
            'recentInspections' => $recentInspections,
        ]);
    }

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
    public function show(string $id)
    {
        //
    }

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

    public function bantuan(Request $request)
   {
        $search = $request->query('search');
        
         $components = Component::with(['inspection_point' => function($query) use ($search) {
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%')
                          ->orWhere('description', 'like', '%'.$search.'%')
                          ->orWhere('notes', 'like', '%'.$search.'%');
                    });
                }
                 // Urutkan inspection_point sesuai field 'order'
                $query->orderBy('order', 'asc');
            }])
            ->when($search, function($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                      ->orWhere('description', 'like', '%'.$search.'%')
                      ->orWhereHas('inspection_point', function($subQ) use ($search) {
                          $subQ->where('name', 'like', '%'.$search.'%')
                               ->orWhere('description', 'like', '%'.$search.'%')
                               ->orWhere('notes', 'like', '%'.$search.'%');
                      });
                });
            })
           // Urutkan komponen sesuai urutan juga
            ->orderBy('order', 'asc')
            ->get();


        // // Membersihkan HTML tags untuk pencarian
        // if ($search) {
        //     $components->each(function($component) {
        //         $component->description = strip_tags($component->description);
        //         $component->inspection_point->each(function($point) {
        //             $point->description = strip_tags($point->description);
        //         });
        //     });
        // }

        if ($request->wantsJson()) {
            return response()->json($components);
        }

        return inertia('FrontEnd/Menu/Home/Bantuan', [
            'components' => $components,
            'filters' => $request->all(['search'])
        ]);
    }


}
