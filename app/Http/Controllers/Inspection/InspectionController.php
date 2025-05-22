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
use Illuminate\Support\Facades\Log;
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
        // Debug 1: Periksa semua data yang masuk
        // dd($request->all());

        $request->validate([
            'point_id' => 'required',
            'image' => 'required|image|max:2048',
        ]);

        // Debug 2: Pastikan file 'image' ada dan merupakan instance UploadedFile
        if (!$request->hasFile('image')) {
            return response()->json(['message' => 'No image file found in request.'], 400);
        }
        $uploadedFile = $request->file('image');
        // dd($uploadedFile); // Ini harus menampilkan objek UploadedFile

        // Debug 3: Coba simpan file ke direktori sementara dan periksa hasilnya
        // Ini adalah langkah krusial. Jika ini gagal, berarti ada masalah dengan environment PHP/server.
        try {
            // Ubah 'public/inspection-images' menjadi 'inspection-images' saja
            // karena 'public' adalah nama disk, bukan bagian dari path folder.
            // Metode `store()` otomatis menggunakan disk default jika tidak disebutkan,
            // atau menggunakan disk yang ditentukan jika Anda menggunakan `->storeAs('disk_name', 'path/filename')`.
            // Jika Anda ingin ke disk 'public', Anda harus menyebutkannya.
            // Cara yang lebih eksplisit dan aman:
            $path = $uploadedFile->store('inspection-images', 'public'); // Simpan ke subfolder 'inspection-images' di disk 'public'
            // dd($path); // Ini harus menampilkan path relatif seperti 'inspection-images/namafileunik.jpg'
        } catch (\Exception $e) {
            // Debug 4: Tangkap error jika penyimpanan gagal
            log::error('File upload failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to store image: ' . $e->getMessage()], 500);
        }

        // Debug 5: Pastikan path yang didapat valid dan file benar-benar ada di storage
        if (!Storage::disk('public')->exists($path)) {
            Log::error('File not found after store: ' . $path);
            return response()->json(['message' => 'Image stored but not found on disk.'], 500);
        }

        // $publicPath ini sudah benar untuk konversi ke URL publik
        $publicPath = str_replace('public/', 'storage/', $path); // Ini tidak lagi dibutuhkan jika $path sudah relatif ke disk public
        // Jika $path sekarang adalah 'inspection-images/namafile.jpg'
        // Maka $publicPath harusnya 'storage/inspection-images/namafile.jpg'
        // Mari kita perbaiki:
        $publicPath = 'storage/' . $path; // Ini lebih tepat

        // Debug 6: Periksa publicPath sebelum disimpan ke DB
        // dd($publicPath);

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
