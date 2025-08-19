<?php

namespace App\Http\Controllers\Menu\Job;

use App\Http\Controllers\Controller;
use App\Models\DataInspection\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class JobController extends Controller
{
    public function index()
    {
        $tasks = Inspection::where('user_id', Auth::id())
            ->whereIn('status', ['draft', 'in_progress', 'pending_review'])
            ->with([
                'car',
                'car.brand',
                'car.model',
                'car.type',
                'category', // tambahkan relasi kategori
            ])
            ->orderBy('inspection_date', 'asc')
            ->get();

        return Inertia::render('FrontEnd/Menu/Tugas/Index', [
            'tasks' => $tasks,
        ]);
    }

    public function history()
    {
        $tasks = Inspection::where('user_id', Auth::id())
            ->whereNotIn('status', ['draft', 'in_progress', 'pending_review'])
            ->with([
                'car',
                'car.brand',
                'car.model',
                'car.type',
                'category',
            ])
            ->orderBy('inspection_date', 'desc')
            ->get();

        return Inertia::render('FrontEnd/Menu/Tugas/History', [
            'tasks' => $tasks,
        ]);
    }

}
