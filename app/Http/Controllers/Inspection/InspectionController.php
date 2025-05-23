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
        'status' => 'nullable|string',
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

//  public function uploadImage(Request $request)
//     {
//         // Debug 1: Periksa semua data yang masuk
//         // dd($request->all());

//         $request->validate([
//             'point_id' => 'required',
//             'image' => 'required|image|max:2048',
//         ]);

//         // Debug 2: Pastikan file 'image' ada dan merupakan instance UploadedFile
//         if (!$request->hasFile('image')) {
//             return response()->json(['message' => 'No image file found in request.'], 400);
//         }
//         $uploadedFile = $request->file('image');
//         // dd($uploadedFile); // Ini harus menampilkan objek UploadedFile

//         // Debug 3: Coba simpan file ke direktori sementara dan periksa hasilnya
//         // Ini adalah langkah krusial. Jika ini gagal, berarti ada masalah dengan environment PHP/server.
//         try {
//             // Ubah 'public/inspection-images' menjadi 'inspection-images' saja
//             // karena 'public' adalah nama disk, bukan bagian dari path folder.
//             // Metode `store()` otomatis menggunakan disk default jika tidak disebutkan,
//             // atau menggunakan disk yang ditentukan jika Anda menggunakan `->storeAs('disk_name', 'path/filename')`.
//             // Jika Anda ingin ke disk 'public', Anda harus menyebutkannya.
//             // Cara yang lebih eksplisit dan aman:
//             $path = $uploadedFile->store('inspection-images', 'public'); // Simpan ke subfolder 'inspection-images' di disk 'public'
//             // dd($path); // Ini harus menampilkan path relatif seperti 'inspection-images/namafileunik.jpg'
//         } catch (\Exception $e) {
//             // Debug 4: Tangkap error jika penyimpanan gagal
//             log::error('File upload failed: ' . $e->getMessage());
//             return response()->json(['message' => 'Failed to store image: ' . $e->getMessage()], 500);
//         }

//         // Debug 5: Pastikan path yang didapat valid dan file benar-benar ada di storage
//         if (!Storage::disk('public')->exists($path)) {
//             Log::error('File not found after store: ' . $path);
//             return response()->json(['message' => 'Image stored but not found on disk.'], 500);
//         }

//         // $publicPath ini sudah benar untuk konversi ke URL publik
//         $publicPath = str_replace('public/', 'storage/', $path); // Ini tidak lagi dibutuhkan jika $path sudah relatif ke disk public
//         // Jika $path sekarang adalah 'inspection-images/namafile.jpg'
//         // Maka $publicPath harusnya 'storage/inspection-images/namafile.jpg'
//         // Mari kita perbaiki:
//         $publicPath = 'storage/' . $path; // Ini lebih tepat

//         // Debug 6: Periksa publicPath sebelum disimpan ke DB
//         // dd($publicPath);

//         $image = InspectionImage::create([
//             'point_id' => $request->point_id,
//             'image_path' => $publicPath,
//         ]);

//         return response()->json([
//             'path' => $publicPath,
//             'image' => $image,
//         ]);
//     }


public function uploadImage(Request $request)
    {
        // Debug 1: Periksa semua data yang masuk
        // dd($request->all());

        $request->validate([
            'point_id' => 'required',
            'image' => 'required|image|max:2048', // Validasi tetap penting
        ]);

        // Debug 2: Pastikan file 'image' ada dan merupakan instance UploadedFile
        if (!$request->hasFile('image')) {
            return response()->json(['message' => 'No image file found in request.'], 400);
        }
        $uploadedFile = $request->file('image');
        // dd($uploadedFile); // Ini harus menampilkan objek UploadedFile

        // Tentukan nama file unik dan folder tujuan
        $filename = 'inspection-image-' . time() . '.' . $uploadedFile->getClientOriginalExtension();
        $destinationPath = public_path('/storage/inspection-image'); // Folder tujuan di dalam public

        // Debug 3: Coba simpan file menggunakan move()
        try {
            $uploadedFile->move($destinationPath, $filename);
        } catch (\Exception $e) {
            // Debug 4: Tangkap error jika penyimpanan gagal
            Log::error('File upload failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to move image: ' . $e->getMessage()], 500);
        }

        // Path yang akan disimpan ke database (relatif ke folder public)
        $publicPathForDb = 'storage/inspection-image/' . $filename; 
        
        // URL yang akan digunakan di frontend
        // Ini akan otomatis disajikan oleh web server
        $publicUrl = asset($publicPathForDb); 

        // Debug 5: Periksa publicPathForDb dan publicUrl sebelum disimpan ke DB
        // dd(['path_for_db' => $publicPathForDb, 'public_url' => $publicUrl]);

        $image = InspectionImage::create([
            'point_id' => $request->point_id,
            'image_path' => $publicPathForDb, // Simpan path relatif ini ke database
        ]);

        return response()->json([
            'path' => $publicPathForDb, // Path yang disimpan di database
            'public_url' => $publicUrl, // URL lengkap untuk ditampilkan di frontend
            'image' => $image, // Mengandung image_path yang disimpan di DB
        ]);
    }

// public function deleteImage(Request $request)
// {
//     $request->validate([
//         'image_path' => 'required|string',
//     ]);

//     // Delete file from storage
//     Storage::delete(str_replace('storage/', 'public/', $request->image_path));

//     // Delete from database
//     InspectionImage::where('image_path', $request->image_path)->delete();

//     return response()->json(['success' => true]);
// }

 public function deleteImage(Request $request)
    {
        $request->validate([
            'image_path' => 'required|string', // image_path adalah path relatif dari public (misal: 'inspection_uploads/images/nama.jpg')
            // Anda bisa tambahkan 'image_id' jika ingin menghapus berdasarkan ID juga
            // 'image_id' => 'sometimes|integer|exists:inspection_images,id',
        ]);

        $imagePathFromRequest = $request->image_path;

        // 1. Hapus file dari penyimpanan fisik (folder public)
        // Kita tahu file disimpan di public/inspection_uploads/images/
        // Jadi, kita perlu path absolut ke file tersebut.
        $fullPathToDelete = public_path($imagePathFromRequest);

        // Debugging: Pastikan path yang akan dihapus itu benar
        // dd($fullPathToDelete, file_exists($fullPathToDelete)); 

        if (file_exists($fullPathToDelete)) {
            try {
                unlink($fullPathToDelete); // Gunakan unlink() untuk menghapus file fisik
                // Atau, jika Anda ingin tetap menggunakan Storage Facade (meskipun tidak untuk upload):
                // Storage::disk('your_public_disk_name')->delete($imagePathFromRequest);
                // *your_public_disk_name* adalah nama disk yang menunjuk ke public/inspection_uploads, 
                // ini bisa sedikit rumit jika tidak dikonfigurasi dengan benar.
                // unlink() adalah cara yang lebih langsung dan dijamin bekerja untuk kasus ini.
            } catch (\Exception $e) {
                // Log error jika gagal menghapus file
                Log::error("Failed to delete image file: {$fullPathToDelete}. Error: {$e->getMessage()}");
                return response()->json(['message' => 'Failed to delete image file.'], 500);
            }
        } else {
            // Log jika file tidak ditemukan (mungkin sudah dihapus sebelumnya atau path salah)
            Log::warning("Image file not found at: {$fullPathToDelete}. Skipping file deletion.");
        }

        // 2. Hapus dari database
        // Anda bisa hapus berdasarkan image_path atau image_id (jika dikirim)
        $deletedCount = InspectionImage::where('image_path', $imagePathFromRequest)->delete();

        if ($deletedCount > 0) {
            return response()->json(['success' => true, 'message' => 'Image and record deleted successfully.']);
        } else {
            // Ini terjadi jika record tidak ditemukan di DB (mungkin sudah dihapus)
            return response()->json(['success' => false, 'message' => 'Image record not found in database.'], 404);
        }
    }

public function finalSubmit(Request $request)
{
    // Logic untuk final submit jika diperlukan
    return redirect()->back()->with('success', 'Inspection submitted successfully');
}

}
