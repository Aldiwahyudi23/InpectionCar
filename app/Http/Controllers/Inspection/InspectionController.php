<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use App\Mail\InspectionReportMail;
use App\Models\DataCar\CarDetail;
use App\Models\DataInspection\AppMenu;
use App\Models\DataInspection\Categorie;
use App\Models\DataInspection\Component;
use App\Models\DataInspection\Inspection;
use App\Models\DataInspection\InspectionImage;
use App\Models\DataInspection\InspectionPoint;
use App\Models\DataInspection\InspectionResult;
use App\Models\DataInspection\MenuPoint;
use App\Models\Team\RegionTeam;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Mpdf\Mpdf;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\FpdiProtection\FpdiProtection;
use Spatie\Browsershot\Browsershot;

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
        $team = RegionTeam::with(['user','regions'])
        ->where('status','active')
        ->get();

        return Inertia::render('FrontEnd/Inspection/Create', [
            'CarDetail' => $CarDetail,
            'Category' => $Category,
            'team' => $team,
        ]);
    }
    
    /**
     * Store a newly created resource in storage.
   
    */
    public function start($inspection)
    {
        try {
            // Dekripsi dan validasi
            $id = Crypt::decrypt($inspection);
            $inspection = Inspection::findOrFail($id);

            // Redirect jika pending_review
                if ($inspection->status === 'pending_review') {
                    $encryptID = Crypt::encrypt($inspection->id);
                    return redirect()->route('inspections.review', ['id' => $encryptID]);
                }

            // Validasi status
            $allowedStatuses = ['draft', 'in_progress', 'revision', 'pending'];
            if (!in_array($inspection->status, $allowedStatuses)) {
                return redirect()->route('job.index')
                    ->with('error', 'Halaman inspeksi tidak bisa diakses karena status tidak valid.');
            }

            // Update status jika draft
            if ($inspection->status === 'draft') {
                $inspection->update(['status' => 'in_progress']);
                $inspection->addLog('in_progress', 'Mulai Inspeksi');
            }

            // Ambil semua AppMenu dengan relasi
            $appMenus = AppMenu::with([
                'menu_point' => function ($query) {
                    $query->where('is_active', true)->orderBy('order');
                },
                'menu_point.inspection_point.component',
                'menu_point.inspection_point' => function ($query) {
                    $query->where('is_active', true);
                }
            ])
            ->where('is_active', true)
            ->where('category_id', $inspection->category_id)
            ->orderBy('order')
            ->get();

            // Ambil semua damage points
            $damagePoints = MenuPoint::with(['inspection_point', 'app_menu'])
                ->where('is_active', true)
                ->whereHas('app_menu', function ($query) use ($inspection) {
                    $query->where('input_type', 'damage')
                        ->where('is_active', true)
                        ->where('category_id', $inspection->category_id);
                })
                ->orderBy('order')
                ->get();

            // Ambil semua inspection_point_id yang terkait
            $inspectionPointIds = $appMenus->flatMap(function ($menu) {
                return $menu->menu_point->pluck('inspection_point_id');
            })->merge($damagePoints->pluck('inspection_point_id'))->unique();

            // Ambil semua hasil inspeksi dan gambar
            $existingResults = InspectionResult::where('inspection_id', $inspection->id)
                ->whereIn('point_id', $inspectionPointIds)
                ->get()
                ->keyBy('point_id');

            $existingImages = InspectionImage::where('inspection_id', $inspection->id)
                ->whereIn('point_id', $inspectionPointIds)
                ->get()
                ->groupBy('point_id');

            // Data untuk frontend
            return Inertia::render('FrontEnd/Inspection/InspectionForm', [
                'inspection' => $inspection->load(['car', 'user']),
                'appMenus' => $appMenus,
                'damagePoints' => $damagePoints,
                'existingResults' => $existingResults,
                'existingImages' => $existingImages,
                'CarDetail' => CarDetail::with(['brand', 'model', 'type'])->get(),
            ]);

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID Inspeksi Tidak Valid');
        }
    }


public function store(Request $request)
{
    if ($request->input('is_scheduled')) {
        $scheduledAt = $request->input('scheduled_at_date') . ' ' . $request->input('scheduled_at_time');
        $request->merge(['scheduled_at' => $scheduledAt]);
    }

    $validated = $request->validate([
        'plate_number' => 'required|string|max:20',
        'category_id' => 'required|exists:categories,id',
        'is_scheduled' => 'required|boolean',
        'scheduled_at' => 'nullable|date',
        'inspector_id' => 'nullable',
        'car_id' => 'nullable',
        'car_name' => 'required_without:car_id|nullable|string|max:100',
    ]);

    $carId = $validated['car_id'] ?? null;

    // 🔑 Generate random secure code (10 karakter alfanumerik)
    $randomCode = Str::upper(Str::random(10));

    $inspectionData = [
        'user_id'       => $validated['inspector_id'],
        'submitted_by'  => Auth::id(),
        'submitted_at'  => now(),
        'plate_number'  => $validated['plate_number'],
        'category_id'   => $validated['category_id'],
        'car_id'        => $carId,
        'car_name'      => $validated['car_name'] ?? null,
        'status'        => $validated['is_scheduled'] ? 'draft' : 'in_progress',
        'code'          => $randomCode, // simpan ke DB
    ];

    if ($validated['is_scheduled']) {
        $inspectionData['inspection_date'] = $validated['scheduled_at'];
    } else {
        $inspectionData['inspection_date'] = now();
        $inspectionData['status'] = 'in_progress';
    }

    $inspection = Inspection::create($inspectionData);

    $inspection->addLog('created', 'Inspection baru dibuat');

    if ($validated['is_scheduled']) {
        return redirect()->route('job.index')
            ->with('success', 'Inspeksi berhasil dijadwalkan.');
    } else {
        $encryptedId = Crypt::encrypt($inspection->id);
        return redirect()->route('inspections.start', ['inspection' => $encryptedId]);
    }
}

    /**
     * Display the specified resource.
     */
    public function show( $inspection)
    {
        $id = Crypt::decrypt($inspection);
        $inspection = Inspection::find($id);
        $inspection->load(['logs.user']); // load logs + user

        return inertia('FrontEnd/Inspection/Log', [
            'inspection' => $inspection
        ]);
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
            // menambil data atau nama dari point_id 
            $point = InspectionPoint::find($validated['point_id']);

            //Untuk mengupdate otomatis setiap ada perubahan di result 
            $inspect = Inspection::find($validated['inspection_id']);
            //jika point name nya warna maka ambil nilai note nya
            if($point->name === "Warna"){
                $inspect->color = $validated['note'];
            }
            if($point->name === "No Rangka"){
            $inspect->noka = $validated['note'];
            }
             if($point->name === "No Mesin"){
            $inspect->nosin = $validated['note'];
             }
             if($point->name === "Jarak Tempuh (KM)"){
            $inspect->km =$validated['note'];
             }

            $inspect->update();

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
            'flooded' => 'nullable|in:yes,no',
            'collision' => 'nullable|in:yes,no',
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
        // 1. Validate the request for multiple files
        $request->validate([
            'point_id' => 'required|integer',
            'inspection_id' => 'required|integer', 
            'images' => 'required|array', // Make sure 'images' is an array
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validate each file in the array
        ]);

        $pointId = $request->input('point_id');
        $inspectionId = $request->input('inspection_id');
        $uploadedImages = [];

        // 2. Loop through each uploaded file
        foreach ($request->file('images') as $uploadedFile) {
            // Generate a unique filename and destination path for each image
            $filename = 'inspection-image-' . time() . '-' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
            $destinationPath = public_path('storage/inspection-image');

            try {
                // Move the file to the public storage directory
                $uploadedFile->move($destinationPath, $filename);
            } catch (\Exception $e) {
                // Handle file move failure
                Log::error('File upload failed: ' . $e->getMessage());
                return response()->json(['message' => 'Failed to move image: ' . $e->getMessage()], 500);
            }

            // Create a path relative to the public directory for the database
            $publicPathForDb = 'storage/inspection-image/' . $filename;
            
            // 3. Save the image information to the database
            $image = InspectionImage::create([
                'inspection_id' => $inspectionId,
                'point_id' => $pointId,
                'image_path' => $publicPathForDb,
            ]);
            
            // Collect data for the response
            $uploadedImages[] = [
                'path' => $publicPathForDb,
                'public_url' => asset($publicPathForDb),
                'image_id' => $image->id,
            ];
        }

        // 4. Return a successful JSON response with all uploaded image data
        return response()->json([
            'message' => 'Images uploaded successfully.',
            'images' => $uploadedImages,
        ]);
    }

    /**
     * Handle the deletion of an image.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'image_id' => 'required_without:image_path',
            'image_path' => 'required_without:image_id',
        ]);

        $image = null;
        if ($request->has('image_id')) {
            $image = InspectionImage::find($request->image_id);
        } elseif ($request->has('image_path')) {
            $image = InspectionImage::where('image_path', $request->image_path)->first();
        }

        if (!$image) {
            return response()->json(['message' => 'Image not found.'], 404);
        }

        // Delete file from storage
        $path = public_path($image->image_path);
        if (file_exists($path)) {
            unlink($path);
        }

        // Delete record from database
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully.']);
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
        $inspection->addLog('finish', 'Sudah selesai Inspection');

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
        if (in_array($inspection->status, [
            'draft', 
            'in_progress', 
            'pending', 
            'revision',        
            // 'rejected',
            // 'revision',
            // 'completed',
            // 'cancelled'
            ])) {
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
        if (in_array($inspection->status, [
            'draft', 
            'in_progress', 
            'pending', 
            'revision',        
            'rejected',
            'revision',
            'completed',
            'cancelled'
            ])) {
            return redirect()->route('job.index');
        }

        $menu_points = MenuPoint::with([
            'app_menu',
            'inspection_point',
            'inspection_point.component',
            'inspection_point.results' => function ($q) use ($inspection) {
                $q->where('inspection_id', $inspection->id);
            },
            'inspection_point.images' => function ($q) use ($inspection) {
                $q->where('inspection_id', $inspection->id)
                ->orderBy('created_at', 'asc');
            }
        ])
        ->whereHas('app_menu', function ($query) use ($inspection) {
            $query->where('category_id', $inspection->category_id);
        })
        ->get();
// dd($menu_points);

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
            'menu_points' => $menu_points,
            'coverImage' => $coverImage,
            'encryptedIds' => $encryptedIds,
        ]);

        // return view('inspection.report.report1', compact('inspection', 'menu_points', 'coverImage')); 
    }


public function downloadPdf($id)
{
    try {
        $id = Crypt::decrypt($id);
        $inspection = Inspection::with([
            'car',
            'car.brand',
            'car.model',
            'car.type',
            'category',
        ])->findOrFail($id);
      
        // cek status
        if (in_array($inspection->status, [
            'draft', 
            'in_progress', 
            'pending', 
            'revision',        
            'rejected',
            'revision',
            'completed',
            'cancelled'
        ])) {
            return redirect()->route('job.index')->with('error', 'Status inspection tidak valid untuk download.');
        }
        
       $menu_points = MenuPoint::with([
            'app_menu',
            'inspection_point',
            'inspection_point.component',
            'inspection_point.results' => function ($q) use ($inspection) {
                $q->where('inspection_id', $inspection->id);
            },
            'inspection_point.images' => function ($q) use ($inspection) {
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
            'menu_points' => $menu_points,
            'coverImage' => $coverImage,
        ])->setPaper('a4', 'portrait');

        // Generate nama file yang unik
        $filename = 'inspection_report_' . $inspection->plate_number . '_' . time() . '.pdf';
        $directory = 'inspection-reports/' . date('Y/m');
        
        // Buat directory jika belum ada
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory, 0755, true);
        }
        
        $filePath = $directory . '/' . $filename;
        
        // Simpan file ke storage
        Storage::disk('public')->put($filePath, $pdf->output());
        
        // Update inspection status dan file path
        $inspection->update([
            'status' => 'approved',
            'file' => $filePath,
            'approved_at' => now(),
        ]);
        $inspection->addLog('approved', 'Laporan sudah di setujui');

        // Return download response
        // return $pdf->download('inspection_report_'.$inspection->car_name.'_('. $inspection->plate_number.').pdf');
         $encryptId = Crypt::encrypt($inspection->id);

            // Redirect ke halaman review
    return redirect()->route('inspections.review', ['id' => $encryptId])
        ->with('success', 'Inspeksi berhasil dikirim untuk review');

    } catch (\Exception $e) {
        Log::error('Error generating PDF: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal generate laporan PDF: ' . $e->getMessage());
    }
}



public function sendEmail($id, Request $request)
{
    $request->validate([
        'email' => 'required|email'
    ]);

    // Decrypt IDs dan proses pengiriman email
    $inspection = Inspection::find(decrypt($id));
    
    $inspection->addLog('email', 'Report sudah di kirim via email');
    // Kirim email disini
    Mail::to($request->email)->send(new InspectionReportMail($inspection));

    return response()->json(['message' => 'Email berhasil dikirim']);
}

    public function downloadApprovePdf($id)
    {
        try {
        $id = Crypt::decrypt($id);
        $inspection = Inspection::findOrFail($id);
        
        // Cek jika file exists
        if (!$inspection->file || !Storage::disk('public')->exists($inspection->file)) {
            return redirect()->back()->with('error', 'File laporan tidak ditemukan.');
        }
        
        $filePath = storage_path('app/public/' . $inspection->file);
        
        $inspection->addLog('download', 'Report sudah di download');
            // Kirim file untuk diunduh
            return response()->download($filePath, 'laporan-inspeksi-' . $inspection->plate_number . '.pdf');
        
        } catch (\Exception $e) {
            return back()->with('error', 'Link tidak valid atau terjadi kesalahan.');
        }
    }

    public function revisi($id, Request $request)
    {
        try {
            $id = Crypt::decrypt($id);
            $inspection = Inspection::findOrFail($id);
            
            // Update status dan simpan alasan
            $inspection->update([
                'status' => 'revision',
            ]);

            $inspection->addLog('revision', 'User revisi Inspeksi ');
            
            $inspectionID = Crypt::encrypt($inspection->id);
            return redirect()->route('inspections.start', ['inspection' => $inspectionID])->with('success', 'Inspeksi berhasil dibatalkan');
            
        } catch (DecryptException $e) {
            abort(404);
        }
    }
    public function cancel($inspection, Request $request)
    {
        try {
            $id = Crypt::decrypt($inspection);
            $inspection = Inspection::findOrFail($id);
            
            // Update status dan simpan alasan
            $inspection->update([
                'status' => 'cancelled',
                'notes' => $request->reason
            ]);

             $inspection->addLog('cancelled',description:  'Inspection di batalkan');
            
            return redirect()->back()->with('success', 'Inspeksi berhasil dibatalkan');
            
        } catch (DecryptException $e) {
            abort(404);
        }
    }

}
