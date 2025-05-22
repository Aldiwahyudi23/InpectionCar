<?php

namespace App\Http\Controllers\Menu\Job;

use App\Http\Controllers\Controller;
use App\Models\DataInpection\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class JobController extends Controller
{
    public function index()
    {
       $tasks = Inspection::where('user_id', Auth::id())
            ->where('status', 'draft')
            ->with([
                'car',
                'car.brand',
                'car.model',
                'car.type',
            ]) // Eager load relasi
            ->get();

        return Inertia::render('FrontEnd/Menu/Tugas/Index', [
            'tasks' => $tasks,
        ]);
    }
}
