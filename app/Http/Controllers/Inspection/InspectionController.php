<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use App\Models\DataInpection\Categorie;
use App\Models\DataInpection\Inspection;
use App\Models\DataInpection\InspectionImage;
use App\Models\DataInpection\InspectionResult;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
   
      // Show the inspection form
//    public function create(Inspection $inspection)
//     {
//         // Load categories with their active inspection points
//         $categories = Categorie::with(['points' => function($query) {
//             $query->where('is_active', true)->orderBy('order');
//         }])
//         ->where('is_active', true)
//         ->orderBy('order')
//         ->get();

//         return Inertia::render('FrontEnd/Inspection/Create', [
//             'inspection' => $inspection->load(['car', 'user']),
//             'categories' => $categories,
//         ]);
//     }

public function create(Inspection $inspection)
{
    // Eager load categories with their active points
    $categories = Categorie::with(['points' => function($query) {
        $query->where('is_active', true)
              ->orderBy('order');
    }])
    ->where('is_active', true)
    ->orderBy('order')
    ->get();

    // Load existing results if any (for edit case)
    $existingResults = InspectionResult::where('inspection_id', $inspection->id)
        ->get()
        ->keyBy('point_id');

        // dd($existingResults);
    // Load existing images if any
    $existingImages = InspectionImage::whereIn('point_id', 
        $categories->flatMap->points->pluck('id')
    )->get()
    ->groupBy('point_id');

    return Inertia::render('FrontEnd/Inspection/InspectionForm', [
        'inspection' => $inspection->load(['car', 'user']),
        'categories' => $categories,
        'existingResults' => $existingResults->values()->all(),
        'existingImages' => $existingImages,
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
// app/Http/Controllers/InspectionController.php
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

// InspectionController.php
public function saveResult(Request $request)
{
    $validated = $request->validate([
        'inspection_id' => 'required|exists:inspections,id',
        'point_id' => 'required|exists:inspection_points,id',
        'status' => 'nullable|string|in:good,bad,na',
        'note' => 'nullable|string|max:1000',
    ]);

    try {
        $result = InspectionResult::updateOrCreate(
            [
                'inspection_id' => $validated['inspection_id'],
                'point_id' => $validated['point_id'],
            ],
            [
                'status' => $validated['status'],
                'note' => $validated['note'],
            ]
        );

        // return response()->json([
        //     'success' => true,
        //     'result' => $result,
        // ]);
        // Untuk success
        return redirect()->back()->with('success', 'Data saved successfully');
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to save result: ' . $e->getMessage(),
        ], 500);
    }
}

public function uploadImage(Request $request)
{
    $request->validate([
        'inspection_id' => 'required|exists:inspections,id',
        'point_id' => 'required|exists:inspection_points,id',
        'image' => 'required|image|max:2048',
    ]);

    $path = $request->file('image')->store('public/inspection-images');
    $publicPath = str_replace('public/', 'storage/', $path);

    $image = InspectionImage::create([
        'point_id' => $request->point_id,
        'image_path' => $publicPath,
    ]);

    return response()->json([
        'path' => $publicPath,
        'image' => $image,
    ]);
}

public function deleteImage(Request $request)
{
    $request->validate([
        'image_path' => 'required|string',
    ]);

    // Delete file from storage
    Storage::delete(str_replace('storage/', 'public/', $request->image_path));

    // Delete from database
    InspectionImage::where('image_path', $request->image_path)->delete();

    return response()->json(['success' => true]);
}

public function finalSubmit(Request $request)
{
    // Logic untuk final submit jika diperlukan
    return redirect()->back()->with('success', 'Inspection submitted successfully');
}

}
