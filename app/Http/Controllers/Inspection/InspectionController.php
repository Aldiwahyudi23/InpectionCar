<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use App\Models\DataCar\CarDetail;
use App\Models\DataInspection\AppMenu;
use App\Models\DataInspection\Categorie;
use App\Models\DataInspection\Component;
use App\Models\DataInspection\Inspection;
use App\Models\DataInspection\InspectionImage;
use App\Models\DataInspection\InspectionPoint;
use App\Models\DataInspection\InspectionResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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
   public function create(Inspection $inspection)
    {
         $CarDetail = CarDetail::with(['brand', 'model', 'type'])
        ->get();
        $Category = Categorie::all();

        return Inertia::render('FrontEnd/Inspection/Create', [
            'CarDetail' => $CarDetail,
            'Category' => $Category,
        ]);
    }

public function start($inspection)
{
    try {
    // Di controller atau blade
        $id = Crypt::decrypt($inspection);

        $inspection = Inspection::find($id);
        // Validasi status yang diperbolehkan
        $allowedStatuses = ['draft', 'in_progress', 'revisi', 'jeda', 'pending_review'];

        if (! in_array($inspection->status, $allowedStatuses)) {
            return redirect()->route('job.index')
                ->with('error', 'Halaman inspeksi tidak bisa diakses karena status tidak valid.');
        }

        // Jika status pending_review, langsung arahkan ke halaman review
        if ($inspection->status === 'pending_review') {
            $encryptID =Crypt::encrypt($inspection->id);
            return redirect()->route('inspections.review', ['id' => $encryptID]);
        }

        // Jika status masih draft, ubah ke in-progress
        if ($inspection->status === 'draft') {
            $inspection->update([
                'status' => 'in_progress',
            ]);
        }

    // Ambil appMenu tanpa filter damage points (ambil semua points)
        $appMenu = AppMenu::with(['points' => function($query) {
            $query->where('is_active', true)
                ->orderBy('order');
        },
        'points.component', // relasi component di dalam points
        'points.app_menu',   // relasi appMenu di dalam points
        ])
        ->where('is_active', true)
        ->where('category_id', $inspection->category_id)
        ->orderBy('order')
        ->get();

        $appMenu_damage = AppMenu::with(['points' => function($query) {
            $query->where('is_active', true)
                ->orderBy('order');
        },
        'points.component', // relasi component di dalam points
        'points.app_menu',   // relasi appMenu di dalam points
        ])

        ->where('input_type', 'damage')
        ->where('is_active', true)
        ->where('category_id', $inspection->category_id)
        ->orderBy('order')
        ->get();

        // Ambil damage points secara terpisah
        $damagePoints = InspectionPoint::with(['component','app_menu'])
            ->where('is_active', true)
            ->whereIn('app_menu_id', $appMenu_damage->pluck('id'))
            ->orderBy('order')
            ->get();

        // Ambil component IDs (jika masih diperlukan)
        $componentIds = collect();
        foreach ($appMenu as $menu) {
            foreach ($menu->points as $point) {
                if ($point->component_id !== null) {
                    $componentIds->push($point->component_id);
                }
            }
        }

        $uniqueComponentIds = $componentIds->unique()->values();
        $components = Component::whereIn('id', $uniqueComponentIds)->get();

        // Ambil existing results dan images
        $existingResults = InspectionResult::where('inspection_id', $inspection->id)
            ->get()
            ->keyBy('point_id');

        $existingImages = InspectionImage::whereIn('point_id', 
            $appMenu->flatMap->points->pluck('id')
        )->where('inspection_id', $inspection->id)
        ->get()
        ->groupBy('point_id');

        $CarDetail = CarDetail::with(['brand', 'model', 'type'])
            ->get();

        // DD($CarDetail);
        return Inertia::render('FrontEnd/Inspection/InspectionForm', [
            'inspection' => $inspection->fresh()->load(['car', 'user']),
            'appMenu' => $appMenu,
            'existingResults' => $existingResults->values()->all(),
            'existingImages' => $existingImages,
            'components' => $components,
            'damagePoints' => $damagePoints, // Kirim damage points terpisah
            'CarDetail' => $CarDetail,
        ]);
    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        abort(404, 'Tidak Valid');
    }
}



    /**
     * Store a newly created resource in storage.
     */
// app/Http/Controllers/InspectionController.php
public function store(Request $request)
{
     // Perbaikan: Jika dijadwalkan, gabungkan tanggal dan waktu menjadi satu field
        if ($request->input('is_scheduled')) {
            $scheduledAt = $request->input('scheduled_at_date') . ' ' . $request->input('scheduled_at_time');
            $request->merge(['scheduled_at' => $scheduledAt]);
        }

    // Validasi data yang masuk dari form
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20',
            'category_id' => 'required|exists:categories,id',
            'is_scheduled' => 'required|boolean',
            'scheduled_at' => 'nullable|date', // Digunakan jika is_scheduled true
            'car_id' => 'nullable',
            'car_name' => 'required_without:car_id|nullable|string|max:100',
        ]);

        $carId = $validated['car_id'] ?? null;

     

        // Siapkan data dasar untuk inspeksi
        $inspectionData = [
            'user_id' => Auth::user()->id,
            'plate_number' => $validated['plate_number'],
            'category_id' => $validated['category_id'],
            'car_id' => $carId,
            'car_name' => $validated['car_name'] ?? null,
            'status' => $validated['is_scheduled'] ? 'draft' : 'in_progress',
        ];

        // Atur waktu jadwal atau waktu mulai sekarang
        if ($validated['is_scheduled']) {
            $inspectionData['inspection_date'] = $validated['scheduled_at'];
        } else {
            $inspectionData['inspection_date'] = now(); // Gunakan waktu sekarang
            // Anda bisa mengatur status menjadi 'in_progress' atau 'completed'
            $inspectionData['status'] = 'in_progress';
        }

        // Buat entri inspeksi baru di database
        $inspection = Inspection::create($inspectionData);

        // Tentukan respons berdasarkan status jadwal
        if ($validated['is_scheduled']) {
            // Jika dijadwalkan, kembali ke halaman sebelumnya dengan pesan sukses
            return redirect()->route('job.index')
                ->with('success', 'Inspeksi berhasil dijadwalkan.');
        } else {
            // Encrypt semua ID di backend
                $inspection = Crypt::encrypt($inspection->id);
            // Jika mulai inspeksi sekarang, redirect ke halaman start dengan ID inspeksi
            return redirect()->route('inspections.start', ['inspection' => $inspection]);
        }

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
            ], 200);
        }
    }

        public function updateConclusion(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'flooded' => 'required|in:yes,no',
            'collision' => 'required|in:yes,no',
            'collision_severity' => 'nullable|required_if:collision,yes|in:light,heavy',
            'conclusion_note' => 'nullable|string|max:1000',
        ]);

        // Pastikan settings adalah array
        $settings = $inspection->settings ?? [];
        
        // Pastikan settings adalah array, bukan string
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }

        // Siapkan data untuk settings (tanpa conclusion_note)
        $conclusionData = [
            'flooded' => $validated['flooded'],
            'collision' => $validated['collision'],
            'collision_severity' => $validated['collision_severity'] ?? null,
        ];

        // Update settings
        $settings['conclusion'] = $conclusionData;

        // Update inspection
        $updateData = [
            'settings' => $settings,
        ];

        // Only update note if conclusion_note is provided
        if (isset($validated['conclusion_note'])) {
            $updateData['notes'] = $validated['conclusion_note'];
        }

        $inspection->update($updateData);

        return back()->with('success', 'Kesimpulan diperbarui');
    }
    // Di InspectionController.php
public function updateVehicleDetails(Request $request, Inspection $inspection)
{
    // 1. Validasi data yang masuk, termasuk `car_id` sebagai opsional
    $validated = $request->validate([
        'plate_number' => 'required|string|max:20',
        'car_id' => 'nullable',
        'car_name' => 'nullable|string|max:100',
    ]);

    // 2. Gunakan Route Model Binding untuk memperbarui data
    //    Tidak perlu Inspection::find() lagi
    $inspection->update([
        'plate_number' => $validated['plate_number'],
        'car_id' => $validated['car_id'],
        'car_name' => $validated['car_name'],
    ]);

    // 3. Kembalikan respons yang sesuai dengan Inertia.js
    //    Redirect ke halaman sebelumnya dengan pesan flash
    return redirect()->back()->with('success', 'Data kendaraan berhasil diperbarui.');
}

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
            'inspection_id' => $request->inspection_id,
            'point_id' => $request->point_id,
            'image_path' => $publicPathForDb, // Simpan path relatif ini ke database
        ]);

        return response()->json([
            'path' => $publicPathForDb, // Path yang disimpan di database
            'public_url' => $publicUrl, // URL lengkap untuk ditampilkan di frontend
            'image' => $image, // Mengandung image_path yang disimpan di DB
        ]);
    }


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

 public function deleteResultImage(Request $request)
{
    $request->validate([
        'inspection_id' => 'required|exists:inspections,id',
        'point_id' => 'required|exists:inspection_points,id',
    ]);

    // 1. Hapus hasil (result) jika ada
    $result = InspectionResult::where('inspection_id', $request->inspection_id)
        ->where('point_id', $request->point_id)
        ->first();

    if ($result) {
        $result->delete();
    }

    // 2. Ambil semua gambar terkait
    $images = InspectionImage::where('inspection_id', $request->inspection_id)
        ->where('point_id', $request->point_id)
        ->get();

    foreach ($images as $image) {
        if ($image->image_path) {
            // Lokasi file fisik
            $fullPathToDelete = public_path($image->image_path);

            if (file_exists($fullPathToDelete)) {
                try {
                    unlink($fullPathToDelete); // hapus file fisik
                } catch (\Exception $e) {
                    Log::error("Gagal hapus file: {$fullPathToDelete}. Error: {$e->getMessage()}");
                }
            } else {
                Log::warning("File tidak ditemukan: {$fullPathToDelete}, skip hapus.");
            }
        }

        // Hapus record di DB
        $image->delete();
    }

}


public function finalSubmit(Request $request, $id)
{
    // Cari inspeksi berdasarkan ID
    $inspection = Inspection::findOrFail($id);

    // Update status inspeksi
    $inspection->update([
        'status' => 'pending_review',
    ]);

    $encryptId = Crypt::encrypt($inspection->id);
    // Redirect ke halaman review
    return redirect()->route('inspections.review', ['id' => $encryptId])
        ->with('success', 'Inspeksi berhasil dikirim untuk review');
}


public function review($id)
{
    $id = Crypt::decrypt($id);
    $inspection = Inspection::with([
        'car',
        'car.brand',
        'car.model',
        'car.type',
        'category',
    ])->findOrFail($id);

    // cek status
    if (in_array($inspection->status, ['draft', 'in_progress', 'jeda', 'revisi'])) {
        return redirect()->route('job.index');
    }

    $encryptedIds = Crypt::encrypt($inspection->id);

    return inertia('FrontEnd/Inspection/Review', [
        'inspection' => $inspection,
        'encryptedIds' => $encryptedIds,
    ]);
}


public function reviewPdf($id)
{
     $id = Crypt::decrypt($id);
    $inspection = Inspection::with([
        'car',
        'car.brand',
        'car.model',
        'car.type',
        'category',
    ])->findOrFail($id);

     // cek status
    if (in_array($inspection->status, ['draft', 'in_progress', 'jeda', 'revisi'])) {
        return redirect()->route('job.index');
    }

    $inspection_points = InspectionPoint::with([
        'component',
        'app_menu',
        'results' => function ($q) use ($inspection) {
            $q->where('inspection_id', $inspection->id);
        },
        'images' => function ($q) use ($inspection) {
            $q->where('inspection_id', $inspection->id)
              ->orderBy('created_at', 'asc');
        }
    ])
    ->whereHas('app_menu', function ($query) use ($inspection) {
        $query->where('category_id', $inspection->category_id);
    })
    ->get();


    $coverImage = InspectionImage::where('inspection_id', $inspection->id)
        ->whereHas('point', function ($q) {
            $q->where('name', 'Depan Kanan');
        })->first();

    // Jika tidak ada cover image, coba ambil gambar pertama
    if (!$coverImage) {
        $coverImage = InspectionImage::where('inspection_id', $inspection->id)->first();
    }

      $encryptedIds = Crypt::encrypt($inspection->id);

    return Inertia::render('FrontEnd/Inspection/Report/ReviewPDF', [
        'inspection' => $inspection,
        'inspection_points' => $inspection_points,
        'coverImage' => $coverImage,
        'encryptedIds' => $encryptedIds,
    ]);

    // return view('inspection.report.report1', compact('inspection', 'inspection_points', 'coverImage')); 
}
public function downloadPdf($id)
{
     $id = Crypt::decrypt($id);
     $inspection = Inspection::with([
        'car',
        'car.brand',
        'car.model',
        'car.type',
        'category',
    ])->findOrFail($id);

     // cek status
    if (in_array($inspection->status, ['draft', 'in_progress', 'jeda', 'revisi'])) {
        return redirect()->route('job.index');
    }
    
   $inspection_points = InspectionPoint::with([
        'component',
        'app_menu',
        'results' => function ($q) use ($inspection) {
            $q->where('inspection_id', $inspection->id);
        },
        'images' => function ($q) use ($inspection) {
            $q->where('inspection_id', $inspection->id)
              ->orderBy('created_at', 'asc');
        }
    ])
    ->whereHas('app_menu', function ($query) use ($inspection) {
        $query->where('category_id', $inspection->category_id);
    })
    ->get();

    $coverImage = InspectionImage::where('inspection_id', $inspection->id)
        ->whereHas('point', function ($q) {
            $q->where('name', 'Depan Kanan');
        })->first();

    // Jika tidak ada cover image, coba ambil gambar pertama
    if (!$coverImage) {
        $coverImage = InspectionImage::where('inspection_id', $inspection->id)->first();
    }

    $pdf = Pdf::loadView('inspection.report.report1', [
        'inspection' => $inspection,
        'inspection_points' => $inspection_points,
        'coverImage' => $coverImage,
    ])->setPaper('a4', 'portrait');

    return $pdf->download('inspection_report_'.$inspection->car_name.'_('. $inspection->plate_number.').pdf');
}


public function approve(Request $request, Inspection $inspection)
{
    // Validasi hanya yang statusnya pending_review bisa di-approve
    if ($inspection->status !== 'pending_review') {
        abort(403, 'Status inspeksi tidak valid');
    }
    
    $inspection->update([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => Auth::user()->id
    ]);
    
    // Generate PDF
    $pdf = Pdf::loadView('inspections.pdf', ['inspection' => $inspection]);
    
    return $pdf->download("inspeksi-{$inspection->id}.pdf");
    
    // Atau jika ingin menyimpan PDF
    // $pdfPath = "inspections/{$inspection->id}.pdf";
    // Storage::put($pdfPath, $pdf->output());
    // $inspection->update(['pdf_path' => $pdfPath]);
}

}
