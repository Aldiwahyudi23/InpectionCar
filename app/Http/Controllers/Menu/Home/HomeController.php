<?php

namespace App\Http\Controllers\Menu\Home;

use App\Http\Controllers\Controller;
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
            ->where('status', 'approved')
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
}
